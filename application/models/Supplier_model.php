<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supplier_model extends CI_Model
{
  public function getAll($keyword = '')
  {
    $this->db->select('s.*, t.terms_name');
    $this->db->from('m_suppliers s');
    $this->db->join('m_terms t', 't.id = s.terms_id', 'left');

    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("s.supplier_name ILIKE '%{$escaped}%'")
          ->or_where("s.contact_person ILIKE '%{$escaped}%'")
          ->or_where("s.mobile_no ILIKE '%{$escaped}%'")
          ->or_where("s.telephone_no ILIKE '%{$escaped}%'")
          ->or_where("s.email_address ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->order_by('s.supplier_name', 'ASC')
        ->get()
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

  public function getDropdown()
  {
    $this->db->select('id, supplier_name, terms_id');
    $this->db->where('is_active', TRUE);
    $this->db->order_by('supplier_name', 'ASC');

    return $this->db->get('m_suppliers')->result();
  }
}