<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Term_model extends CI_Model
{
  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("terms_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->order_by('terms_name ASC')
        ->get('m_terms')
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('m_terms')
        ->row();
  }

  public function save($data, $id = null)
  {
    if (empty($id)) {
      return $this->db->insert('m_terms', $data);
    }

    return $this->db
        ->where('id', $id)
        ->update('m_terms', $data);
  }

  public function activate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_terms', [
          'is_active' => TRUE
        ]);
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_terms', [
          'is_active' => FALSE
        ]);
  }

  public function termsExists($term, $excludeId = null)
  {
    $this->db->where('LOWER(terms_name)', strtolower($term));
    if (!empty($excludeId)) {
      $this->db->where('id <>', $excludeId);
    }

    return $this->db->count_all_results('m_terms') > 0;
  }

  public function getDropdown()
  {
    $this->db->select('id, terms_name');
    $this->db->where('is_active', TRUE);
    $this->db->order_by('terms_name', 'ASC');

    return $this->db->get('m_terms')->result();
  }
}