<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_model extends CI_Model
{
public function getAll($keyword = '')
{
  if (!empty($keyword)) {
    $escaped = $this->db->escape_like_str($keyword);

    $this->db->group_start()
        ->where("supplier_name ILIKE '%{$escaped}%'")
        ->or_where("contact_person ILIKE '%{$escaped}%'")
        ->or_where("mobile_no ILIKE '%{$escaped}%'")
        ->or_where("telephone_no ILIKE '%{$escaped}%'")
        ->or_where("email_address ILIKE '%{$escaped}%'")
    ->group_end();
  }

  return $this->db
      ->order_by('supplier_name ASC')
      ->get('m_suppliers')
      ->result();
}

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('m_suppliers')
        ->row();
  }

  public function save($data, $id = null)
  {
    if (empty($id)) {
      return $this->db->insert('m_suppliers', $data);
    }

    return $this->db
        ->where('id', $id)
        ->update('m_suppliers', $data);
  }

  public function activate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_suppliers', [
          'is_active' => TRUE
        ]);
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_suppliers', [
          'is_active' => FALSE
        ]);
  }

  public function supplierNameExists($supplierName, $excludeId = null)
  {
      $this->db->where('supplier_name', $supplierName);
      if (!empty($excludeId)) {
          $this->db->where('id <>', $excludeId);
      }

      return $this->db->count_all_results('m_suppliers') > 0;
  }
}