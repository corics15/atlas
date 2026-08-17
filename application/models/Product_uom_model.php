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

  public function save($productId, $uomId, $conversionFactor)
  {
    $existing = $this->db
        ->where('product_id', $productId)
        ->where('uom_id', $uomId)
        ->get('m_product_uom')
        ->row();

    $data = [
      'conversion_factor' => $conversionFactor,
      'is_active' => TRUE,
      'updated_by' => $this->session->userdata('user_id'),
      'updated_on' => date('Y-m-d H:i:s')
    ];

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

}