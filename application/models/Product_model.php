<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model
{
  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("description ILIKE '%{$escaped}%'")
          ->or_where("barcode ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->order_by('description ASC')
        ->get('v_products')
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('m_products')
        ->row();
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
}