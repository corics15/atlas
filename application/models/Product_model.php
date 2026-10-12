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

      /*** load assigned Base UOM eligibility */
      $baseUom = $this->db
        ->select('is_sales_uom, is_purchase_uom')
        ->where('product_id', (int)$id)
        ->where('uom_id', (int)$product->uom_id)
        ->get('m_product_uom')
        ->row();

      $product->is_sales_uom = $baseUom ? $baseUom->is_sales_uom : NULL;
      $product->is_purchase_uom = $baseUom ? $baseUom->is_purchase_uom : NULL;

    }

    return $product;
  }

  public function save($data, $id = null, $isSalesUom = FALSE, $isPurchaseUom = FALSE)
  {
    /*
    * BASE UOM INTEGRITY (Product Edit)
    * The assigned base UOM must have exactly one active m_product_uom
    * record with conversion_factor = 1.
    * Reject inconsistent legacy records instead of silently repairing them.
    * Additional UOM prices and Sales/Purchase flags remain independent.
    */
    if (!empty($id)) {
      $originalDbDebug = $this->db->db_debug;
      $this->db->db_debug = FALSE;
      $this->db->trans_begin();

      $baseUoms = $this->db
        ->where('product_id', (int)$id)
        ->where('uom_id', (int)$data['uom_id'])
        ->get('m_product_uom')
        ->result();

      $validBaseUom = count($baseUoms) === 1
        && in_array($baseUoms[0]->is_active, [TRUE, 't'], TRUE)
        && (float)$baseUoms[0]->conversion_factor == 1;

      if (!$validBaseUom) {
        $this->db->trans_rollback();
        $this->db->db_debug = $originalDbDebug;
        return FALSE;
      }

      $saved = @$this->db
        ->where('id', (int)$id)
        ->update('m_products', $data);

      if ($saved && $this->db->trans_status() !== FALSE) {
        $saved = @$this->db
          ->where('id', (int)$baseUoms[0]->id)
          ->update('m_product_uom', [
            'is_sales_uom' => $isSalesUom,
            'is_purchase_uom' => $isPurchaseUom
          ]);
      }

      if (!$saved || $this->db->trans_status() === FALSE) {
        $dbError = $this->db->error();
        log_message('error', 'Product edit failed: ' . $dbError['message']);

        $this->db->trans_rollback();
        $this->db->db_debug = $originalDbDebug;
        return FALSE;
      }

      $committed = $this->db->trans_commit();
      $this->db->db_debug = $originalDbDebug;

      return $committed !== FALSE;
    }

    /*
    * BASE UOM INTEGRITY (Product Creation)
    * Create m_products and its active base m_product_uom atomically.
    * If either INSERT fails, roll back both records to avoid orphaned
    * products that cannot be selected correctly in transactions.
    *
    * db_debug is temporarily disabled and INSERT warnings are suppressed
    * locally because CI3's PostgreSQL driver emits native pg_query warnings.
    * Database failures are still checked and logged; restore db_debug
    * before returning.
    *
    * Base UOM conversion_factor = 1. Sales/Purchase eligibility flags
    * remain FALSE by default and are not automatically enabled.
    */
    $originalDbDebug = $this->db->db_debug;
    $this->db->db_debug = FALSE;

    $this->db->trans_begin();

    $saved = @$this->db->insert('m_products', $data);

    if (!$saved || $this->db->trans_status() === FALSE) {
      $dbError = $this->db->error();
      log_message('error', 'Product creation failed: ' . $dbError['message']);

      $this->db->trans_rollback();
      $this->db->db_debug = $originalDbDebug;
      return false;
    }

    $productId = $this->db->insert_id();

    $baseUom = [
      'product_id' => $productId,
      'uom_id' => (int)$data['uom_id'],
      'conversion_factor' => 1,
      'last_cost' => $data['cost'],
      'selling_price' => $data['srp'],
      'is_active' => TRUE,
      'is_sales_uom' => $isSalesUom,
      'is_purchase_uom' => $isPurchaseUom,
      'entered_by' => $data['entered_by'],
      'entered_on' => $data['entered_on']
    ];

    $saved = @$this->db->insert('m_product_uom', $baseUom);

    if (!$saved || $this->db->trans_status() === FALSE) {
      $dbError = $this->db->error();
      log_message('error', 'Base UOM creation failed: ' . $dbError['message']);

      $this->db->trans_rollback();
      $this->db->db_debug = $originalDbDebug;
      return false;
    }

    $committed = $this->db->trans_commit();
    $this->db->db_debug = $originalDbDebug;

    return $committed !== FALSE;
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