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
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("username ILIKE '%{$escaped}%'")
          ->or_where("first_name ILIKE '%{$escaped}%'")
          ->or_where("last_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->select('u.*, b.branch_name')
        ->from('m_users u')
        ->join('m_branches b', 'b.id = u.branch_id', 'left')
        ->order_by('u.id', 'DESC')
        ->get()
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

  public function activate($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_users', [
            'is_active' => TRUE
        ]);
  }

  public function resetPassword($id)
  {
    return $this->db
        ->where('id', $id)
        ->update('m_users', [
            'password' => password_hash(config_item('atlas')['default_password'], PASSWORD_DEFAULT)
        ]);
  }

  public function getProfile($userId)
  {
    return $this->db
        ->select('
          u.id,
          u.username,
          u.first_name,
          u.last_name,
          u.email,
          u.branch_id,
          u.salesman_id,
          u.access_level,
          u.avatar,
          b.branch_name
        ')
        ->from('m_users u')
        ->join('m_branches b', 'b.id = u.branch_id', 'left')
        ->where('u.id', $userId)
        ->where('u.is_active', TRUE)
        ->get()
        ->row();
  }

  public function updatePassword($userId, $password)
  {
    return $this->db
        ->where('id', $userId)
        ->update('m_users', [
          'password'   => password_hash($password, PASSWORD_DEFAULT),
          'updated_by' => $userId,
          'updated_on' => date('Y-m-d H:i:s')
        ]);
  }

  public function updateAvatar($userId, $avatar)
  {
    return $this->db
        ->where('id', $userId)
        ->update('m_users', [
          'avatar'     => $avatar,
          'updated_by' => $userId,
          'updated_on' => date('Y-m-d H:i:s')
        ]);
  }

}