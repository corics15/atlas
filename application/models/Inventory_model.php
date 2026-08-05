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
    $this->load->model('Sales_return_model');
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

    if (!empty($filters['branch_id'])) {
      $this->db->where(
        'sl.branch_id',
        $filters['branch_id']
      );
    }

    return $this->db
      ->select("
        sl.transaction_date,
        sl.transaction_type,
        sl.reference_no,
        sl.qty_in,
        sl.qty_out,
        sl.balance_after,
        sl.unit_cost,
        sl.remarks,
        sl.reference_id,
        b.branch_name
      ")
      ->from('t_stock_ledger sl')
      ->join(
        'm_branches b',
        'b.id = sl.branch_id',
        'left'
      )
      ->where('sl.product_id', $productId)
      ->order_by('sl.transaction_date ASC, sl.id ASC')
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

  /*** post post goods receipt */
  public function postGoodsReceipt($grn, $details)
  {
    $this->validateGoodsReceiptPosting($grn);

    foreach ($details as $detail) {
      $this->Branch_inventory_model->adjustBalance(
        $grn['branch_id'],
        $detail->product_id,
        $detail->qty_receive
      );
    }

    $this->writeStockLedger(
      $grn['branch_id'],
      'GRN',
      $grn['id'],
      $grn['grn_no'],
      $details,
      'qty_receive',
      NULL,
      'unit_cost'
    );

    $this->markGoodsReceiptAsPosted($grn);
  }

  public function postInventoryAdjustment($adjustmentId)
  {

  }

  /*** post stock transfer */
  public function postStockTransfer($stockTransferId)
  {
    $this->db->trans_begin();

    try {

      $header = $this->Stock_transfer_model->get($stockTransferId);

      if ($header->status != 'OPEN') {
        throw new Exception(
          "Stock Transfer is already {$header->status}."
        );
      }

      if (!$header) {
        throw new Exception('Stock Transfer not found.');
      }

      $details = $this->Stock_transfer_model->getDetails($stockTransferId);

      foreach ($details as $detail)
      {
        /*** deduct from source */
        $this->Branch_inventory_model->adjustBalance(
            $header->from_branch_id,
            $detail->product_id,
            -$detail->qty
        );

        $this->writeStockLedger(
            $header->from_branch_id,
            'TRANSFER',
            $header->id,
            $header->transfer_no,
            [$detail],
            NULL,
            'qty'
        );

        /*** add to destination */
        $this->Branch_inventory_model->adjustBalance(
            $header->to_branch_id,
            $detail->product_id,
            $detail->qty
        );

        $this->writeStockLedger(
            $header->to_branch_id,
            'TRANSFER',
            $header->id,
            $header->transfer_no,
            [$detail],
            'qty',
            NULL
        );
      }

      /*** set status */
      $this->db
          ->where('id', $stockTransferId)
          ->update(
              't_stock_transfers',
              [
                'status' => 'POSTED',
                'updated_by' => $this->session->userdata('user_id'),
                'updated_on' => date('Y-m-d H:i:s')
              ]
          );

      if ($this->db->trans_status() === FALSE) {
        throw new Exception(
          'Unable to post Stock Transfer.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => '',
        'data' => [],
      ];

    } catch (Exception $ex) {

      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data' => [],
      ];
    }
  }

  /*** post sales */
  public function postSales($salesInvoiceId)
  {
    $this->db->trans_begin();

    try
    {
      $branchId = (int) $this->session->userdata('branch_id');
      $header = $this->Sales_invoice_model->get($salesInvoiceId);

      if ($header->status != 'OPEN') {
        return [
          'success' => FALSE,
          'message' => "Sales Invoice is already {$header->status}."
        ];
      }

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

        $this->writeStockLedger($branchId, 'SI', $header->id, $header->si_no, [$detail], NULL, 'qty');
      }


      if ($this->db->trans_status() === FALSE) {
        throw new Exception(
          'Unable to post Sales Invoice.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => ''
      ];

    } catch (Exception $ex) {

        $this->db->trans_rollback();

        return [
          'success' => FALSE,
          'message' => $ex->getMessage()
        ];
    }
  }

  /*** post sales return */
  public function postSalesReturn($salesReturnId)
  {
    $this->db->trans_begin();

    try {

      $branchId = (int)$this->session->userdata('branch_id');

      $header = $this->Sales_return_model->get($salesReturnId);

      if (!$header) {
        return [
          'success' => FALSE,
          'message' => 'Sales Return not found.'
        ];
      }

      if ($header->status != 'OPEN') {
        return [
          'success' => FALSE,
          'message' => "Sales Return is already {$header->status}."
        ];
      }

      $details = $this->Sales_return_model->getDetails($salesReturnId);
      foreach ($details as $detail)
      {

        /*** update t_branch_inventory */
        $this->Branch_inventory_model->adjustBalance(
          $branchId,
          $detail->product_id,
          $detail->qty
        );

        /*** update stock ledger */
        $this->writeStockLedger(
            $branchId,
            'SR',
            $header->id,
            $header->sr_no,
            [$detail],
            'qty',
            NULL
        );
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to post Sales Return.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Return posted.'
      ];

    } catch (Exception $ex) {

      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage()
      ];
    }
  }

  /*** post purchase return */
  public function postPurchaseReturn($purchaseReturnId)
  {
    $this->db->trans_begin();

    try {

      $branchId = (int)$this->session->userdata('branch_id');

      $header = $this->Purchase_return_model->get($purchaseReturnId);

      if (!$header) {
        return [
          'success' => FALSE,
          'message' => 'Purchase Return not found.'
        ];
      }

      if ($header->status != 'OPEN') {
        return [
          'success' => FALSE,
          'message' => "Purchase Return is already {$header->status}."
        ];
      }

      $details = $this->Purchase_return_model->getDetails($purchaseReturnId);

      foreach ($details as $detail) {

        /*** validate available stock */
        $inventory = $this->Branch_inventory_model->getBalance(
            $branchId,
            $detail->product_id
        );

        $qtyOnHand = $inventory ? $inventory->qty_on_hand : 0;

        if ($qtyOnHand < $detail->qty) {
          throw new Exception(
            "{$detail->description} has insufficient stock."
          );
        }

        /*** deduct branch inventory */
        $this->Branch_inventory_model->adjustBalance(
            $branchId,
            $detail->product_id,
            -$detail->qty
        );

        /*** stock ledger */
        $this->writeStockLedger(
            $branchId,
            'PR',
            $header->id,
            $header->pr_no,
            [$detail],
            'qty',
            NULL
        );
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to post Purchase Return.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Purchase Return posted.'
      ];

    } catch (Exception $ex) {

      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage()
      ];
    }
  }

  /*** post delivery receipt */
  public function postDeliveryReceipt($deliveryReceiptId)
  {
    $this->db->trans_begin();

    try
    {
      $header = $this->Delivery_receipt_model->get($deliveryReceiptId);

      if (!$header) {
        return [
          'success' => FALSE,
          'message' => 'Delivery Receipt not found.'
        ];
      }

      if ($header->status != 'OPEN') {
        return [
          'success' => FALSE,
          'message' => "Delivery Receipt is already {$header->status}."
        ];
      }

      $branchId = (int)$header->branch_id;
      $details = $this->Delivery_receipt_model->getDetails($deliveryReceiptId);

      /*** quantity validation */
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

        $this->writeStockLedger(
            $branchId,
            'DR',
            $header->id,
            $header->dr_no,
            [$detail],
            NULL,
            'qty'
        );
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception(
          'Unable to post Delivery Receipt.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => ''
      ];

    }
    catch (Exception $ex) {

      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage()
      ];
    }
  }

  /*** reverse transaction */
  public function reverseTransaction($transactionType, $referenceId)
  {
    try {

      switch ($transactionType) {
        case 'DR':
          $header = $this->Delivery_receipt_model->get($referenceId);
          $details = $this->Delivery_receipt_model->getDetails($referenceId);
          $referenceNo = $header->dr_no;
          break;

        /*
        case 'PR':
            ...
            break;

        case 'SI':
            ...
            break;
        */

        default:
          throw new Exception('Unsupported transaction type.');

      }

      $branchId = (int)$header->branch_id;

      foreach ($details as $detail)
      {
        /*** restore inventory */
        $this->Branch_inventory_model->adjustBalance(
            $branchId,
            $detail->product_id,
            $detail->qty_reverse
        );

        /*** write reversal ledger */
        $this->writeStockLedger(
            $branchId,
            $transactionType . '-CANCEL',
            $header->id,
            $referenceNo,
            [$detail],
            NULL,
            'qty_reverse'
        );
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to reverse inventory transaction.'
        );
      }

      return [
        'success' => TRUE,
        'message' => ''
      ];

    }
    catch (Exception $ex) {

      return [
        'success' => FALSE,
        'message' => $ex->getMessage()
      ];

    }
  }

  /*** write stock ledger */
  public function writeStockLedger($branchId, $transactionType, $referenceId, $referenceNo, $details, $qtyInField, $qtyOutField, $unitCostField = NULL)
  {
    $sql = "INSERT INTO t_stock_ledger
            (
              branch_id,
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
              ?,?,?,?,?,?,?,?,?,?,
              CURRENT_TIMESTAMP
            )";

    foreach ($details as $detail)
    {
      $balanceInventory = $this->Branch_inventory_model->getBalance(
        $branchId,
        $detail->product_id
      );
      $balanceAfter = $balanceInventory ? $balanceInventory->qty_on_hand : 0;

      $qtyIn = $qtyInField ? $detail->{$qtyInField} : 0;
      $qtyOut = $qtyOutField ? $detail->{$qtyOutField} : 0;
      $unitCost = $unitCostField ? $detail->{$unitCostField} : 0;

      $query = $this->db->query(
        $sql,
        [
          $branchId,
          strtoupper($transactionType),
          $referenceId,
          $referenceNo,
          $detail->product_id,
          $qtyIn,
          $qtyOut,
          $balanceAfter,
          $unitCost,
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

  private function writeStockLedger_old($grn, $details)
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

      $balance = $this->Branch_inventory_model->getBalance($grn['branch_id'], $detail->product_id);
      $balance = $balance ? $balance->qty_on_hand : 0;

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
}