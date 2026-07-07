<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Salesman_model extends CI_Model
{
  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $this->db->group_start();

      $this->db->like('code', $keyword);
      $this->db->or_like('first_name', $keyword);
      $this->db->or_like('last_name', $keyword);

      $this->db->group_end();
    }

    return $this->db
        ->order_by('last_name')
        ->order_by('first_name')
        ->get('m_salesmen')
        ->result();
  }

  public function save($data, $id = null)
  {
    if (empty($id)) {
      return $this->db->insert('m_salesmen', $data);
    }

    return $this->db
        ->where('id', $id)
        ->update('m_salesmen', $data);
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('m_salesmen')
        ->row();
  }

  public function codeExists($code, $excludeId = null)
  {
      $this->db->where('code', $code);
      if (!empty($excludeId)) {
          $this->db->where('id <>', $excludeId);
      }

      return $this->db->count_all_results('m_salesmen') > 0;
  }

  public function activate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_salesmen', [
          'is_active' => TRUE
        ]);
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_salesmen', [
          'is_active' => FALSE
        ]);
  }
}