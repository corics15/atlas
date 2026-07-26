<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Branch_inventory_model extends MY_Model
{
  protected $table = 't_branch_inventory';

  public function getBalance($branchId, $productId)
  {
    return $this->db
        ->where('branch_id', $branchId)
        ->where('product_id', $productId)
        ->get($this->table)
        ->row();
  }

  public function setBalance($branchId, $productId, $qtyOnHand, $avgCost = 0)
  {
    $record = $this->getBalance(
      $branchId,
      $productId
    );

    if ($record) {
      $this->db
          ->where('id', $record->id)
          ->update(
              $this->table,
              [
                'qty_on_hand' => $qtyOnHand,
                'avg_cost'    => $avgCost,
                'updated_on'  => date('Y-m-d H:i:s')
              ]
          );

    } else {

      $this->db->insert(
        $this->table,
        [
          'branch_id'   => $branchId,
          'product_id'  => $productId,
          'qty_on_hand' => $qtyOnHand,
          'avg_cost'    => $avgCost,
          'updated_on'  => date('Y-m-d H:i:s')
        ]
      );
    }
  }

  public function adjustBalance($branchId, $productId, $qtyChange)
  {
    $balance = $this->getBalance(
      $branchId,
      $productId
    );

    if ($balance) {
      $this->setBalance(
          $branchId,
          $productId,
          $balance->qty_on_hand + $qtyChange,
          $balance->avg_cost
      );

    } else {
        $this->setBalance(
          $branchId,
          $productId,
          $qtyChange,
          0
        );
    }
  }
}