<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_invoice_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Inventory_model');
    $this->load->model('Document_number_model');
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
          ->where("si.si_no ILIKE '%{$escaped}%'")
          ->or_where("so.so_no ILIKE '%{$escaped}%'")
          ->or_where("c.customer_name ILIKE '%{$escaped}%'")
          ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'si.invoice_date >=',
        $filters['date_from']
      );
    } else {
      $this->db->where(
        'si.invoice_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'si.invoice_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'si.invoice_date <=',
        date('Y-m-d')
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'si.status',
        $filters['status']
      );
    }

    return $this->db
        ->select("
            si.*,
            so.so_no,
            so.id AS so_id,
            c.customer_name,
            concat(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name
        ")
        ->from('t_sales_invoices si')
        ->join(
            't_sales_orders so',
            'so.id = si.sales_order_id',
            'left'
        )
        ->join(
            'm_customers c',
            'c.id = si.customer_id',
            'left'
        )
        ->join(
            'm_salesmen s',
            's.id = si.salesman_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = si.terms_id',
            'left'
        )
        ->order_by(
            'si.invoice_date',
            'DESC'
        )
        ->order_by(
            'si.id',
            'DESC'
        )
        ->get()
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->select("
            si.*,
            so.so_no,
            c.customer_name,
            concat(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name,
            dr.dr_no,
            dr.delivery_date,
            dr.status AS dr_status,
            dr.remarks AS dr_remarks
        ")
        ->from('t_sales_invoices si')
        ->join(
            't_sales_orders so',
            'so.id = si.sales_order_id'
        )
        ->join(
            't_delivery_receipts dr',
            'dr.id = si.delivery_receipt_id'
        )
        ->join(
            'm_customers c',
            'c.id = si.customer_id',
            'left'
        )
        ->join(
            'm_salesmen s',
            's.id = si.salesman_id',
            'left'
        )
        ->join(
            'm_terms t',
            't.id = si.terms_id',
            'left'
        )
        ->where('si.id', $id)
        ->get()
        ->row();
  }

  public function getDetails($id)
  {
    $branchId = (int) $this->session->userdata('branch_id');

    return $this->db
        ->select("
            sid.*,
            p.barcode,
            p.description,
            COALESCE(bi.qty_on_hand, 0) AS qty_available,
            u.uom
        ")
        ->from('t_sales_invoice_details sid')
        ->join(
            'm_products p',
            'p.id = sid.product_id',
            'left'
        )
        ->join(
            't_branch_inventory bi',
            "bi.product_id = sid.product_id AND bi.branch_id = {$branchId}",
            'left'
        )
        ->join(
            'm_uom u',
            'u.id = p.uom_id',
            'left'
        )
        ->where(
            'sid.sales_invoice_id',
            $id
        )
        ->order_by(
            'sid.id',
            'ASC'
        )
        ->get()
        ->result();
  }

  public function getSalesOrder($salesOrderId)
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
        ->where(
            'so.id',
            $salesOrderId
        )
        ->get()
        ->row();
  }

  public function getSalesOrderDetails($salesOrderId)
  {
    $branchId = (int) $this->session->userdata('branch_id');

    return $this->db
      ->query("SELECT
                sod.id AS sales_order_detail_id,
                sod.product_id,
                sod.qty - COALESCE(inv.qty_invoiced, 0) AS qty,
                p.barcode,
                p.description,
                COALESCE(bi.qty_on_hand,0) AS qty_available,
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
              AND (sod.qty - COALESCE(inv.qty_invoiced,0)) > 0
              ORDER BY sod.id
              ",
              [
                $branchId,
                $salesOrderId
              ]
            )
      ->result();
  }

  public function save($salesInvoice)
  {
    try {
      $this->db->trans_begin();

      if (empty($salesInvoice->id)) {
        /*** insert header */
        $header = [
          'si_no'          => $this->Document_number_model->generate('SI'),
          'invoice_date'   => $salesInvoice->invoice_date,
          'sales_order_id' => $salesInvoice->sales_order_id,
          'delivery_receipt_id' => $salesInvoice->delivery_receipt_id,
          'customer_id'    => $salesInvoice->customer_id,
          'salesman_id'    => $salesInvoice->salesman_id,
          'terms_id'       => $salesInvoice->terms_id,
          'credit_limit'   => $salesInvoice->credit_limit,
          'remarks'        => trim($salesInvoice->remarks) <> '' ? strtoupper(trim($salesInvoice->remarks)) : NULL,
          'status'         => 'OPEN',
          'entered_by'     => $this->session->userdata('user_id'),
          'entered_on'     => date('Y-m-d H:i:s')
        ];

        $this->db->insert('t_sales_invoices', $header);

        $salesInvoiceId = $this->db->insert_id();
        $invoiceNo = $header['si_no'];

        /*** insert details */
        foreach ($salesInvoice->details as $detail)
        {
          if ($detail->qty <= 0) {
            continue;
          }

          $this->db->insert(
            't_sales_invoice_details',
            [
              'sales_invoice_id'      => $salesInvoiceId,
              'sales_order_detail_id' => $detail->sales_order_detail_id,
              'product_id'            => $detail->product_id,
              'qty'                   => $detail->qty,
              'unit_price'            => 0,
              'discount_percent'      => 0,
              'discount_amount'       => 0,
              'remarks'               => NULL
            ]
          );
        }
      }

      else {

        $invoice = $this->db
            ->where('id', $salesInvoice->id)
            ->get('t_sales_invoices')
            ->row();

        if (!$invoice) {
          throw new Exception(
            'Sales Invoice not found.'
          );
        }

        if ($invoice->status != 'OPEN') {
          throw new Exception(
            "Cannot modify a {$invoice->status} Sales Invoice."
          );
        }

        /*** update header only */
        $this->db
            ->where('id', $salesInvoice->id)
            ->update(
                't_sales_invoices',
                [
                  'invoice_date' => $salesInvoice->invoice_date,
                  'remarks' => trim($salesInvoice->remarks) <> '' ? strtoupper(trim($salesInvoice->remarks)) : NULL,
                  'updated_by' => $this->session->userdata('user_id'),
                  'updated_on' => date('Y-m-d H:i:s')
                ]
            );

        $exists = $this->db
            ->where('id', $salesInvoice->id)
            ->where('status', 'OPEN')
            ->count_all_results('t_sales_invoices');

        if ($exists == 0) {
          throw new Exception(
            'Sales Invoice can no longer be updated.'
          );
        }

        $salesInvoiceId = $salesInvoice->id;
        $invoiceNo = $invoice->si_no;
        /** end header update */
      }

      if ($this->db->trans_status() === FALSE)
      {
        throw new Exception('Unable to save Sales Invoice.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Invoice saved.',
        'data' => [
          'sales_invoice_id' => $salesInvoiceId,
          'si_no' => $invoiceNo
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

  public function post($ids)
  {
    try {
      if (empty($ids)) {
        throw new Exception(
          'Please select at least one Sales Invoice.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
        $salesInvoice = $this->db
            ->where('id', $id)
            ->get('t_sales_invoices')
            ->row();

        if (!$salesInvoice) {
          throw new Exception(
            'Sales Invoice not found.'
          );
        }

        if ($salesInvoice->status != 'OPEN') {
          throw new Exception(
            "Sales Invoice {$salesInvoice->si_no} is already {$salesInvoice->status}."
          );
        }

        /*** post sales invoice */
        $this->db
            ->where('id', $id)
            ->update(
                't_sales_invoices',
                [
                  'status'     => 'POSTED',
                  'posted_by'  => $this->session->userdata('user_id'),
                  'posted_on'  => date('Y-m-d H:i:s'),
                  'updated_by' => $this->session->userdata('user_id'),
                  'updated_on' => date('Y-m-d H:i:s')
                ]
            );
        /*** end post sales invoice */
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to post Sales Invoice.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Invoice(s) posted successfully.',
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

  public function cancel($ids, $cancelReason)
  {
    try {
      if (empty($ids)) {
        throw new Exception(
          'Please select at least one Sales Invoice.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
          $invoice = $this->db
              ->where('id', $id)
              ->get('t_sales_invoices')
              ->row();

          if (!$invoice) {
            throw new Exception(
              'Sales Invoice not found.'
            );
          }

          if ($invoice->status != 'OPEN') {
            throw new Exception(
              "Only OPEN Sales Invoices can be cancelled."
            );
          }

          $this->db
              ->where('id', $id)
              ->update(
                  't_sales_invoices',
                  [
                      'status'          => 'CANCELLED',
                      'cancel_reason'   => trim($cancelReason),
                      'cancelled_by'    => $this->session->userdata('user_id'),
                      'cancelled_on'    => date('Y-m-d H:i:s'),
                      'updated_by'      => $this->session->userdata('user_id'),
                      'updated_on'      => date('Y-m-d H:i:s')
                  ]
              );
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to cancel Sales Invoice.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Invoice(s) cancelled successfully.',
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

  public function hasRemainingItems($salesOrderId)
  {
    $row = $this->db
        ->query("SELECT COUNT(*) remaining_count
                  FROM t_sales_order_details sod
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
                  AND (sod.qty - COALESCE(inv.qty_invoiced, 0)) > 0",
                  [
                    $salesOrderId
                  ]
              )
              ->row();

    return $row->remaining_count > 0;
  }

  public function getDeliveryReceipt($deliveryReceiptId)
  {
    return $this->db
        ->select("
            dr.*,
            dr.dr_no,
            so.so_no,
            c.customer_name,
            CONCAT(s.first_name,' ',s.last_name) AS salesman_name,
            t.terms_name,
            c.credit_limit,
            so.salesman_id,
            t.id AS terms_id
        ")
        ->from('t_delivery_receipts dr')
        ->join(
            't_sales_orders so',
            'so.id = dr.sales_order_id'
        )
        ->join(
            'm_customers c',
            'c.id = dr.customer_id',
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
        ->where('dr.id', $deliveryReceiptId)
        ->where('dr.status', 'POSTED')
        ->get()
        ->row();
  }

  public function getDeliveryReceiptDetails($deliveryReceiptId)
  {
    $branchId = (int)$this->session->userdata('branch_id');

    return $this->db->query("SELECT
                                drd.id AS delivery_receipt_detail_id,
                                drd.sales_order_detail_id,
                                drd.product_id,
                                drd.qty - COALESCE(inv.qty_invoiced, 0) AS qty,
                                p.barcode,
                                p.description,
                                COALESCE(
                                    bi.qty_on_hand,
                                    0
                                ) AS qty_available,
                                u.uom
                              FROM t_delivery_receipt_details drd
                              INNER JOIN m_products p ON p.id = drd.product_id
                              LEFT JOIN t_branch_inventory bi ON bi.product_id = drd.product_id AND bi.branch_id = ?
                              LEFT JOIN m_uom u ON u.id = p.uom_id
                              LEFT JOIN (
                                  SELECT
                                      sid.sales_order_detail_id,
                                      SUM(sid.qty) AS qty_invoiced
                                  FROM t_sales_invoice_details sid
                                  INNER JOIN t_sales_invoices si ON si.id = sid.sales_invoice_id
                                  WHERE si.status <> 'CANCELLED'
                                    AND si.delivery_receipt_id = ?
                                  GROUP BY sid.sales_order_detail_id
                              ) inv
                                  ON inv.sales_order_detail_id = drd.sales_order_detail_id
                              WHERE drd.delivery_receipt_id = ?
                              AND (
                                drd.qty - COALESCE(inv.qty_invoiced, 0)
                              ) > 0
                              ORDER BY drd.id
                            ",
                            [
                              $branchId,
                              $deliveryReceiptId,
                              $deliveryReceiptId
                            ])->result();
  }

}