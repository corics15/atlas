<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods_receipt_model extends CI_Model
{

  /*** initial status is DRAFT */
  public function save($grn)
  {
    $this->db->trans_begin();

    try {
      $draft = $this->db
                  ->where('po_id', $grn['po_id'])
                  ->where('status', 'DRAFT')
                  ->get('t_goods_receipts')
                  ->row_array();

      if ($draft) {
        throw new Exception(
          'A draft Goods Receipt already exists for this Purchase Order.'
        );
      }

      $this->validateReceiveQuantities(
        $grn['details']
      );

      /*** insert GRN header */
      $header = $this->insertHeader($grn);

      /*** insert GRN details */
      $this->insertDetails(
        $header['id'],
        $grn['details'],
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

  /*** updates the DRAFT */
  public function update($request)
  {
    $this->db->trans_begin();

    try {

      $this->validateDraftGoodsReceipt($request['id']);

      /*** update header */
      $this->db
        ->where('id', $request['id'])
        ->update(
          't_goods_receipts',
          [
            'remarks'     => trim($request['remarks']) <> '' ? strtoupper(trim($request['remarks'])) : NULL,
            'updated_by'  => $request['updated_by'],
            'updated_on'  => date('Y-m-d H:i:s')
          ]
        );

      /*** update details */
      foreach ($request['details'] as $detail) {

        $this->db
          ->where('id', $detail['id'])
          ->update(
            't_goods_receipt_details',
            [
              'qty_received' => $detail['qty_received']
            ]
          );

      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to update Goods Receipt.');
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => 'Goods Receipt updated successfully.',
        'data'    => [
          'goods_receipt_id' => $request['id']
        ]
      ];

    } catch (Exception $e) {
      $this->db->trans_rollback();

      return [
        'success' => false,
        'message' => $e->getMessage(),
        'data'    => null
      ];
    }
  }

  public function post($request)
  {
    $this->db->trans_begin();

    try {
      $this->validateDraftGoodsReceipt($request['id']);

      $grn = $this->db
        ->where('id', $request['id'])
        ->get('t_goods_receipts')
        ->row_array();

      $details = $this->db
                  ->select("
                    po_detail_id,
                    product_id,
                    qty_received AS qty_receive,
                    qty_ordered,
                    unit_cost
                  ")
                  ->from('t_goods_receipt_details')
                  ->where('grn_id', $request['id'])
                  ->get()
                  ->result();

      if (count($details) === 0) {
        throw new Exception('There are no items to post.');
      }

      $this->validateReceiveQuantities($details);
      $this->load->model('Inventory_model');

      $this->Inventory_model->receive($grn, $details);
      $this->updatePurchaseOrderDetails($details);
      $this->updatePurchaseOrderStatus($grn['po_id']);

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => 'Goods Receipt posted successfully.',
        'data' => [
          'id' => $request['id']
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

  public function cancel($request)
  {
    try {
      $id = (int) ($request['id'] ?? 0);

      if ($id <= 0) {
        throw new Exception('Invalid Goods Receipt.');
      }

      $this->db->trans_begin();

      $grn = $this->db
          ->where('id', $id)
          ->get('t_goods_receipts')
          ->row();

      if (!$grn) {
        throw new Exception('Goods Receipt not found.');
      }

      if ($grn->status !== 'DRAFT') {
        throw new Exception('Only DRAFT Goods Receipts can be cancelled.');
      }

      $this->db
        ->where('id', $id)
        ->update('t_goods_receipts', [
            'status'     => 'CANCELLED',
            'updated_by' => $this->session->userdata('user_id'),
            'updated_on' => date('Y-m-d H:i:s')
        ]);

      if (!$this->db->trans_status()) {
        throw new Exception('Failed to cancel Goods Receipt.');
      }

      $this->db->trans_commit();

      return [
          'success' => true,
          'message' => 'Goods Receipt cancelled successfully.',
          'data'    => [
              'id'     => $id,
              'status' => 'CANCELLED'
          ]
      ];

    } catch (Exception $e) {
      $this->db->trans_rollback();

      return [
        'success' => false,
        'message' => $e->getMessage(),
        'data'    => null
      ];
    }
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("grn_no ILIKE '%{$escaped}%'")
        ->or_where("po_no ILIKE '%{$escaped}%'")
        ->or_where("supplier_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'grn_date >=',
        $filters['date_from']
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'grn_date <=',
        $filters['date_to']
      );
    }

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

  public function getDetails($grnId)
  {
    return $this->db
        ->select('
            d.*,
            p.barcode,
            p.description,
            u.uom
        ')
        ->from('t_goods_receipt_details d')
        ->join('m_products p', 'p.id = d.product_id')
        ->join('m_uom u', 'u.id = p.uom_id')
        ->where('d.grn_id', $grnId)
        ->order_by('d.id')
        ->get()
        ->result();
  }

  /*** check if a DRAFT already exists for a certain PO id */
  public function getDraftByPurchaseOrder($purchaseOrderId)
  {
    return $this->db
                ->where('po_id', $purchaseOrderId)
                ->where('status', 'DRAFT')
                ->get('t_goods_receipts')
                ->row_array();
  }

  /*** private functions */
  private function insertHeader($grn)
  {
    $grnNo = 'GRN-' . date('YmdHis'); /*** generateGRN */

    $remarks = trim($grn['remarks']) <> ''
      ? strtoupper(trim($grn['remarks']))
      : NULL;

    $sql = "INSERT INTO t_goods_receipts
              (
                grn_no,
                grn_date,
                po_id,
                supplier_id,
                status,
                is_posted_to_inventory,
                remarks,
                entered_by,
                entered_on
              )
            VALUES
              (
                ?,?,?,?,?,?,?,?,
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
        'DRAFT',
        FALSE,
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

  private function validateDraftGoodsReceipt($id)
  {
    $grn = $this->db
        ->where('id', $id)
        ->get('t_goods_receipts')
        ->row();

    if (!$grn) {
      throw new Exception('Goods Receipt not found.');
    }

    if ($grn->status !== 'DRAFT') {
      throw new Exception('Only DRAFT Goods Receipts can perform this operation.');
    }

    return $grn;
  }
}