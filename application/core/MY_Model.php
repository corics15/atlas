<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model
{
  protected $branchId;

  public function __construct()
  {
    parent::__construct();

    $this->branchId = (int) $this->session->userdata('branch_id');
  }
}