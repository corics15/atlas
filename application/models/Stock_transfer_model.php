<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_transfer_model extends CI_Model
{
  protected $table = 't_stock_transfers';

  public function __construct()
  {
    parent::__construct();
  }
}