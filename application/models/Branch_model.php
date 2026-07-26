<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Branch_model extends MY_Model
{
  protected $table = 'm_branches';

  public function get($id = null)
  {
    if ($id !== null) {
        return $this->db
            ->where('id', $id)
            ->get($this->table)
            ->row();
    }

    return $this->db
        ->where('is_active', TRUE)
        ->order_by('branch_name')
        ->get($this->table)
        ->result();
  }

  public function getDropdown()
  {
    return $this->db
        ->select('id, branch_name')
        ->where('is_active', TRUE)
        ->order_by('branch_name')
        ->get($this->table)
        ->result();
  }
}