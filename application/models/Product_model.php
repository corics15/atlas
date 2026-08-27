<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Branch_inventory_model');
  }

  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("description ILIKE '%{$escaped}%'")
          ->or_where("barcode ILIKE '%{$escaped}%'")
          ->or_where("case_barcode ILIKE '%{$escaped}%'")
      ->group_end();
    }

    $products = $this->db
        ->order_by('updated_on DESC NULLS LAST, description ASC', null, false)
        ->get('v_products')
        ->result();

    $branchId = (int)$this->session->userdata('branch_id');

    /*** override qty_on_hand in table m_products, use qty_on_hand on t_branch_inventory instead */
    foreach ($products as $product)
    {
      $balance = $this->Branch_inventory_model->getBalance(
        $branchId,
        $product->id
      );

      $product->qty_on_hand = $balance ? $balance->qty_on_hand : 0;
    }

    return $products;
  }

  public function get($id)
  {
    $product = $this->db
        ->where('id', $id)
        ->get('v_products')
        ->row();

    if ($product)
    {
      $balance = $this->Branch_inventory_model->getBalance(
        (int) $this->session->userdata('branch_id'),
        $id
      );

      $product->qty_on_hand = $balance ? $balance->qty_on_hand : 0;
    }

    return $product;
  }

  public function save($data, $id = null)
  {
    if (empty($id)) {
      return $this->db->insert('m_products', $data);
    }

    return $this->db
        ->where('id', $id)
        ->update('m_products', $data);
  }

  public function activate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_products', [
          'is_active' => TRUE
        ]);
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_products', [
          'is_active' => FALSE
        ]);
  }

  public function getDropdown()
  {
    $this->db->select('id,
                      barcode,
                      description,
                      uom,
                      srp,
                      supplier_name');

    $this->db->where('is_active', TRUE);
    $this->db->order_by('description');

    return $this->db
        ->get('v_products')
        ->result();
  }

  public function getFinder()
  {
    $this->db->select("
        id,
        barcode,
        supplier_name,
        description,
        uom,
        srp
    ");

    $this->db->where('is_active', TRUE);
    $this->db->order_by('description');

    return $this->db
        ->get('v_products')
        ->result();
  }

  public function getByBarcode($barcode)
  {
    return $this->db
        ->where('barcode', trim($barcode))
        ->where('is_active', TRUE)
        ->get('v_products')
        ->row();
  }

  public function generateBarcode()
  {
    $this->db->trans_start();

    $numbering = $this->db
        ->query("
            SELECT id, next_number
            FROM m_barcode_numbering
            ORDER BY id
            LIMIT 1
            FOR UPDATE
        ")
        ->row();

    if (!$numbering) {
      $this->db->trans_complete();
      return false;
    }

    $nextNumber = (int)$numbering->next_number;

    do {
      $body = '29' . str_pad($nextNumber, 10, '0', STR_PAD_LEFT);
      $barcode = $body . $this->calculateBarcodeCheckDigit($body);

      $exists = $this->barcodeExists($barcode);

      if ($exists) {
          $nextNumber++;
      }
    } while ($exists);

    $this->db
        ->where('id', $numbering->id)
        ->update('m_barcode_numbering', [
            'next_number' => $nextNumber + 1,
            'updated_by' => $this->session->userdata('user_id'),
            'updated_on' => date('Y-m-d H:i:s')
        ]);

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      return false;
    }

    return $barcode;
  }

  private function calculateBarcodeCheckDigit($body)
  {
    $sum = 0;
    for ($i = 0; $i < strlen($body); $i++) {
      $digit = (int)$body[$i];
      $sum += ($i % 2 === 0) ? $digit : $digit * 3;
    }

    return (10 - ($sum % 10)) % 10;
  }

  private function barcodeExists($barcode)
  {
    $productBarcode = $this->db
        ->group_start()
            ->where('barcode', $barcode)
            ->or_where('case_barcode', $barcode)
        ->group_end()
        ->count_all_results('m_products');

    if ($productBarcode > 0) {
        return true;
    }

    $productUomBarcode = $this->db
        ->where('barcode', $barcode)
        ->count_all_results('m_product_uom');

    return $productUomBarcode > 0;
  }

  public function barcodeInUse($barcode, $excludeProductId = null)
  {
    $barcode = trim($barcode);

    if ($barcode === '') {
      return false;
    }

    $this->db
        ->group_start()
            ->where('barcode', $barcode)
            ->or_where('case_barcode', $barcode)
        ->group_end();

    if (!empty($excludeProductId)) {
      $this->db->where('id !=', (int)$excludeProductId);
    }

    if ($this->db->count_all_results('m_products') > 0) {
      return true;
    }

    $this->db->where('barcode', $barcode);
    if (!empty($excludeProductId)) {
      $this->db->where('product_id !=', (int)$excludeProductId);
    }

    return $this->db->count_all_results('m_product_uom') > 0;
  }

}