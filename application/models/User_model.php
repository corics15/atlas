<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model
{
  public function getByUsername($username)
  {
    return $this->db
            ->where('username', $username)
            ->where('is_active', TRUE)
            ->get('m_users')
            ->row();
  }

  public function insert($data)
  {
    return $this->db->insert('m_users', $data);
  }

  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $this->db->group_start()
          ->like('username', $keyword)
          ->or_like('first_name', $keyword)
          ->or_like('last_name', $keyword)
      ->group_end();
    }

    return $this->db
        ->order_by('id', 'DESC')
        ->get('m_users')
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('m_users')
        ->row();
  }

  public function update($id, $data)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_users', $data);
  }

  public function usernameExists($username, $excludeId = 0)
  {
    $this->db->where('username', $username);

    if ($excludeId > 0) {
      $this->db->where('id <>', $excludeId);
    }

    return $this->db->count_all_results('m_users') > 0;
  }

  public function deactivate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_users', [
            'is_active' => FALSE
        ]);
  }
}