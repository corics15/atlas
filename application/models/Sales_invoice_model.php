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
          ->or_where("dr.dr_no ILIKE '%{$escaped}%'")
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
            dr.dr_no,
            dr.id AS dr_id,
            so.so_no,
            so.id AS so_id,
            c.customer_name,
            CONCAT(s.first_name, ' ', s.last_name) AS salesman_name,
            t.terms_name,
            COALESCE((
              SELECT SUM(sid.qty)
              FROM t_sales_invoice_details sid
              WHERE sid.sales_invoice_id = si.id
            ), 0) AS item_count
        ")
        ->from('t_sales_invoices si')
        ->join(
            't_delivery_receipts dr',
            'dr.id = si.delivery_receipt_id',
            'left'
        )
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
        ->order_by('si.invoice_date', 'DESC')
        ->order_by('si.si_no', 'DESC')
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
            p.uom_id AS base_uom_id,
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
            'u.id = sid.uom_id',
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
                WHERE si.status IN ('OPEN', 'POSTED')
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

      /*** resolve source Sales Order */
      if (empty($salesInvoice->id)) {

        /*** new SI: source SO comes from request */
        $salesOrderId = (int)($salesInvoice->sales_order_id ?? 0);

      } else {

        /*** existing SI: source SO comes from saved SI */
        $existingInvoice = $this->db
            ->select('sales_order_id')
            ->where('id', $salesInvoice->id)
            ->get('t_sales_invoices')
            ->row();

        if (!$existingInvoice) {
          throw new Exception(
            'Sales Invoice not found.'
          );
        }

        $salesOrderId = (int)$existingInvoice->sales_order_id;
      }
      /*** end resolve source Sales Order */

      /*** get authoritative SO VAT snapshot */
        $salesOrder = $this->db
            ->select('id, terms_id, vat_mode, vat_rate')
            ->where('id', $salesOrderId)
            ->get('t_sales_orders')
            ->row();

        if (!$salesOrder) {
          throw new Exception(
            'Source Sales Order not found.'
          );
        }

        $vatMode = strtoupper(trim($salesOrder->vat_mode ?? ''));
        $vatRate = (float)($salesOrder->vat_rate ?? 0);
        $termsId = (int)($salesOrder->terms_id ?? 0);

        if (
          !in_array(
            $vatMode,
            ['INCLUSIVE', 'EXCLUSIVE'],
            TRUE
          )
        ) {
          throw new Exception(
            'Invalid Sales Order VAT pricing mode.'
          );
        }

        if ($vatRate < 0 || $vatRate > 100) {
          throw new Exception(
            'Invalid Sales Order VAT rate.'
          );
        }
      /*** end SO VAT snapshot */

      if (empty($salesInvoice->id)) {
        /*** insert header */
        $header = [
          'si_no'          => $this->Document_number_model->generate('SI'),
          'invoice_date'   => $salesInvoice->invoice_date,
          'sales_order_id' => $salesOrderId,
          'delivery_receipt_id' => $salesInvoice->delivery_receipt_id,
          'customer_id'    => $salesInvoice->customer_id,
          'salesman_id'    => $salesInvoice->salesman_id,
          'terms_id'       => $termsId > 0 ? $termsId : NULL,
          'credit_limit'   => $salesInvoice->credit_limit,
          'remarks'        => trim($salesInvoice->remarks) <> '' ? strtoupper(trim($salesInvoice->remarks)) : NULL,
          'vat_mode'       => $vatMode,
          'vat_rate'       => $vatRate,
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

          /*** validate source DR snapshot */
            $deliveryReceiptDetail = $this->db
                ->select('
                  product_id,
                  uom_id,
                  conversion_factor
                ')
                ->where('sales_order_detail_id', $detail->sales_order_detail_id)
                ->where('delivery_receipt_id', $salesInvoice->delivery_receipt_id)
                ->get('t_delivery_receipt_details')
                ->row();

            if (!$deliveryReceiptDetail) {
              throw new Exception(
                'Delivery Receipt detail not found.'
              );
            }

            if (
              (int)$detail->product_id !==
              (int)$deliveryReceiptDetail->product_id
            ) {
              throw new Exception(
                'Sales Invoice product does not match the Delivery Receipt.'
              );
            }

            if (
              (int)$detail->uom_id !==
              (int)$deliveryReceiptDetail->uom_id
            ) {
              throw new Exception(
                'Sales Invoice UOM does not match the Delivery Receipt.'
              );
            }

            if (
              (float)$detail->conversion_factor !==
              (float)$deliveryReceiptDetail->conversion_factor
            ) {
              throw new Exception(
                'Sales Invoice conversion does not match the Delivery Receipt.'
              );
            }
          /*** end validate */

          /*** get authoritative SO commercial snapshot */
            $salesOrderDetail = $this->db
                ->select('
                  qty,
                  unit_price,
                  discount_type,
                  discount_percent,
                  discount_amount
                ')
                ->where('id', $detail->sales_order_detail_id)
                ->get('t_sales_order_details')
                ->row();

            if (!$salesOrderDetail) {
              throw new Exception(
                'Sales Order detail not found.'
              );
            }

            $invoiceQty = (float)$detail->qty;
            $soQty = (float)$salesOrderDetail->qty;
            $unitPrice = (float)$salesOrderDetail->unit_price;
            $discountType = strtoupper(trim($salesOrderDetail->discount_type ?? ''));
            $discountPercent = (float)$salesOrderDetail->discount_percent;
            $soDiscountAmount = (float)$salesOrderDetail->discount_amount;
            $discountAmount = 0;

            /*** percentage discount */
            if ($discountType === 'PERCENT') {
              $grossAmount = $invoiceQty * $unitPrice;
              $discountAmount = round($grossAmount * ($discountPercent / 100), 2);
            }

            /*** fixed row discount */
            elseif ($discountType === 'AMOUNT') {

              if ($soQty <= 0) {
                throw new Exception(
                  'Invalid Sales Order quantity for discount allocation.'
                );
              }

              /*** get previously invoiced qty + discount */
              $previous = $this->db
                            ->select('
                              COALESCE(SUM(sid.qty), 0) AS qty_invoiced,
                              COALESCE(SUM(sid.discount_amount), 0) AS discount_invoiced
                            ', FALSE)
                            ->from('t_sales_invoice_details sid')
                            ->join(
                              't_sales_invoices si',
                              'si.id = sid.sales_invoice_id'
                            )
                            ->where(
                              'sid.sales_order_detail_id',
                              $detail->sales_order_detail_id
                            )
                            ->where_in('si.status', ['OPEN', 'POSTED'])
                            ->get()
                            ->row();

              $previousQty = (float)$previous->qty_invoiced;
              $previousDiscount = (float)$previous->discount_invoiced;
              $remainingDiscount = max(0, $soDiscountAmount - $previousDiscount);

              /*** final quantity receives exact remaining discount */
              if (($previousQty + $invoiceQty) >= $soQty) {
                $discountAmount = round($remainingDiscount, 2);
              } else {

                /*** proportional allocation */
                $discountAmount = round($soDiscountAmount * ($invoiceQty / $soQty), 2);

                /*** never exceed remaining discount */
                $discountAmount = min($discountAmount, $remainingDiscount);
              }
            }
          /*** end authoritative SO commercial snapshot */

          $this->db->insert(
            't_sales_invoice_details',
            [
              'sales_invoice_id'      => $salesInvoiceId,
              'sales_order_detail_id' => $detail->sales_order_detail_id,
              'product_id'            => $detail->product_id,
              'uom_id'                => $detail->uom_id,
              'conversion_factor'     => $detail->conversion_factor,
              'qty'                   => $detail->qty,
              'unit_price'            => $unitPrice,
              'discount_type'         => $discountType !== '' ? $discountType : NULL,
              'discount_percent'      => $discountType === 'PERCENT' ? $discountPercent : 0,
              'discount_amount'       => $discountAmount,
              'remarks' => NULL,
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

      /*** calculate authoritative Sales Invoice totals */
        $totals = $this->db
            ->select("
              COALESCE(
                SUM((qty * unit_price) - discount_amount),
                0
              ) AS discounted_amount
            ", FALSE)
            ->where(
              'sales_invoice_id',
              $salesInvoiceId
            )
            ->get('t_sales_invoice_details')
            ->row();

        $discountedAmount = round((float)$totals->discounted_amount, 2);

        $subtotal = 0;
        $vatAmount = 0;
        $totalAmount = 0;
        $vatDecimal = $vatRate / 100;

        /*** VAT inclusive */
        if ($vatMode === 'INCLUSIVE') {
          $totalAmount = $discountedAmount;

          if ($vatDecimal > 0) {
            $subtotal = round($totalAmount / (1 + $vatDecimal), 2);
            $vatAmount = round($totalAmount - $subtotal, 2);

          } else {
            $subtotal = $totalAmount;
            $vatAmount = 0;
          }
        }

        /*** VAT exclusive */
        elseif ($vatMode === 'EXCLUSIVE') {
          $subtotal = $discountedAmount;
          $vatAmount = round($subtotal * $vatDecimal, 2);
          $totalAmount = round($subtotal + $vatAmount, 2);
        }

        $this->db
            ->where('id', $salesInvoiceId)
            ->update(
              't_sales_invoices',
              [
                'subtotal'     => $subtotal,
                'vat_amount'   => $vatAmount,
                'total_amount' => $totalAmount
              ]
            );
      /*** end Sales Invoice totals */

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

  /*** validate if POSTED Sales Invoice can be reversed */
  public function canReverse($id)
  {
    try {
      $salesInvoice = $this->db
          ->where('id', (int)$id)
          ->get('t_sales_invoices')
          ->row();

      if (!$salesInvoice) {
        throw new Exception(
          'Sales Invoice not found.'
        );
      }

      if ($salesInvoice->status !== 'POSTED') {
        throw new Exception(
          "Only POSTED Sales Invoices can be reversed."
        );
      }

      /*** posted customer payment allocation */
      $payment = $this->db
          ->select('cp.payment_no')
          ->from('t_customer_payment_allocations cpa')
          ->join(
            't_customer_payments cp',
            'cp.id = cpa.customer_payment_id'
          )
          ->where('cpa.sales_invoice_id', (int)$id)
          ->where('cp.status', 'POSTED')
          ->limit(1)
          ->get()
          ->row();

      if ($payment) {
        throw new Exception(
          "Sales Invoice {$salesInvoice->si_no} cannot be reversed because "
          . "Customer Payment {$payment->payment_no} has already been applied to it. "
          . "Resolve the Customer Payment first."
        );
      }

      /*** posted other deduction */
      $deduction = $this->db
          ->select('cp.payment_no')
          ->from('t_customer_payment_deductions cpd')
          ->join(
            't_customer_payments cp',
            'cp.id = cpd.customer_payment_id'
          )
          ->where('cpd.sales_invoice_id', (int)$id)
          ->where('cp.status', 'POSTED')
          ->limit(1)
          ->get()
          ->row();

      if ($deduction) {
        throw new Exception(
          "Sales Invoice {$salesInvoice->si_no} cannot be reversed because "
          . "Customer Payment {$deduction->payment_no} contains an Other Deduction "
          . "applied to it. Resolve the Customer Payment first."
        );
      }

      /*** active Sales Return */
      $salesReturn = $this->db
          ->select('sr_no, status')
          ->where('sales_invoice_id', (int)$id)
          ->where_in('status', ['OPEN', 'POSTED'])
          ->limit(1)
          ->get('t_sales_returns')
          ->row();

      if ($salesReturn) {
        throw new Exception(
          "Sales Invoice {$salesInvoice->si_no} cannot be reversed because "
          . "Sales Return {$salesReturn->sr_no} already exists for it. "
          . "Resolve the Sales Return first."
        );
      }

      /*** posted Credit Memo */
      $creditMemo = $this->db
          ->select('cm_no')
          ->where('sales_invoice_id', (int)$id)
          ->where('status', 'POSTED')
          ->limit(1)
          ->get('t_credit_memos')
          ->row();

      if ($creditMemo) {
        throw new Exception(
          "Sales Invoice {$salesInvoice->si_no} cannot be reversed because "
          . "Credit Memo {$creditMemo->cm_no} already exists for it. "
          . "Resolve the Credit Memo first."
        );
      }

      return [
        'success' => TRUE,
        'message' => "Sales Invoice {$salesInvoice->si_no} can be reversed.",
        'data'    => []
      ];

    } catch (Exception $ex) {
      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data'    => []
      ];
    }
  }

  /*** reverse POSTED Sales Invoice */
  public function reverse($ids, $reverseReason)
  {
    try {
      if (empty($ids)) {
        throw new Exception(
          'Please select at least one Sales Invoice.'
        );
      }

      if (trim($reverseReason) === '') {
        throw new Exception(
          'Reverse reason is required.'
        );
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
        /*** validate downstream dependencies */
        $validation = $this->canReverse($id);

        if (!$validation['success']) {
          throw new Exception(
            $validation['message']
          );
        }

        /*** authoritative Sales Invoice */
        $salesInvoice = $this->db
            ->where('id', (int)$id)
            ->get('t_sales_invoices')
            ->row();

        if (!$salesInvoice) {
          throw new Exception(
            'Sales Invoice not found.'
          );
        }

        /***
         * A reversed SI remains in history.
         * Never delete or rewrite the original posted document.
         */
        $this->db
            ->where('id', (int)$id)
            ->update(
              't_sales_invoices',
              [
                'status'         => 'REVERSED',
                'reverse_reason' => trim(strtoupper($reverseReason)),
                'reversed_by'    => $this->session->userdata('user_id'),
                'reversed_on'    => date('Y-m-d H:i:s'),
                'updated_by'     => $this->session->userdata('user_id'),
                'updated_on'     => date('Y-m-d H:i:s')
              ]
            );
      }

      if (!$this->db->trans_status()) {
        throw new Exception(
          'Unable to reverse Sales Invoice.'
        );
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Sales Invoice(s) reversed successfully.',
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
                    WHERE si.status IN ('OPEN', 'POSTED')
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
            so.vat_mode,
            so.vat_rate,
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
                                drd.uom_id,
                                drd.conversion_factor,
                                p.uom_id AS base_uom_id,
                                sod.qty AS so_qty,
                                sod.unit_price,
                                sod.discount_type,
                                sod.discount_percent,
                                sod.discount_amount AS so_discount_amount,
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
                              INNER JOIN t_sales_order_details sod ON sod.id = drd.sales_order_detail_id
                              LEFT JOIN t_branch_inventory bi ON bi.product_id = drd.product_id AND bi.branch_id = ?
                              LEFT JOIN m_uom u ON u.id = drd.uom_id
                              LEFT JOIN (
                                  SELECT
                                      sid.sales_order_detail_id,
                                      SUM(sid.qty) AS qty_invoiced
                                  FROM t_sales_invoice_details sid
                                  INNER JOIN t_sales_invoices si ON si.id = sid.sales_invoice_id
                                  WHERE si.status IN ('OPEN', 'POSTED')
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