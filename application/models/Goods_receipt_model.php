<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods_receipt_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Inventory_model');
    $this->load->model('Document_number_model');
  }

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
              'qty_received'      => $detail['qty_received'],
              'conversion_factor' => $detail['conversion_factor']
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

      /*** validate source Purchase Order */
      $purchaseOrder = $this->Purchase_order_model->get($grn['po_id']);

      if (!$purchaseOrder) {
        throw new Exception('Source Purchase Order not found.');
      }

      if (!in_array($purchaseOrder['header']->status, ['OPEN', 'PARTIAL'])) {
        throw new Exception(
          "Cannot post Goods Receipt. Purchase Order {$purchaseOrder['header']->po_no} is {$purchaseOrder['header']->status}."
        );
      }

      $details = $this->db
                  ->select("
                      po_detail_id,
                      product_id,
                      uom_id,
                      conversion_factor,
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

      /*** finalize product UOM conversions */
      foreach ($details as $detail) {
        $product = $this->db
            ->select('uom_id')
            ->where('id', $detail->product_id)
            ->get('m_products')
            ->row();

        if (!$product) {
          throw new Exception('Product not found.');
        }

        /*** base UOM always has conversion 1 */
        if ((int)$product->uom_id === (int)$detail->uom_id) {

          if ((float)$detail->conversion_factor !== 1.0) {
            throw new Exception(
              'Invalid conversion for product base UOM.'
            );
          }
          continue;
        }

        if ((float)$detail->conversion_factor <= 0) {
          throw new Exception('Invalid UOM conversion.');
        }

        $productUom = $this->Product_uom_model->get(
          $detail->product_id,
          $detail->uom_id
        );

        /*** unknown relationship: learn it on POST */
        if (!$productUom) {
          if (!$this->Product_uom_model->save(
            $detail->product_id,
            $detail->uom_id,
            $detail->conversion_factor
          )) {
            throw new Exception(
              'Unable to save product UOM conversion.'
            );
          }
          continue;
        }

        /*** conversion still matches current default */
        if (
          (float)$productUom->conversion_factor ===
          (float)$detail->conversion_factor
        ) {
          continue;
        }

        /*** conversion differs: browser must have supplied a decision */
        $decision = NULL;
        foreach ($request['conversion_decisions'] ?? [] as $item) {
          if (
            (int)$item['product_id'] === (int)$detail->product_id &&
            (int)$item['uom_id'] === (int)$detail->uom_id
          ) {
            $decision = $item;
            break;
          }
        }

        if (!$decision) {
          throw new Exception(
            'A conversion decision is required before posting.'
          );
        }

        /*** protect against manipulated/stale browser values */
        if (
          (float)$decision['conversion_factor'] !==
          (float)$detail->conversion_factor
        ) {
          throw new Exception(
            'Goods Receipt conversion has changed. Please review before posting.'
          );
        }

        /*** THIS GR ONLY */
        if (empty($decision['update_default_conversion'])) {
          continue;
        }

        /*** UPDATE DEFAULT */
        if (!$this->Product_uom_model->save(
          $detail->product_id,
          $detail->uom_id,
          $detail->conversion_factor
        )) {
          throw new Exception(
            'Unable to update product UOM conversion.'
          );
        }
      }
      /*** end finalize */

      $this->Inventory_model->postGoodsReceipt($grn, $details);
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
            'cancelled_by' =>  $this->session->userdata('user_id'),
            'cancelled_on' => date('Y-m-d H:i:s'),
            'cancel_reason' => $request['cancel_reason'] <> '' ? strtoupper(trim($request['cancel_reason'])) : NULL,
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
    } else {
      $this->db->where(
        'grn_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'grn_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'grn_date <=',
        date('Y-m-d')
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'status',
        $filters['status']
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
              ->select("
                d.*,
                p.barcode,
                p.description,
                p.uom_id AS base_uom_id,
                u.uom,
                pu.conversion_factor AS default_conversion
              ")
              ->from('t_goods_receipt_details d')
              ->join('m_products p', 'p.id = d.product_id')
              ->join('m_uom u', 'u.id = d.uom_id', 'left')
              ->join(
                'm_product_uom pu',
                'pu.product_id = d.product_id AND pu.uom_id = d.uom_id AND pu.is_active = TRUE',
                'left',
                FALSE
              )
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
    $grnNo = $this->Document_number_model->generate('GRN');
    $branchId = (int) $this->session->userdata('branch_id');

    $remarks = trim($grn['remarks']) <> ''
      ? strtoupper(trim($grn['remarks']))
      : NULL;

    $sql = "INSERT INTO t_goods_receipts
              (
                grn_no,
                grn_date,
                po_id,
                supplier_id,
                branch_id,
                status,
                is_posted_to_inventory,
                remarks,
                entered_by,
                entered_on
              )
            VALUES
              (
                ?,?,?,?,?,?,?,?,?,
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
        $branchId,
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
                uom_id,
                conversion_factor,
                qty_ordered,
                qty_received,
                unit_cost
              )
            VALUES
              (
                ?,?,?,?,?,?,?,?
              )";

    foreach ($details as $detail) {
      $this->db->query(
        $sql,
        [
          $grnId,
          $detail->po_detail_id,
          $detail->product_id,
          $detail->uom_id,
          $detail->conversion_factor,
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
    if (empty($details)) {
      return;
    }

    /*** validate at least one received quantity */
    $hasReceivedQty = FALSE;

    foreach ($details as $detail) {
      if ((float)$detail->qty_receive > 0) {
        $hasReceivedQty = TRUE;
        break;
      }
    }

    if (!$hasReceivedQty) {
      throw new Exception(
        'Please enter a received quantity for at least one item.'
      );
    }

    /*** collect all po_detail_ids from the details array */
    $ids = array_map(function($d) {
        return $d->po_detail_id;
    }, $details);

    /*** fetch all rows in 1 query */
    $rows = $this->db
        ->select('id, qty, qty_received')
        ->from('t_purchase_order_details')
        ->where_in('id', $ids)
        ->get()
        ->result();

    $indexed = [];
    foreach ($rows as $row) {
      $indexed[$row->id] = $row;
    }

    foreach ($details as $detail) {
      if (!isset($indexed[$detail->po_detail_id])) {
        throw new Exception('Purchase Order detail not found.');
      }

      $row = $indexed[$detail->po_detail_id];
      $remaining = $row->qty - $row->qty_received;

      if ($detail->qty_receive > $remaining) {
        throw new Exception('Receive quantity exceeds the remaining quantity.');
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