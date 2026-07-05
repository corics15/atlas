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
}