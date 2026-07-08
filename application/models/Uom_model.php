<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Uom_model extends CI_Model
{
  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("uom ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->order_by('uom ASC')
        ->get('m_uom')
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('m_uom')
        ->row();
  }

  public function save($data, $id = null)
  {
    if (empty($id)) {
      return $this->db->insert('m_uom', $data);
    }

    return $this->db
        ->where('id', $id)
        ->update('m_uom', $data);
  }

  public function activate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_uom', [
          'is_active' => TRUE
        ]);
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_uom', [
          'is_active' => FALSE
        ]);
  }

  public function uomExists($uom, $excludeId = null)
  {
      $this->db->where('uom', strtoupper($uom));
      if (!empty($excludeId)) {
        $this->db->where('id <>', $excludeId);
      }

      return $this->db->count_all_results('m_uom') > 0;
  }

  public function getDropdown()
  {
    $this->db->select('id, uom');
    $this->db->where('is_active', TRUE);
    $this->db->order_by('uom', 'ASC');

    return $this->db->get('m_uom')->result();
  }
}