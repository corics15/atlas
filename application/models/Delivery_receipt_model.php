<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery_receipt_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Inventory_model');
    $this->load->model('Branch_inventory_model');
    $this->load->model('Document_number_model');
  }

  public function getSalesOrder($salesOrderId)
  {
    return $this->db
        ->select("
            so.*,
            c.customer_name,
            CONCAT(s.first_name,' ',s.last_name) AS salesman_name,
            t.terms_name,
            so.status AS so_status
        ")
        ->from('t_sales_orders so')
        ->join('m_customers c','c.id=so.customer_id')
        ->join('m_salesmen s','s.id=so.salesman_id','left')
        ->join('m_terms t','t.id=so.terms_id','left')
        ->where('so.id', $salesOrderId)
        ->get()
        ->row();
  }

  public function getSalesOrderDetails($salesOrderId)
  {
    $branchId = (int)$this->session->userdata('branch_id');

    return $this->db->query("SELECT
                              sod.id AS sales_order_detail_id,
                              sod.product_id,
                              sod.uom_id,
                              sod.conversion_factor,
                              p.uom_id AS base_uom_id,
                              p.barcode,
                              p.description,
                              u.uom,
                              sod.qty AS qty_ordered,
                              COALESCE(inv.qty_on_hand, 0) qty_available,
                              COALESCE(dr.qty_delivered, 0) qty_delivered,
                              COALESCE(res.qty_reserved, 0) qty_reserved,
                              (sod.qty - COALESCE(dr.qty_delivered, 0)) qty_remaining,
                              (sod.qty - COALESCE(dr.qty_delivered, 0) - COALESCE(res.qty_reserved, 0)) qty_available_to_deliver
                          FROM t_sales_order_details sod
                          INNER JOIN m_products p ON p.id = sod.product_id
                          LEFT JOIN m_uom u ON u.id = sod.uom_id
                          LEFT JOIN t_branch_inventory inv ON inv.product_id=p.id AND inv.branch_id = ?
                          LEFT JOIN
                          (
                            SELECT
                                drd.sales_order_detail_id,
                                SUM(drd.qty) qty_delivered
                            FROM t_delivery_receipt_details drd
                            INNER JOIN t_delivery_receipts dr ON dr.id=drd.delivery_receipt_id
                            WHERE dr.status = 'POSTED'
                            GROUP BY drd.sales_order_detail_id
                          ) dr ON dr.sales_order_detail_id = sod.id
                          LEFT JOIN
                          (
                            SELECT
                                drd.sales_order_detail_id,
                                SUM(drd.qty) AS qty_reserved
                            FROM t_delivery_receipt_details drd
                            INNER JOIN t_delivery_receipts dr
                                ON dr.id = drd.delivery_receipt_id
                            WHERE dr.status = 'OPEN'
                            GROUP BY drd.sales_order_detail_id
                          ) res ON res.sales_order_detail_id = sod.id
                          WHERE sod.sales_order_id = ?
                          ORDER BY sod.id
                        ",
                        [
                          $branchId,
                          $salesOrderId
                        ])->result();
  }

  public function getDetails($deliveryReceiptId)
  {
    $branchId = (int)$this->session->userdata('branch_id');

    return $this->db->query("SELECT
                                drd.id,
                                drd.sales_order_detail_id,
                                drd.product_id,
                                drd.uom_id,
                                drd.conversion_factor,
                                p.uom_id AS base_uom_id,
                                p.barcode,
                                p.description,
                                u.uom,
                                sod.qty AS qty_ordered,
                                drd.qty AS qty_delivered,
                                COALESCE(inv.qty_on_hand, 0) AS qty_available,
                                0 AS qty_remaining,
                                0 AS qty_available_to_deliver,
                                (drd.qty * drd.conversion_factor) AS qty_reverse -- used for cancellation events
                              FROM t_delivery_receipt_details drd
                              INNER JOIN t_sales_order_details sod
                                ON sod.id = drd.sales_order_detail_id
                              INNER JOIN m_products p
                                ON p.id = drd.product_id
                              LEFT JOIN m_uom u
                                ON u.id = drd.uom_id
                              LEFT JOIN t_branch_inventory inv
                                ON inv.product_id = drd.product_id
                                AND inv.branch_id = ?
                              WHERE drd.delivery_receipt_id = ?
                              ORDER BY drd.id
                            ",
                            [
                              $branchId,
                              $deliveryReceiptId
                            ])->result();
  }

  public function get($id)
  {
    return $this->db
        ->select("
            dr.*,
            so.so_no,
            so.order_date,
            so.salesman_id,
            so.terms_id,
            so.remarks AS so_remarks,
            c.customer_name,
            CONCAT(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name,
            so.status AS so_status
        ")
        ->from('t_delivery_receipts dr')
        ->join('t_sales_orders so', 'so.id = dr.sales_order_id')
        ->join('m_customers c', 'c.id = dr.customer_id')
        ->join('m_salesmen s', 's.id = so.salesman_id', 'left')
        ->join('m_terms t', 't.id = so.terms_id', 'left')
        ->where('dr.id', $id)
        ->get()
        ->row();
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
          ->where("dr.dr_no ILIKE '%{$escaped}%'")
          ->or_where("so.so_no ILIKE '%{$escaped}%'")
          ->or_where("c.customer_name ILIKE '%{$escaped}%'")
          ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'dr.delivery_date >=',
        $filters['date_from']
      );
    } else {
      $this->db->where(
        'dr.delivery_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'dr.delivery_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'dr.delivery_date <=',
        date('Y-m-d')
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'dr.status',
        $filters['status']
      );
    }

    return $this->db
        ->select("
            dr.*,
            so.so_no,
            c.customer_name,
            so.status AS so_status,
            so.id AS so_id,
            so.total_amount,
            (SELECT count(*) FROM t_sales_order_details sod WHERE sod.sales_order_id = so.id) AS item_count            
        ")
        ->from('t_delivery_receipts dr')
        ->join(
            't_sales_orders so',
            'so.id = dr.sales_order_id',
        )
        ->join(
            'm_customers c',
            'c.id = dr.customer_id'
        )
        ->order_by(
            'dr.id',
            'DESC'
        )
        ->get()
        ->result();
  }

  public function save($deliveryReceipt)
  {
    try {

      $this->db->trans_begin();

      if (empty($deliveryReceipt->id)) {

        /*** insert header */
        $header = [
          'dr_no'           => $this->Document_number_model->generate('DR'),
          'delivery_date'   => $deliveryReceipt->delivery_date,
          'sales_order_id'  => (int)$deliveryReceipt->sales_order_id,
          'customer_id'     => (int)$deliveryReceipt->customer_id,
          'branch_id'       => (int)$this->session->userdata('branch_id'),
          'remarks'         => trim($deliveryReceipt->remarks) <> '' ? strtoupper(trim($deliveryReceipt->remarks)) : NULL,
          'status'          => 'OPEN',
          'entered_by'      => $this->session->userdata('user_id'),
          'entered_on'      => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('t_delivery_receipts', $header);

        $deliveryReceiptId = $this->db->insert_id();
        $drNo = $header['dr_no'];

        /*** synchronize details */
        $this->db
            ->where('delivery_receipt_id', $deliveryReceiptId)
            ->delete('t_delivery_receipt_details');

        $hasQty = FALSE;

        foreach ($deliveryReceipt->details as $detail)
        {
          if ((float)$detail->qty <= 0) {
            continue;
          }

          /*** validate DELIVER vs AVAILABLE TO DELIVER quantity */
          $availableToDeliver = $this->getAvailableToDeliver(
            $detail->sales_order_detail_id,
            empty($deliveryReceipt->id) ? 0 : $deliveryReceipt->id
          );

          if ((float)$detail->qty > $availableToDeliver) {
            throw new Exception(
              "{$detail->description} exceeds the available quantity to deliver."
            );
          }
          /*** end validate */

          $hasQty = TRUE;

          /*** validate source SO snapshot */
            $salesOrderDetail = $this->db
                ->select('
                  product_id,
                  uom_id,
                  conversion_factor
                ')
                ->where('id', $detail->sales_order_detail_id)
                ->get('t_sales_order_details')
                ->row();

            if (!$salesOrderDetail) {
              throw new Exception(
                'Sales Order detail not found.'
              );
            }

            if (
              (int)$detail->product_id !==
              (int)$salesOrderDetail->product_id
            ) {
              throw new Exception(
                'Delivery Receipt product does not match the Sales Order.'
              );
            }

            if (
              (int)$detail->uom_id !==
              (int)$salesOrderDetail->uom_id
            ) {
              throw new Exception(
                'Delivery Receipt UOM does not match the Sales Order.'
              );
            }

            if (
              (float)$detail->conversion_factor !==
              (float)$salesOrderDetail->conversion_factor
            ) {
              throw new Exception(
                'Delivery Receipt conversion does not match the Sales Order.'
              );
            }
          /*** end validate */

          $this->db->insert(
            't_delivery_receipt_details',
            [
              'delivery_receipt_id'   => $deliveryReceiptId,
              'sales_order_detail_id' => $detail->sales_order_detail_id,
              'product_id'            => $detail->product_id,
              'uom_id'             => $detail->uom_id,
              'conversion_factor'  => $detail->conversion_factor,
              'qty'                   => $detail->qty,
              'remarks'               => NULL
            ]
          );
        }

        if (!$hasQty) {
          throw new Exception(
            'Please enter a quantity for at least one item.'
          );
        }

      }
      else {

        /***
         * Update HEADER only.
         * DETAIL rows are immutable.
         */
        $header = [
          'delivery_date' => $deliveryReceipt->delivery_date,
          'remarks'       => trim($deliveryReceipt->remarks) <> '' ? strtoupper(trim($deliveryReceipt->remarks)) : NULL,
          'updated_by'    => $this->session->userdata('user_id'),
          'updated_on'    => date('Y-m-d H:i:s')
        ];

        $exists = $this->db
            ->where('id', $deliveryReceipt->id)
            ->where('status', 'OPEN')
            ->count_all_results('t_delivery_receipts');

        if ($exists == 0) {
          throw new Exception(
            'Delivery Receipt can no longer be updated.'
          );
        }
        $this->db
            ->where('id', $deliveryReceipt->id)
            ->where('status', 'OPEN')
            ->update(
                't_delivery_receipts',
                $header
            );

        $deliveryReceiptId = $deliveryReceipt->id;

        $drNo = $this->db
            ->select('dr_no')
            ->from('t_delivery_receipts')
            ->where('id', $deliveryReceiptId)
            ->get()
            ->row()
            ->dr_no;

        /*** end HEADER update */
      }

      if (!$this->db->trans_status()) {
          throw new Exception(
              'Unable to save Delivery Receipt.'
          );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => empty($deliveryReceipt->id)
            ? 'Delivery Receipt saved.'
            : 'Delivery Receipt updated.',
        'data' => [
          'delivery_receipt_id' => $deliveryReceiptId,
          'dr_no'               => $drNo
        ]
      ];

    }
    catch (Exception $ex) {

      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data' => []
      ];
    }
  }

  public function post($id)
  {
    try {

      $this->db->trans_begin();

      /*** validate document exists */
      $deliveryReceipt = $this->db
          ->where('id', $id)
          ->get('t_delivery_receipts')
          ->row();

      if (!$deliveryReceipt) {
          throw new Exception(
            'Delivery Receipt not found.'
          );
      }

      /*** validate status */
      if ($deliveryReceipt->status != 'OPEN') {
        throw new Exception(
          'Only OPEN Delivery Receipts can be posted.'
        );
      }

      /*
      * Inventory validation
      * Inventory movement
      * Status update
      */
      /*** load Delivery Receipt details */
      $details = $this->db
          ->select("
              drd.*,
              sod.qty AS qty_ordered
          ")
          ->from('t_delivery_receipt_details drd')
          ->join(
              't_sales_order_details sod',
              'sod.id = drd.sales_order_detail_id'
          )
          ->where('drd.delivery_receipt_id', $id)
          ->get()
          ->result();

      if (empty($details)) {
        throw new Exception(
          'Delivery Receipt has no detail lines.'
        );
      }

      /*** validate each detail */
      foreach ($details as $detail)
      {
        /*** validate AVAILABLE TO DELIVER */
        $availableToDeliver = $this->getAvailableToDeliver(
          $detail->sales_order_detail_id,
          $id
        );

        if ((float)$detail->qty > $availableToDeliver) {
          throw new Exception(
            'Available quantity to deliver has changed. Please recreate the Delivery Receipt.'
          );
        }
        /*** end validate */

        /*** inventory validation */
        $inventory = $this->db
            ->where('branch_id', $deliveryReceipt->branch_id)
            ->where('product_id', $detail->product_id)
            ->get('t_branch_inventory')
            ->row();

        $available = $inventory ? (float)$inventory->qty_on_hand : 0;

        /*** convert DR transaction qty to product base UOM */
        $conversionFactor = (float)$detail->conversion_factor;

        if ($conversionFactor <= 0) {
          throw new Exception(
            'Invalid UOM conversion.'
          );
        }

        $detail->base_qty = (float)$detail->qty * $conversionFactor;

        if ($detail->base_qty > $available) {
          throw new Exception('Insufficient inventory.');
        }
      }
      /*** end validation */

      /*** deduct inventory */
      foreach ($details as $detail)
      {
          $this->Branch_inventory_model->adjustBalance(
              $deliveryReceipt->branch_id,
              $detail->product_id,
              -$detail->base_qty
          );
      }
      /*** end deduct inventory */

      /*** stock ledger */
      foreach ($details as $detail)
      {
        $this->Inventory_model->writeStockLedger(
            $deliveryReceipt->branch_id,
            'DR',
            $deliveryReceipt->id,
            $deliveryReceipt->dr_no,
            [$detail],
            NULL,
            'base_qty'
        );
      }
      /*** end stock ledger */

      /*** DR status update */
      $this->db
          ->where('id', $id)
          ->update(
              't_delivery_receipts',
              [
                'status'      => 'POSTED',
                'posted_by'   => $this->session->userdata('user_id'),
                'posted_on'   => date('Y-m-d H:i:s')
              ]
          );
      /*** end DR status update */

      /*** update Sales Order status */
      $remaining = $this->db->query("SELECT COUNT(*) AS remaining_items
                                      FROM t_sales_order_details sod
                                      LEFT JOIN (
                                          SELECT
                                              drd.sales_order_detail_id,
                                              SUM(drd.qty) AS qty_delivered
                                          FROM t_delivery_receipt_details drd
                                          INNER JOIN t_delivery_receipts dr ON dr.id = drd.delivery_receipt_id
                                          WHERE dr.status = 'POSTED'
                                          GROUP BY drd.sales_order_detail_id
                                      ) dr
                                          ON dr.sales_order_detail_id = sod.id
                                      WHERE sod.sales_order_id = ?
                                      AND (
                                        sod.qty - COALESCE(dr.qty_delivered, 0)
                                      ) > 0
                                      ", [
                                        $deliveryReceipt->sales_order_id
                                      ])->row();

      if ((int)$remaining->remaining_items === 0) {
          $this->db
              ->where(
                'id',
                $deliveryReceipt->sales_order_id
              )
              ->update(
                't_sales_orders',
                [
                  'status'     => 'COMPLETED',
                  'updated_by' => $this->session->userdata('user_id'),
                  'updated_on' => date('Y-m-d H:i:s')
                ]
              );
      }
      /*** end update Sales Order status */

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to post Delivery Receipt.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Delivery Receipt posted.',
        'data' => []
      ];

    }
    catch (Exception $ex) {

      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data' => []
      ];
    }
  }

  public function cancel($ids, $cancelReason = null)
  {
    try {

      if (empty($ids)) {
        throw new Exception(
        'Please select at least one Delivery Receipt.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id)
      {
        $header = $this->get($id);

        if (!$header) {
          throw new Exception(
            'Delivery Receipt not found.'
          );
        }

        /*** allow OPEN or POSTED DRs */
        if (!in_array($header->status, ['OPEN', 'POSTED'])) {
          throw new Exception(
            "Delivery Receipt {$header->dr_no} is already {$header->status}."
          );
        }

        /*
        * Restore inventory
        * Reverse stock ledger
        * Reopen Sales Order
        */
        if ($header->status === 'POSTED') {
          $result = $this->Inventory_model->reverseTransaction('DR', $id);

          if (!$result['success']) {
            throw new Exception(
              $result['message']
            );
          }
        }
        /*** end restore */

        $this->db
            ->where('id', $id)
            ->update(
                't_delivery_receipts',
                [
                  'status'       => 'CANCELLED',
                  'cancelled_by' => $this->session->userdata('user_id'),
                  'cancelled_on' => date('Y-m-d H:i:s'),
                  'cancel_reason' => trim($cancelReason) <> '' ? strtoupper(trim($cancelReason)) : NULL,
                  'updated_by'   => $this->session->userdata('user_id'),
                  'updated_on'   => date('Y-m-d H:i:s')
                ]
            );

            /*** recalculate Sales Order status */
            if ($header->status === 'POSTED') {
              $remaining = $this->db->query("SELECT COUNT(*) AS remaining_items
                                              FROM t_sales_order_details sod
                                              LEFT JOIN (
                                                  SELECT
                                                      drd.sales_order_detail_id,
                                                      SUM(drd.qty) AS qty_delivered
                                                  FROM t_delivery_receipt_details drd
                                                  INNER JOIN t_delivery_receipts dr ON dr.id = drd.delivery_receipt_id
                                                  WHERE dr.status = 'POSTED'
                                                  GROUP BY drd.sales_order_detail_id
                                              ) dr
                                                  ON dr.sales_order_detail_id = sod.id
                                              WHERE sod.sales_order_id = ?
                                                AND (
                                                      sod.qty - COALESCE(dr.qty_delivered, 0)
                                                    ) > 0
                                          ", [
                                              $header->sales_order_id
                                          ])->row();

              $this->db
                  ->where('id', $header->sales_order_id)
                  ->update(
                      't_sales_orders',
                      [
                        'status' => (
                          (int)$remaining->remaining_items > 0
                        )
                            ? 'POSTED'
                            : 'COMPLETED',
                        'updated_by' => $this->session->userdata('user_id'),
                        'updated_on' => date('Y-m-d H:i:s')
                      ]
                  );
            }
            /*** end recalculate Sales Order status */
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to cancel Delivery Receipt.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Delivery Receipt(s) cancelled successfully.',
        'data'    => []
      ];

    } catch (Exception $ex) {

        $this->db->trans_rollback();

        return [
          'success' => FALSE,
          'message' => $ex->getMessage(),
          'data'    => []
        ];
    }
  }

  private function getAvailableToDeliver($salesOrderDetailId, $excludeDeliveryReceiptId = 0)
  {
      $row = $this->db->query("SELECT
                                  sod.qty - COALESCE(posted.qty_delivered, 0) - COALESCE(reserved.qty_reserved, 0)AS qty_available_to_deliver
                                FROM t_sales_order_details sod
                                LEFT JOIN
                                (
                                  SELECT
                                      drd.sales_order_detail_id,
                                      SUM(drd.qty) AS qty_delivered
                                  FROM t_delivery_receipt_details drd
                                  INNER JOIN t_delivery_receipts dr ON dr.id = drd.delivery_receipt_id
                                  WHERE dr.status = 'POSTED'
                                  GROUP BY drd.sales_order_detail_id
                                ) posted ON posted.sales_order_detail_id = sod.id
                                LEFT JOIN
                                (
                                  SELECT
                                      drd.sales_order_detail_id,
                                      SUM(drd.qty) AS qty_reserved
                                  FROM t_delivery_receipt_details drd
                                  INNER JOIN t_delivery_receipts dr ON dr.id = drd.delivery_receipt_id
                                  WHERE dr.status = 'OPEN'
                                    AND dr.id <> ?
                                  GROUP BY drd.sales_order_detail_id
                                ) reserved ON reserved.sales_order_detail_id = sod.id
                                WHERE sod.id = ?
                                ",
                                [
                                  (int)$excludeDeliveryReceiptId,
                                  (int)$salesOrderDetailId
                                ]
                              )->row();

    return $row ? (float)$row->qty_available_to_deliver : 0;
  }

  private function getDeliveryAvailability_deleteme($salesOrderId, $excludeDeliveryReceiptId = 0)
  {
    $branchId = (int)$this->session->userdata('branch_id');

    return $this->db->query("SELECT
                              sod.id AS sales_order_detail_id,
                              sod.product_id,
                              p.barcode,
                              p.description,
                              u.uom,
                              sod.qty AS qty_ordered,
                              COALESCE(inv.qty_on_hand,0) AS qty_available,
                              COALESCE(posted.qty_delivered,0) AS qty_delivered,
                              COALESCE(reserved.qty_reserved,0) AS qty_reserved,
                              (sod.qty - COALESCE(posted.qty_delivered, 0)) AS qty_remaining,
                              (sod.qty - COALESCE(posted.qty_delivered, 0) - COALESCE(reserved.qty_reserved, 0)) AS qty_available_to_deliver
                              FROM t_sales_order_details sod
                              INNER JOIN m_products p ON p.id = sod.product_id
                              LEFT JOIN m_uom u ON u.id = p.uom_id
                              LEFT JOIN t_branch_inventory inv ON inv.product_id = p.id AND inv.branch_id = ?
                              LEFT JOIN
                              (
                                SELECT
                                    drd.sales_order_detail_id,
                                    SUM(drd.qty) AS qty_delivered
                                FROM t_delivery_receipt_details drd
                                INNER JOIN t_delivery_receipts dr ON dr.id = drd.delivery_receipt_id
                                WHERE dr.status = 'POSTED'
                                GROUP BY drd.sales_order_detail_id
                              ) posted ON posted.sales_order_detail_id = sod.id
                              LEFT JOIN
                              (
                                SELECT
                                    drd.sales_order_detail_id,
                                    SUM(drd.qty) AS qty_reserved
                                FROM t_delivery_receipt_details drd
                                INNER JOIN t_delivery_receipts dr ON dr.id = drd.delivery_receipt_id
                                WHERE dr.status = 'OPEN' AND dr.id <> ?
                                GROUP BY drd.sales_order_detail_id
                              ) reserved ON reserved.sales_order_detail_id = sod.id
                              WHERE sod.sales_order_id = ?
                              ORDER BY sod.id
                            ",
                            [
                              $branchId,
                              $excludeDeliveryReceiptId,
                              $salesOrderId
                            ])->result();
  }

  public function getSalesOrderDetails_deleteme($salesOrderId)
  {
    $branchId = (int)$this->session->userdata('branch_id');

    return $this->db->query("SELECT
                              sod.id AS sales_order_detail_id,
                              sod.product_id,
                              p.barcode,
                              p.description,
                              u.uom,
                              sod.qty AS qty_ordered,
                              COALESCE(inv.qty_on_hand,0) qty_available,
                              COALESCE(dr.qty_delivered,0) qty_delivered,
                              (sod.qty - COALESCE(dr.qty_delivered, 0)) qty_remaining
                          FROM t_sales_order_details sod
                          INNER JOIN m_products p ON p.id=sod.product_id
                          LEFT JOIN m_uom u ON u.id=p.uom_id
                          LEFT JOIN t_branch_inventory inv ON inv.product_id=p.id AND inv.branch_id = ?
                          LEFT JOIN
                          (
                            SELECT
                                drd.sales_order_detail_id,
                                SUM(drd.qty) qty_delivered
                            FROM t_delivery_receipt_details drd
                            INNER JOIN t_delivery_receipts dr ON dr.id=drd.delivery_receipt_id
                            WHERE dr.status = 'POSTED'
                            GROUP BY drd.sales_order_detail_id
                          ) dr ON dr.sales_order_detail_id = sod.id
                          WHERE sod.sales_order_id = ?
                          ORDER BY sod.id
                        ",
                        [
                          $branchId,
                          $salesOrderId
                        ])->result();
  }
}