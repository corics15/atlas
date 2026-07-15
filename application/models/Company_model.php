<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company_model extends CI_Model
{
  public function get()
  {
    return $this->db
        ->limit(1)
        ->get('m_company')
        ->row();
  }

  public function save($data)
  {
    return $this->db
        ->where('id', 1)
        ->update('m_company', $data);
  }
}