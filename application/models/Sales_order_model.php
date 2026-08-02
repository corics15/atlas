<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_order_model extends CI_Model
{

  public function get($id)
  {
    return $this->db
        ->select("
            so.*,
            c.customer_name,
            concat(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name
        ")
        ->from('t_sales_orders so')
        ->join(
            'm_customers c',
            'c.id = so.customer_id',
            'left'
        )
        ->join(
            'm_salesmen s',
            's.id = so.salesman_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = so.terms_id',
            'left'
        )
        ->where('so.id', $id)
        ->get()
        ->row();
  }

  public function getDetails($salesOrderId)
  {
    $branchId = (int) $this->session->userdata('branch_id');

    return $this->db->query("SELECT
                              sod.id,
                              sod.product_id,
                              p.barcode,
                              p.description,
                              COALESCE(bi.qty_on_hand,0) AS qty_available,
                              sod.qty,
                              COALESCE(inv.qty_invoiced, 0) AS qty_fulfilled,
                              sod.qty - COALESCE(inv.qty_invoiced, 0) AS qty_remaining,
                              u.uom
                            FROM t_sales_order_details sod
                            INNER JOIN m_products p ON p.id = sod.product_id
                            LEFT JOIN t_branch_inventory bi ON bi.product_id = sod.product_id AND bi.branch_id = ?
                            LEFT JOIN m_uom u ON u.id = p.uom_id
                            LEFT JOIN (
                                SELECT
                                    sid.sales_order_detail_id,
                                    SUM(sid.qty) qty_invoiced
                                FROM t_sales_invoice_details sid
                                INNER JOIN t_sales_invoices si ON si.id = sid.sales_invoice_id
                                WHERE si.status <> 'CANCELLED'
                                GROUP BY sid.sales_order_detail_id
                            ) inv
                            ON inv.sales_order_detail_id = sod.id
                            WHERE sod.sales_order_id = ?
                            ORDER BY sod.id",
                            [
                              $branchId,
                              $salesOrderId
                            ]
                          )->result();
  }

  public function getAll($keyword = '')
  {
    if (!empty($keyword)) {
      $escaped = $this->db->escape_like_str($keyword);

      $this->db->group_start()
          ->where("so.so_no ILIKE '%{$escaped}%'")
          ->or_where("c.customer_name ILIKE '%{$escaped}%'")
          ->group_end();
    }

    return $this->db
      ->select("
          so.*,
          c.customer_name,
          concat(s.first_name, ' ', s.last_name) AS salesman_name,
          t.terms_name
      ")
      ->from('t_sales_orders so')
      ->join(
          'm_customers c',
          'c.id = so.customer_id',
          'left'
      )
      ->join(
          'm_salesmen s',
          's.id = so.salesman_id',
          'left'
      )
      ->join(
          'm_terms t',
          't.id = so.terms_id',
          'left'
      )
      ->order_by(
          'so.id',
          'DESC'
      )
      ->get()
      ->result();
  }

  public function save($postData)
  {
    try {
      $this->db->trans_begin();

      if (empty($postData->id)) {

        /*** insert header */
        $header = [
          'so_no' => $this->generateSoNo(),
          'order_date' => $postData->order_date,
          'customer_id' => (int) $postData->customer_id,
          'salesman_id' => (int) $postData->salesman_id,
          'terms_id' => $postData->terms_id <> '' ? (int) $postData->terms_id : NULL,
          'credit_limit' => $postData->credit_limit,
          'remarks' => trim($postData->remarks) <> '' ? strtoupper(trim($postData->remarks)) : NULL,
          'status' => 'OPEN',
          'entered_by' => $this->session->userdata('user_id'),
          'entered_on' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('t_sales_orders', $header);

        $salesOrderId = $this->db->insert_id();
        $soNo = $header['so_no'];

      } else {

        /*** don't allow modification of POSTED/CANCELLED docs */
        $current = $this->db
            ->select('status')
            ->where('id', $postData->id)
            ->get('t_sales_orders')
            ->row();

        if (!$current) {
          throw new Exception('Sales Order not found.');
        }

        if ($current->status !== 'OPEN') {
          throw new Exception(
            "Cannot modify a {$current->status} Sales Order."
          );
        }

        /*** update header */
        $this->db
            ->where('id', $postData->id)
            ->update(
              't_sales_orders',
              [
                'order_date' => $postData->order_date,
                'customer_id' => (int) $postData->customer_id,
                'salesman_id' => (int) $postData->salesman_id,
                'terms_id' => (int) $postData->terms_id,
                'credit_limit' => $postData->credit_limit,
                'remarks' => trim($postData->remarks) <> '' ? strtoupper(trim($postData->remarks)) : NULL,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_on' => date('Y-m-d H:i:s')
              ]
            );

        $salesOrderId = $postData->id;

        $soNo = $this->db
            ->select('so_no')
            ->where('id', $salesOrderId)
            ->get('t_sales_orders')
            ->row()
            ->so_no;

        /*** remove old details */
        $this->db->where('sales_order_id', $salesOrderId)->delete('t_sales_order_details');
      }

      /*** INSERT DETAILS */
      foreach ($postData->details as $detail) {
        $this->db->insert(
          't_sales_order_details',
          [
            'sales_order_id' => $salesOrderId,
            'product_id' => $detail->product_id,
            'qty' => $detail->qty,
            'unit_price' => 0,
            'remarks' => NULL
          ]
        );
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to save Sales Order.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => empty($postData->id)
          ? 'Sales Order saved.'
          : 'Sales Order updated.',
        'data' => [
          'sales_order_id' => $salesOrderId,
          'so_no' => $soNo
        ]
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

  public function post($ids)
  {
    try {
      if (empty($ids)) {
        throw new Exception(
          'Please select at least one Sales Order.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {

        $salesOrder = $this->db
            ->where('id', $id)
            ->get('t_sales_orders')
            ->row();

        if (!$salesOrder) {
          throw new Exception(
            'Sales Order not found.'
          );
        }

        if ($salesOrder->status !== 'OPEN') {
          throw new Exception(
            "Sales Order {$salesOrder->so_no} is already {$salesOrder->status}."
          );
        }

        $this->db
            ->where('id', $id)
            ->update(
                't_sales_orders',
                [
                  'status'     => 'POSTED',
                  'posted_by'  => $this->session->userdata('user_id'),
                  'posted_on'  => date('Y-m-d H:i:s'),
                  'updated_by' => $this->session->userdata('user_id'),
                  'updated_on' => date('Y-m-d H:i:s')
                ]
            );
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to post Sales Order.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Order(s) posted successfully.',
        'data'    => []
      ];
    }
    catch (Exception $ex) {
      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data'    => []
      ];
    }
  }

  public function cancel(array $ids, $cancelReason = null)
  {
    try {

      if (empty($ids)) {
        throw new Exception('Please select at least one Sales Order.');
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
          $row = $this->db
              ->where('id', $id)
              ->get('t_sales_orders')
              ->row();

          if (!$row) {
            throw new Exception("Sales Order #{$id} not found.");
          }

          if ($row->status !== 'OPEN') {
            throw new Exception(
              "Sales Order {$row->so_no} is already {$row->status}."
            );
          }

          $this->db
              ->where('id', $id)
              ->update(
                  't_sales_orders',
                  [
                    'status'         => 'CANCELLED',
                    'cancelled_by'   => $this->session->userdata('user_id'),
                    'cancelled_on'   => date('Y-m-d H:i:s'),
                    'cancel_reason'  => $cancelReason,
                    'updated_by'     => $this->session->userdata('user_id'),
                    'updated_on'     => date('Y-m-d H:i:s')
                  ]
              );

          if (!$this->db->affected_rows()) {
            throw new Exception(
              "Unable to cancel {$row->so_no}."
            );
          }
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => count($ids) . ' Sales Order(s) cancelled successfully.',
        'data'    => $ids
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

  public function generateSoNo()
  {
    // Temporary implementation, temporary generate
    return 'SO-' . date('YmdHis');
  }
}