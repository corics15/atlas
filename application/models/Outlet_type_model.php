<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Outlet_type_model extends CI_Model
{
  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("outlet_type_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->order_by('outlet_type_name ASC')
        ->get('m_outlet_types')
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('m_outlet_types')
        ->row();
  }

  public function save($data, $id = null)
  {
    if (empty($id)) {
      return $this->db->insert('m_outlet_types', $data);
    }

    return $this->db
        ->where('id', $id)
        ->update('m_outlet_types', $data);
  }

  public function activate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_outlet_types', [
          'is_active' => TRUE
        ]);
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_outlet_types', [
          'is_active' => FALSE
        ]);
  }

  public function getDropdown()
  {
    $this->db->select('id, outlet_type_name');
    $this->db->where('is_active', TRUE);
    $this->db->order_by('outlet_type_name');

    return $this->db->get('m_outlet_types')->result();
  }

}