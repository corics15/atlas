<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_ledger_model extends MY_Model
{

  protected $table = 't_stock_ledger';

  /**
   * Records a stock movement in the stock ledger.
   *
   * @param array $data
   * @return int
   */
  public function record(array $data)
  {
    $this->db->insert($this->table, $data);

    return $this->db->insert_id();
  }

}