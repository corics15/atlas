<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model
{
  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("customer_name ILIKE '%{$escaped}%'")
          // ->or_where("barcode ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->order_by('customer_name ASC')
        ->get('v_customers')
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('v_customers')
        ->row();
  }

  public function save($data, $id = null)
  {
    if (empty($id)) {
      return $this->db->insert('m_customers', $data);
    }

    return $this->db
        ->where('id', $id)
        ->update('m_customers', $data);
  }

  public function activate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_customers', [
          'is_active' => TRUE
        ]);
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_customers', [
          'is_active' => FALSE
        ]);
  }
}