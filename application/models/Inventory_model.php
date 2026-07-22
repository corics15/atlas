<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  public function receive($grn, $details)
  {
    $this->validateGoodsReceiptPosting($grn);
    $this->updateQtyOnHand($details);

    $this->writeStockLedger(
      $grn,
      $details
    );

    $this->markGoodsReceiptAsPosted($grn);
  }

  public function getStockLedger($productId)
  {
    return $this->db
      ->select("
        transaction_date,
        transaction_type,
        reference_no,
        qty_in,
        qty_out,
        balance_after,
        unit_cost,
        remarks
      ")
      ->from('t_stock_ledger')
      ->where('product_id', $productId)
      ->order_by('transaction_date ASC, id ASC')
      ->get()
      ->result();
  }

  public function getInventoryList()
  {
    $rows = $this->db
              ->from('v_inventory_inquiry')
              ->order_by('description')
              ->get()
              ->result_array();

    return [
      'success' => true,
      'message' => '',
      'data'    => $rows,
    ];
  }

  /*** private functions */
  private function updateQtyOnHand($details)
  {
    $sql = "UPDATE m_products
               SET qty_on_hand = qty_on_hand + ?,
                   updated_by  = ?,
                   updated_on  = CURRENT_TIMESTAMP
             WHERE id = ?";

    foreach ($details as $detail) {
      $query = $this->db->query(
        $sql,
        [
          $detail->qty_receive,
          $this->session->userdata('user_id'),
          $detail->product_id
        ]
      );

      if (!$query) {
        throw new Exception(
          'Unable to update inventory quantity.'
        );
      }
    }
  }

  private function writeStockLedger($grn, $details)
  {
    $sql = "INSERT INTO t_stock_ledger
              (
                transaction_type,
                reference_id,
                reference_no,
                product_id,
                qty_in,
                qty_out,
                balance_after,
                unit_cost,
                entered_by,
                entered_on
              )
            VALUES
              (
                ?,?,?,?,?,?,?,?,
                ?,CURRENT_TIMESTAMP
              )";

    foreach ($details as $detail) {
      $balance = $this->db
        ->select('qty_on_hand')
        ->from('m_products')
        ->where('id', $detail->product_id)
        ->get()
        ->row()
        ->qty_on_hand;

      $query = $this->db->query(
        $sql,
        [
          'GRN',
          $grn['id'],
          $grn['grn_no'],
          $detail->product_id,
          $detail->qty_receive,
          0,
          $balance,
          $detail->unit_cost,
          $this->session->userdata('user_id')
        ]
      );

      if (!$query) {
        throw new Exception(
          'Unable to write Stock Ledger.'
        );
      }
    }
  }

  private function validateGoodsReceiptPosting($grn)
  {
    $row = $this->db
      ->select('is_posted_to_inventory')
      ->from('t_goods_receipts')
      ->where('id', $grn['id'])
      ->get()
      ->row();

    if (!$row) {
      throw new Exception(
        'Goods Receipt not found.'
      );
    }

    /*** normalize boolean */
    $isPosted = filter_var(
      $row->is_posted_to_inventory,
      FILTER_VALIDATE_BOOLEAN
    );

    if ($isPosted) {
      throw new Exception(
        'Goods Receipt has already been posted to inventory.'
      );
    }
  }

  private function markGoodsReceiptAsPosted($grn)
  {
    $query = $this->db->query(
      "UPDATE t_goods_receipts
          SET status = ?,
              is_posted_to_inventory = TRUE,
              updated_by = ?,
              updated_on = CURRENT_TIMESTAMP
        WHERE id = ?",
      [
        'POSTED',
        $this->session->userdata('user_id'),
        $grn['id']
      ]
    );

    if (!$query) {
      throw new Exception(
        'Unable to update Goods Receipt inventory status.'
      );
    }
  }
}