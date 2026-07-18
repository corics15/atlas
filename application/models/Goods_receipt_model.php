<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods_receipt_model extends CI_Model
{

  public function save($grn)
  {
    $this->db->trans_begin();

    try {
      $this->validateReceiveQuantities(
        $grn['details']
      );

      $header = $this->insertHeader($grn);

      $this->insertDetails(
        $header['id'],
        $grn['details'],
      );

      /*** reflect quantity change or table t_purchase_order_details */
      $this->updatePurchaseOrderDetails(
        $grn['details']
      );

      /*** update PO status */
      $this->updatePurchaseOrderStatus(
        $grn['po_id']
      );

      $this->Inventory_model->receive(
        [
          'id'     => $header['id'],
          'grn_no' => $header['grn_no']
        ],
        $grn['details']
      );

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => 'Goods Receipt saved successfully.',
        'data' => [
          'goods_receipt_id' => $header['id'],
          'grn_no' => $header['grn_no']
        ]
      ];

    } catch (Exception $e) {
      $this->db->trans_rollback();

      return [
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
      ];
    }
  }

  public function getAll()
  {
    return $this->db
        ->order_by('grn_date', 'DESC')
        ->order_by('id', 'DESC')
        ->get('v_goods_receipts')
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('v_goods_receipts')
        ->row();
  }

  /*** private functions */
  private function insertHeader($grn)
  {
    $grnNo = 'GRN-' . date('YmdHis');

    $remarks = trim($grn['remarks']) <> ''
      ? strtoupper(trim($grn['remarks']))
      : NULL;

    $sql = "INSERT INTO t_goods_receipts
              (
                grn_no,
                grn_date,
                po_id,
                supplier_id,
                remarks,
                entered_by,
                entered_on
              )
            VALUES
              (
                ?,?,?,?,?,?,
                CURRENT_TIMESTAMP
              )
            RETURNING id";

    $query = $this->db->query(
      $sql,
      [
        $grnNo,
        $grn['grn_date'],
        $grn['po_id'],
        $grn['supplier_id'],
        $remarks,
        $this->session->userdata('user_id')
      ]
    );

    if (!$query || $query->num_rows() === 0) {
      throw new Exception(
        'Unable to save Goods Receipt header.'
      );
    }

    $row = $query->row();

    return [
      'id' => $row->id,
      'grn_no' => $grnNo
    ];
  }

  private function insertDetails($grnId, $details)
  {
    $sql = "INSERT INTO t_goods_receipt_details
              (
                grn_id,
                po_detail_id,
                product_id,
                qty_ordered,
                qty_received,
                unit_cost
              )
            VALUES
              (
                ?,?,?,?,?,?
              )";

    foreach ($details as $detail) {
      $this->db->query(
        $sql,
        [
          $grnId,
          $detail->po_detail_id,
          $detail->product_id,
          $detail->qty_ordered,
          $detail->qty_receive,
          $detail->unit_cost
        ]
      );

      if ($this->db->affected_rows() == 0) {
        throw new Exception(
          'Unable to save Goods Receipt detail.'
        );
      }
    }
  }

  private function updatePurchaseOrderDetails($details)
  {
    $sql = "UPDATE t_purchase_order_details
              SET qty_received = qty_received + ?,
                  updated_by   = ?,
                  updated_on   = CURRENT_TIMESTAMP
            WHERE id = ?";

    foreach ($details as $detail) {
      $query = $this->db->query(
        $sql,
        [
          $detail->qty_receive,
          $this->session->userdata('user_id'),
          $detail->po_detail_id
        ]
      );

      if (!$query) {
        throw new Exception(
          'Unable to update Purchase Order detail.'
        );
      }
    }
  }

  private function updatePurchaseOrderStatus($purchaseOrderId)
  {
    $sql = "SELECT
              COUNT(*) AS total_items,
              SUM(
                CASE
                  WHEN qty_received = 0 THEN 1
                  ELSE 0
                END
              ) AS open_items,
              SUM(
                CASE
                  WHEN qty_received >= qty THEN 1
                  ELSE 0
                END
              ) AS completed_items
            FROM t_purchase_order_details
            WHERE purchase_order_id = ?";

    $query = $this->db->query(
      $sql,
      [$purchaseOrderId]
    );

    if (!$query || $query->num_rows() === 0) {
      throw new Exception(
        'Unable to determine Purchase Order status.'
      );
    }

    $row = $query->row();

    if ($row->completed_items == $row->total_items) {
      $status = 'COMPLETED';
    }
    elseif ($row->open_items == $row->total_items) {
      $status = 'OPEN';
    }
    else {
      $status = 'PARTIAL';
    }

    $query = $this->db->query(
              "UPDATE t_purchase_orders
                  SET status = ?,
                      updated_by = ?,
                      updated_on = CURRENT_TIMESTAMP
                WHERE id = ?",
              [
                $status,
                $this->session->userdata('user_id'),
                $purchaseOrderId
              ]
            );

    if (!$query) {
      throw new Exception(
        'Unable to update Purchase Order status.'
      );
    }
  }

  private function validateReceiveQuantities($details)
  {
    foreach ($details as $detail) {
      $row = $this->db
        ->select('qty, qty_received')
        ->from('t_purchase_order_details')
        ->where('id', $detail->po_detail_id)
        ->get()
        ->row();

      if (!$row) {
        throw new Exception(
          'Purchase Order detail not found.'
        );
      }

      $remaining = $row->qty - $row->qty_received;

      if ($detail->qty_receive > $remaining) {
        throw new Exception(
          'Receive quantity exceeds the remaining quantity.'
        );
      }
    }
  }

}