<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_uom_model extends CI_Model
{

  public function get($productId, $uomId)
  {
    return $this->db
        ->where('product_id', $productId)
        ->where('uom_id', $uomId)
        ->where('is_active', TRUE)
        ->get('m_product_uom')
        ->row();
  }

  public function getByProduct($productId)
  {
    return $this->db
      ->select('
        pu.id,
        pu.product_id,
        pu.uom_id,
        u.uom,
        pu.conversion_factor,
        pu.barcode,
        pu.last_cost,
        pu.selling_price,
        p.srp,
        pu.is_active,
        pu.is_sales_uom,
        pu.is_purchase_uom
      ')
      ->from('m_product_uom pu')
      ->join('m_products p', 'p.id = pu.product_id', 'inner')
      ->join('m_uom u', 'u.id = pu.uom_id', 'left')
      ->where('pu.product_id', $productId)
      ->where('pu.uom_id != p.uom_id', NULL, FALSE)
      ->order_by('pu.conversion_factor', 'DESC')
      ->get()
      ->result();
  }

  public function getSalesUom($productId, $uomId)
  {
    return $this->db
      ->where('product_id', $productId)
      ->where('uom_id', $uomId)
      ->where('is_sales_uom', TRUE)
      ->get('m_product_uom')
      ->row();
  }

  public function getSalesUoms($productId)
  {
    return $this->db
      ->select('
        pu.uom_id,
        u.uom,
        pu.conversion_factor,
        pu.selling_price
      ')
      ->from('m_product_uom pu')
      ->join('m_uom u', 'u.id = pu.uom_id')
      ->where('pu.product_id', $productId)
      ->where('pu.is_sales_uom', true)
      ->order_by('pu.conversion_factor', 'ASC')
      ->get()
      ->result();
  }

  public function getPurchaseUoms($productId)
  {
    return $this->db
      ->select('
        pu.uom_id,
        u.uom,
        pu.conversion_factor,
        pu.last_cost
      ')
      ->from('m_product_uom pu')
      ->join('m_uom u', 'u.id = pu.uom_id', 'left')
      ->where('pu.product_id', $productId)
      ->where('pu.is_purchase_uom', TRUE)
      ->order_by('pu.conversion_factor', 'ASC')
      ->get()
      ->result();
  }

  public function save($productId, $uomId, $conversionFactor, $sellingPrice = null, $lastCost = null, $isSalesUom = false, $isPurchaseUom = false)
  {
    $existing = $this->db
        ->where('product_id', $productId)
        ->where('uom_id', $uomId)
        ->get('m_product_uom')
        ->row();

    $data = [
      'conversion_factor' => $conversionFactor,
      'is_sales_uom' => $isSalesUom,
      'is_purchase_uom' => $isPurchaseUom,
      'is_active' => TRUE,
      'updated_by' => $this->session->userdata('user_id'),
      'updated_on' => date('Y-m-d H:i:s')
    ];

    if ($sellingPrice !== null) {
      $data['selling_price'] = $sellingPrice;
    }

    if ($lastCost !== null) {
      $data['last_cost'] = $lastCost;
    }

    if ($existing) {
      return $this->db
          ->where('id', $existing->id)
          ->update('m_product_uom', $data);
    }

    $data['product_id'] = $productId;
    $data['uom_id'] = $uomId;
    $data['entered_by'] =$this->session->userdata('user_id');
    $data['entered_on'] = date('Y-m-d H:i:s');

    return $this->db->insert('m_product_uom', $data);
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_product_uom', [
          'is_active' => FALSE,
          'updated_by' => $this->session->userdata('user_id'),
          'updated_on' => date('Y-m-d H:i:s')
        ]);
  }

}