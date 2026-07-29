<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Branch_inventory_model');
    $this->load->model('Stock_transfer_model');
    $this->load->model('Sales_invoice_model');
  }

  public function receive($grn, $details)
  {
    $this->validateGoodsReceiptPosting($grn);
    // $this->updateQtyOnHand($details);

    foreach ($details as $detail) {
      $this->Branch_inventory_model->adjustBalance(
        $grn['branch_id'],
        $detail->product_id,
        $detail->qty_receive
      );
    }

    $this->writeStockLedger(
      $grn,
      $details
    );

    /*** parked until Sales, Sales Returns, and Purchase Returns are completed. */
    // foreach ($details as $detail) {
    //   $balance = $this->Branch_inventory_model
    //       ->getBalance(
    //         // $grn['branch_id'], /*** parked at the moment */
    //         1,
    //         $detail->product_id
    //       );

    //   $this->writeStockLedger(
    //     // $grn['branch_id'], /*** parked at the moment */
    //     1,
    //     'GRN',
    //     $grn['id'],
    //     $grn['grn_no'],
    //     $detail->product_id,
    //     $detail->qty_receive,
    //     0,
    //     $balance->qty_on_hand,
    //     $detail->unit_cost
    //   );
    // }

    $this->markGoodsReceiptAsPosted($grn);
  }

  public function getStockLedger($productId, $filters = [])
  {
    if (!empty($filters['date_from'])) {
      $this->db->where(
        "transaction_date::date >= '{$filters['date_from']}'",
       NULL,
       FALSE,
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        "transaction_date::date <= '{$filters['date_to']}'",
       NULL,
       FALSE,
      );
    }

    if (!empty($filters['transType'])) {
      $this->db->where(
        'transaction_type',
        $filters['transType']
      );
    }

    return $this->db
      ->select("
          transaction_date,
          transaction_type,
          reference_no,
          qty_in,
          qty_out,
          balance_after,
          unit_cost,
          remarks,
          reference_id
      ")
      ->from('t_stock_ledger')
      ->where('product_id', $productId)
      ->order_by('transaction_date ASC, id ASC')
      ->get()
      ->result();
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("barcode ILIKE '%{$escaped}%'")
        ->or_where("case_barcode ILIKE '%{$escaped}%'")
        ->or_where("description ILIKE '%{$escaped}%'")
        ->or_where("supplier_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->order_by('description')
        ->get('v_inventory_inquiry')
        ->result();
  }

  public function postGoodsReceipt($goodsReceiptId)
  {

  }

  public function postInventoryAdjustment($adjustmentId)
  {

  }

  public function postStockTransfer($stockTransferId)
  {
    $header = $this->Stock_transfer_model->get($stockTransferId);
    $details = $this->Stock_transfer_model->getDetails($stockTransferId);

    foreach ($details as $detail) {

      /*** deduct from source */
      $this->Branch_inventory_model->adjustBalance(
        $header->from_branch_id,
        $detail->product_id,
        -$detail->qty
      );

      /*** add to destination */
      $this->Branch_inventory_model->adjustBalance(
        $header->to_branch_id,
        $detail->product_id,
        $detail->qty
      );
    }

    return [
      'success' => TRUE,
      'message' => ''
    ];
  }

  public function postSales($salesInvoiceId)
  {
    $branchId = (int) $this->session->userdata('branch_id');
    $header = $this->Sales_invoice_model->get($salesInvoiceId);

    if (!$header) {
      return [
        'success' => FALSE,
        'message' => 'Sales Invoice not found.'
      ];
    }

    $details = $this->Sales_invoice_model->getDetails($salesInvoiceId);

    /*** quantity balance validation */
    foreach ($details as $detail)
    {
      $balance = $this->Branch_inventory_model->getBalance(
        $branchId,
        $detail->product_id
      );

      $available = $balance ? $balance->qty_on_hand : 0;
      if ($available < $detail->qty) {
        return [
          'success' => FALSE,
          'message' =>
            "Insufficient stock.\n\n" .
            "{$detail->description}\n\n" .
            "Available : {$available}\n" .
            "Required  : {$detail->qty}"
        ];
      }
    }

    /*** deduct inventory */
    foreach ($details as $detail)
    {
      $this->Branch_inventory_model->adjustBalance(
        $branchId,
        $detail->product_id,
        -$detail->qty
      );
    }
    return [
      'success' => TRUE,
      'message' => ''
    ];
  }

  public function reverseTransaction($transactionType, $referenceId)
  {

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

  // private function writeStockLedger(
  //     $branchId,
  //     $transactionType,
  //     $referenceId,
  //     $referenceNo,
  //     $productId,
  //     $qtyIn,
  //     $qtyOut,
  //     $balanceAfter,
  //     $unitCost = 0,
  //     $remarks = NULL
  // )
  // {
  //     $query = $this->db->insert(
  //       't_stock_ledger',
  //       [
  //         'branch_id'        => $branchId,
  //         'transaction_type' => strtoupper($transactionType),
  //         'reference_id'     => $referenceId,
  //         'reference_no'     => $referenceNo,
  //         'product_id'       => $productId,
  //         'qty_in'           => $qtyIn,
  //         'qty_out'          => $qtyOut,
  //         'balance_after'    => $balanceAfter,
  //         'unit_cost'        => $unitCost,
  //         'remarks'          => $remarks,
  //         'entered_by'       => $this->session->userdata('user_id'),
  //         'entered_on'       => date('Y-m-d H:i:s')
  //       ]
  //     );

  //     if (!$query) {
  //       throw new Exception(
  //         'Unable to write Stock Ledger.'
  //       );
  //     }
  // }

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