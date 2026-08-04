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
}