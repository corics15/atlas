<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_payment_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Document_number_model');
  }

  public function getAll($filters = [])
  {
    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db
          ->group_start()
          ->where("cp.payment_no ILIKE '%{$escaped}%'")
          ->or_where("cp.reference_no ILIKE '%{$escaped}%'")
          ->or_where("c.customer_name ILIKE '%{$escaped}%'")
          ->or_where("CONCAT(s.first_name, ' ', s.last_name) ILIKE '%{$escaped}%'")
          ->group_end();
    }

    if (!empty($filters['date_from'])) {
      $this->db->where(
        'cp.payment_date >=',
        $filters['date_from']
      );
    } else {
      $this->db->where(
        'cp.payment_date >=',
        date('Y-m-01')
      );
    }

    if (!empty($filters['date_to'])) {
      $this->db->where(
        'cp.payment_date <=',
        $filters['date_to']
      );
    } else {
      $this->db->where(
        'cp.payment_date <=',
        date('Y-m-d')
      );
    }

    if (!empty($filters['status'])) {
      $this->db->where(
        'cp.status',
        $filters['status']
      );
    }

    if (!empty($filters['payment_method'])) {
      $this->db->where(
        'cp.payment_method',
        $filters['payment_method']
      );
    }

    return $this->db
        ->select("
          cp.*,
          c.customer_name,
          b.branch_name,
          CONCAT(s.first_name, ' ', s.last_name) AS collector_name,
          COALESCE(a.amount_applied, 0) AS amount_applied,
          cp.amount_received - COALESCE(a.amount_applied, 0) AS amount_unapplied
        ", FALSE)
        ->from('t_customer_payments cp')
        ->join(
          'm_customers c',
          'c.id = cp.customer_id'
        )
        ->join(
          'm_branches b',
          'b.id = cp.branch_id',
          'left'
        )
        ->join(
          'm_salesmen s',
          's.id = cp.collected_by_salesman_id',
          'left'
        )
        ->join(
          "(
            SELECT
              customer_payment_id,
              SUM(amount_applied) AS amount_applied
            FROM t_customer_payment_allocations
            GROUP BY customer_payment_id
          ) a",
          'a.customer_payment_id = cp.id',
          'left',
          FALSE
        )
        ->order_by('cp.payment_date', 'DESC')
        ->order_by('cp.payment_no', 'DESC')
        ->get()
        ->result();
  }

  public function get($id)
  {
    return $this->db
        ->select("
          cp.*,
          c.customer_name,
          c.credit_limit,
          b.branch_name,
          CONCAT(s.first_name, ' ', s.last_name) AS collector_name,
          COALESCE(a.amount_applied, 0) AS amount_applied,
          cp.amount_received - COALESCE(a.amount_applied, 0) AS amount_unapplied
        ", FALSE)
        ->from('t_customer_payments cp')
        ->join(
          'm_customers c',
          'c.id = cp.customer_id'
        )
        ->join(
          'm_branches b',
          'b.id = cp.branch_id',
          'left'
        )
        ->join(
          'm_salesmen s',
          's.id = cp.collected_by_salesman_id',
          'left'
        )
        ->join(
          "(
            SELECT
              customer_payment_id,
              SUM(amount_applied) AS amount_applied
            FROM t_customer_payment_allocations
            GROUP BY customer_payment_id
          ) a",
          'a.customer_payment_id = cp.id',
          'left',
          FALSE
        )
        ->where(
          'cp.id',
          (int)$id
        )
        ->get()
        ->row();
  }

  public function getAllocations($customerPaymentId)
  {
    return $this->db
        ->select("
          cpa.*,
          si.si_no,
          si.invoice_date,
          si.total_amount
        ")
        ->from('t_customer_payment_allocations cpa')
        ->join(
          't_sales_invoices si',
          'si.id = cpa.sales_invoice_id'
        )
        ->where(
          'cpa.customer_payment_id',
          (int)$customerPaymentId
        )
        ->order_by(
          'si.invoice_date',
          'ASC'
        )
        ->order_by(
          'si.id',
          'ASC'
        )
        ->get()
        ->result();
  }

  public function getOutstandingInvoices($customerId)
  {
    $customerId = (int)$customerId;

    if ($customerId <= 0) {
      return [];
    }

    return $this->db
      ->select("
        si.id,
        si.si_no,
        si.invoice_date,
        si.total_amount,
        COALESCE(p.amount_paid, 0) AS amount_paid,
        COALESCE(cm.amount_credited, 0) AS amount_credited,
        COALESCE(cma.credit_applied, 0) AS credit_applied,
        si.total_amount
          - COALESCE(p.amount_paid, 0)
          - COALESCE(cm.amount_credited, 0)
          - COALESCE(cma.credit_applied, 0) AS balance
      ", FALSE)
      ->from('t_sales_invoices si')
      ->join(
        "(
          SELECT
            cpa.sales_invoice_id,
            SUM(cpa.amount_applied) AS amount_paid
          FROM t_customer_payment_allocations cpa
          INNER JOIN t_customer_payments cp ON cp.id = cpa.customer_payment_id
          WHERE cp.status = 'POSTED'
          GROUP BY cpa.sales_invoice_id
        ) p",
        'p.sales_invoice_id = si.id',
        'left',
        FALSE
      )
      ->join(
        "(
          SELECT
            sales_invoice_id,
            SUM(amount - available_credit) AS amount_credited
          FROM t_credit_memos
          WHERE status = 'POSTED'
          GROUP BY sales_invoice_id
        ) cm",
        'cm.sales_invoice_id = si.id',
        'left',
        FALSE
      )
      ->join(
        "(
          SELECT
            sales_invoice_id,
            SUM(amount_applied) AS credit_applied
          FROM t_credit_memo_allocations
          GROUP BY sales_invoice_id
        ) cma",
        'cma.sales_invoice_id = si.id',
        'left',
        FALSE
      )
      ->where('si.customer_id', $customerId)
      ->where('si.status', 'POSTED')
      ->where(
        'si.total_amount
          - COALESCE(p.amount_paid, 0)
          - COALESCE(cm.amount_credited, 0)
          - COALESCE(cma.credit_applied, 0) > 0',
        NULL,
        FALSE
      )
      ->order_by('si.invoice_date', 'ASC')
      ->order_by('si.id', 'ASC')
      ->get()
      ->result();
  }

  public function getCustomerLedger($customerId, $dateFrom = null, $dateTo = null)
  {
    $customerId = (int)$customerId;
    $dateFrom = trim($dateFrom ?? '');
    $dateTo = trim($dateTo ?? '');

    if ($customerId <= 0) {
      return [
        'opening_balance' => 0,
        'transactions'    => []
      ];
    }

    /*** opening balance */
    $openingBalance = 0;

    if ($dateFrom !== '') {
      $row = $this->db->query("SELECT COALESCE(SUM(x.debit - x.credit), 0) AS opening_balance
                                FROM (
                                  SELECT si.total_amount AS debit, 0::numeric AS credit
                                    FROM t_sales_invoices si
                                    WHERE si.customer_id = ?
                                    AND si.status = 'POSTED'
                                    AND si.invoice_date < ?
                                  UNION ALL
                                    SELECT 0::numeric AS debit, cp.amount_received AS credit
                                    FROM t_customer_payments cp
                                    WHERE cp.customer_id = ?
                                    AND cp.status = 'POSTED'
                                    AND cp.payment_date < ?
                                  UNION ALL
                                    SELECT r.amount AS debit, 0::numeric AS credit
                                    FROM t_customer_payment_refunds r
                                    INNER JOIN t_customer_payments cp ON cp.id = r.customer_payment_id
                                    WHERE cp.customer_id = ?
                                    AND r.status = 'POSTED'
                                    AND r.refund_date < ?
                                  UNION ALL
                                    SELECT 0::numeric AS debit, cm.amount AS credit
                                    FROM t_credit_memos cm
                                    WHERE cm.customer_id = ?
                                    AND cm.status = 'POSTED'
                                    AND cm.credit_memo_date < ?
                                ) x",
                                [
                                  $customerId, $dateFrom,
                                  $customerId, $dateFrom,
                                  $customerId, $dateFrom,
                                  $customerId, $dateFrom
                                ]
                              )->row();

      $openingBalance = round((float)$row->opening_balance, 2);
    }

    /*** transaction conditions */
    $invoiceWhere = '';
    $paymentWhere = '';
    $creditMemoWhere = '';
    $refundWhere = '';
    $params = [$openingBalance, $customerId];

    /*** sales invoices */
    if ($dateFrom !== '') {
      $invoiceWhere .= ' AND si.invoice_date >= ?';
      $params[] = $dateFrom;
    }
    if ($dateTo !== '') {
      $invoiceWhere .= ' AND si.invoice_date <= ?';
      $params[] = $dateTo;
    }

    /*** payments */
    $params[] = $customerId;
    if ($dateFrom !== '') {
      $paymentWhere .= ' AND cp.payment_date >= ?';
      $params[] = $dateFrom;
    }
    if ($dateTo !== '') {
      $paymentWhere .= ' AND cp.payment_date <= ?';
      $params[] = $dateTo;
    }

    /*** credit memos */
    $params[] = $customerId;
    if ($dateFrom !== '') {
      $creditMemoWhere .= ' AND cm.credit_memo_date >= ?';
      $params[] = $dateFrom;
    }
    if ($dateTo !== '') {
      $creditMemoWhere .= ' AND cm.credit_memo_date <= ?';
      $params[] = $dateTo;
    }

    /*** refunds */
    $params[] = $customerId;
    if ($dateFrom !== '') {
      $refundWhere .= ' AND r.refund_date >= ?';
      $params[] = $dateFrom;
    }
    if ($dateTo !== '') {
      $refundWhere .= ' AND r.refund_date <= ?';
      $params[] = $dateTo;
    }

    $transactions = $this->db->query("SELECT
                                        x.transaction_date,
                                        x.reference_no,
                                        x.transaction_type,
                                        x.debit,
                                        x.credit,
                                        ? + SUM(x.debit - x.credit) OVER (
                                          ORDER BY x.transaction_date, x.sort_order, x.transaction_id
                                          ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                                        ) AS balance,
                                        x.transaction_id
                                      FROM (
                                        SELECT
                                          si.invoice_date AS transaction_date,
                                          si.si_no AS reference_no,
                                          'SALES INVOICE' AS transaction_type,
                                          si.total_amount AS debit,
                                          0::numeric AS credit,
                                          si.id AS transaction_id,
                                          1 AS sort_order
                                        FROM t_sales_invoices si
                                        WHERE si.customer_id = ?
                                        AND si.status = 'POSTED'
                                        {$invoiceWhere}
                                        UNION ALL
                                          SELECT
                                            cp.payment_date AS transaction_date,
                                            cp.payment_no AS reference_no,
                                            'CUSTOMER PAYMENT' AS transaction_type,
                                            0::numeric AS debit,
                                            cp.amount_received AS credit,
                                            cp.id AS transaction_id,
                                            2 AS sort_order
                                          FROM t_customer_payments cp
                                          WHERE cp.customer_id = ?
                                          AND cp.status = 'POSTED'
                                          {$paymentWhere}
                                        UNION ALL
                                          SELECT
                                            cm.credit_memo_date AS transaction_date,
                                            cm.cm_no AS reference_no,
                                            'CREDIT MEMO' AS transaction_type,
                                            0::numeric AS debit,
                                            cm.amount AS credit,
                                            cm.id AS transaction_id,
                                            3 AS sort_order
                                          FROM t_credit_memos cm
                                          WHERE cm.customer_id = ?
                                          AND cm.status = 'POSTED'
                                          {$creditMemoWhere}
                                        UNION ALL
                                          SELECT
                                            r.refund_date AS transaction_date,
                                            COALESCE(NULLIF(r.reference_no, ''), cp.payment_no) AS reference_no,
                                            'CUSTOMER CREDIT REFUND' AS transaction_type,
                                            r.amount AS debit,
                                            0::numeric AS credit,
                                            cp.id AS transaction_id,
                                            4 AS sort_order
                                          FROM t_customer_payment_refunds r
                                          INNER JOIN t_customer_payments cp ON cp.id = r.customer_payment_id
                                          WHERE cp.customer_id = ?
                                          AND r.status = 'POSTED'
                                          {$refundWhere}
                                      ) x
                                      ORDER BY x.transaction_date, x.sort_order, x.transaction_id",
                                      $params
                                    )->result();

    return [
      'opening_balance' => $openingBalance,
      'transactions'    => $transactions
    ];
  }

  public function save($customerPayment)
  {
    try {
      $this->db->trans_begin();

      $customerId = (int)($customerPayment->customer_id ?? 0);
      $branchId = (int)($customerPayment->branch_id ?? 0);
      $amountReceived = round((float)($customerPayment->amount_received ?? 0), 2);

      $paymentMethod = strtoupper(trim($customerPayment->payment_method ?? ''));

      if ($customerId <= 0) {
        throw new Exception('Customer is required.');
      }

      if ($branchId <= 0) {
        throw new Exception('Branch is required.');
      }

      if ($amountReceived <= 0) {
        throw new Exception('Amount received must be greater than zero.');
      }

      if (
        !in_array(
          $paymentMethod,
          [
            'CASH',
            'CHECK',
            'BANK_TRANSFER',
            'OTHER'
          ],
          TRUE
        )
      ) {
        throw new Exception('Invalid payment method.');
      }

      /*** insert */
      if (empty($customerPayment->id)) {

        $header = [
          'payment_no' => $this->Document_number_model->generate('CP'),
          'payment_date' => $customerPayment->payment_date,
          'customer_id' => $customerId,
          'branch_id' => $branchId,
          'amount_received' => $amountReceived,
          'payment_method' => $paymentMethod,
          'reference_no' => trim($customerPayment->reference_no ?? '') !== '' ? strtoupper(trim($customerPayment->reference_no)) : NULL,
          'collected_by_salesman_id' => (int)($customerPayment->collected_by_salesman_id ?? 0) > 0 ? (int)$customerPayment->collected_by_salesman_id : NULL,
          'remarks' => trim($customerPayment->remarks ?? '') !== '' ? strtoupper(trim($customerPayment->remarks)) : NULL,
          'status' => 'OPEN',
          'entered_by' => $this->session->userdata('user_id'),
          'entered_on' => date('Y-m-d H:i:s')
        ];

        $this->db->insert(
          't_customer_payments',
          $header
        );

        $customerPaymentId = $this->db->insert_id();
        $paymentNo = $header['payment_no'];
      }

      /*** update */
      else {

        $current = $this->db
            ->where('id', (int)$customerPayment->id)
            ->get('t_customer_payments')
            ->row();

        if (!$current) {
          throw new Exception('Customer Payment not found.');
        }

        if ($current->status !== 'OPEN') {
          throw new Exception("Cannot modify a {$current->status} Customer Payment.");
        }

        $customerPaymentId = (int)$current->id;
        $paymentNo = $current->payment_no;

        $this->db
            ->where('id', $customerPaymentId)
            ->update(
              't_customer_payments',
              [
                'payment_date' => $customerPayment->payment_date,
                'customer_id' => $customerId,
                'branch_id' => $branchId,
                'amount_received' => $amountReceived,
                'payment_method' => $paymentMethod,
                'reference_no' => trim($customerPayment->reference_no ?? '') !== '' ? strtoupper(trim($customerPayment->reference_no)) : NULL,
                'collected_by_salesman_id' => (int)($customerPayment->collected_by_salesman_id ?? 0) > 0 ? (int)$customerPayment->collected_by_salesman_id : NULL,
                'remarks' => trim($customerPayment->remarks ?? '') !== '' ? strtoupper(trim($customerPayment->remarks)) : NULL,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_on' => date('Y-m-d H:i:s')
              ]
            );

        /*** replace OPEN allocations */
        $this->db->where('customer_payment_id', $customerPaymentId)->delete('t_customer_payment_allocations');
      }

      /*** validate + insert allocations */
      $totalApplied = 0;

      foreach (($customerPayment->allocations ?? []) as $allocation) {

        $salesInvoiceId = (int)($allocation->sales_invoice_id ?? 0);
        $amountApplied = round((float)($allocation->amount_applied ?? 0), 2);

        if ($amountApplied <= 0) {
          continue;
        }

        if ($salesInvoiceId <= 0) {
          throw new Exception('Invalid Sales Invoice allocation.');
        }

        /*** authoritative invoice */
        $invoice = $this->db
            ->select('id, si_no, customer_id, status, total_amount')
            ->where('id', $salesInvoiceId)
            ->get('t_sales_invoices')
            ->row();

        if (!$invoice) {
          throw new Exception('Sales Invoice not found.');
        }

        if ($invoice->status !== 'POSTED') {
          throw new Exception("Sales Invoice {$invoice->si_no} is not POSTED.");
        }

        if ((int)$invoice->customer_id !== $customerId) {
          throw new Exception("Sales Invoice {$invoice->si_no} does not belong to the selected customer.");
        }

        /*** authoritative current invoice balance */
        $previous = $this->db
            ->query(
              "SELECT
                COALESCE((
                  SELECT SUM(cpa.amount_applied)
                  FROM t_customer_payment_allocations cpa
                  INNER JOIN t_customer_payments cp ON cp.id = cpa.customer_payment_id
                  WHERE cpa.sales_invoice_id = ?
                  AND cp.status = 'POSTED'
                ), 0) AS amount_paid,
                COALESCE((
                  SELECT SUM(cm.amount - cm.available_credit)
                  FROM t_credit_memos cm
                  WHERE cm.sales_invoice_id = ?
                  AND cm.status = 'POSTED'
                ), 0) AS amount_credited",
              [
                $salesInvoiceId,
                $salesInvoiceId
              ]
            )
            ->row();

        $amountPaid = round((float)$previous->amount_paid, 2);
        $amountCredited = round((float)$previous->amount_credited, 2);
        $balance = round((float)$invoice->total_amount - $amountPaid - $amountCredited, 2);

        if ($amountApplied > $balance) {
          throw new Exception("Applied amount for {$invoice->si_no} exceeds its outstanding balance.");
        }

        $totalApplied += $amountApplied;

        if ($totalApplied > $amountReceived) {
          throw new Exception('Total applied amount cannot exceed the amount received.');
        }

        $this->db->insert(
          't_customer_payment_allocations',
          [
            'customer_payment_id' => $customerPaymentId,
            'sales_invoice_id' => $salesInvoiceId,
            'amount_applied' => $amountApplied,
            'remarks' => NULL
          ]
        );
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to save Customer Payment.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => empty($customerPayment->id) ? 'Customer Payment saved.' : 'Customer Payment updated.',
        'data' => [
          'customer_payment_id' => $customerPaymentId,
          'payment_no' => $paymentNo
        ]
      ];

    } catch (Exception $ex) {

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
        throw new Exception('Please select at least one Customer Payment.');
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
        $customerPayment = $this->db
            ->where('id', (int)$id)
            ->get('t_customer_payments')
            ->row();

        if (!$customerPayment) {
          throw new Exception('Customer Payment not found.');
        }

        if ($customerPayment->status !== 'OPEN') {
          throw new Exception("{$customerPayment->payment_no} is already {$customerPayment->status}.");
        }

        /*** get allocations */
        $allocations = $this->db
            ->where('customer_payment_id', (int)$customerPayment->id)
            ->get('t_customer_payment_allocations')
            ->result();

        $totalApplied = 0;

        foreach ($allocations as $allocation) {
          $invoice = $this->db
              ->select('id, si_no, customer_id, status, total_amount')
              ->where('id', (int)$allocation->sales_invoice_id)
              ->get('t_sales_invoices')
              ->row();

          if (!$invoice) {
            throw new Exception('Sales Invoice not found.');
          }

          if ($invoice->status !== 'POSTED') {
            throw new Exception("Sales Invoice {$invoice->si_no} is not POSTED."            );
          }

          if ((int)$invoice->customer_id !== (int)$customerPayment->customer_id) {
            throw new Exception("Sales Invoice {$invoice->si_no} does not belong to the payment customer.");
          }

          /*** authoritative current invoice balance */
          $previous = $this->db
              ->query(
                "SELECT
                  COALESCE((
                    SELECT SUM(cpa.amount_applied)
                    FROM t_customer_payment_allocations cpa
                    INNER JOIN t_customer_payments cp ON cp.id = cpa.customer_payment_id
                    WHERE cpa.sales_invoice_id = ?
                    AND cp.status = 'POSTED'
                  ), 0) AS amount_paid,
                  COALESCE((
                    SELECT SUM(cm.amount - cm.available_credit)
                    FROM t_credit_memos cm
                    WHERE cm.sales_invoice_id = ?
                    AND cm.status = 'POSTED'
                  ), 0) AS amount_credited",
                [
                  (int)$invoice->id,
                  (int)$invoice->id
                ]
              )
              ->row();

          $amountPaid = round((float)$previous->amount_paid, 2);
          $amountCredited = round((float)$previous->amount_credited, 2);
          $balance = round((float)$invoice->total_amount - $amountPaid - $amountCredited, 2);
          $amountApplied = round((float)$allocation->amount_applied, 2);

          if ($amountApplied > $balance) {
            throw new Exception("Applied amount for {$invoice->si_no} exceeds its current outstanding balance.");
          }

          $totalApplied += $amountApplied;
        }

        $totalApplied = round($totalApplied, 2);

        if ($totalApplied > round((float)$customerPayment->amount_received, 2)) {
          throw new Exception("Applied amount for {$customerPayment->payment_no} exceeds the amount received.");
        }

        $availableCredit = round((float)$customerPayment->amount_received - $totalApplied,  2);

        /*** post customer payment */
        $this->db
            ->where('id', (int)$customerPayment->id)
            ->where('status', 'OPEN')
            ->update(
              't_customer_payments',
              [
                'status' => 'POSTED',
                'available_credit' => $availableCredit,
                'posted_by' => $this->session->userdata('user_id'),
                'posted_on' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id'),
                'updated_on' => date('Y-m-d H:i:s'),
              ]
            );

        if (!$this->db->affected_rows()) {
          throw new Exception("Unable to post {$customerPayment->payment_no}.");
        }
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to post Customer Payment.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Customer Payment(s) posted successfully.',
        'data' => []
      ];

    } catch (Exception $ex) {
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
        throw new Exception('Please select at least one Customer Payment.');
      }

      $this->db->trans_begin();

      foreach ($ids as $id) {
        $customerPayment = $this->db
            ->where('id', (int)$id)
            ->get('t_customer_payments')
            ->row();

        if (!$customerPayment) {
          throw new Exception('Customer Payment not found.');
        }

        if ($customerPayment->status === 'CANCELLED') {
          throw new Exception("{$customerPayment->payment_no} is already CANCELLED.");
        }

        if ($customerPayment->status === 'POSTED') {
          $usage = $this->db->query(
            "SELECT
              EXISTS(
                SELECT 1
                FROM t_customer_payment_allocations
                WHERE customer_payment_id = ?
                AND allocation_type = 'CREDIT'
              ) AS has_credit_allocations,
              EXISTS(
                SELECT 1
                FROM t_customer_payment_refunds
                WHERE customer_payment_id = ?
                AND status = 'POSTED'
              ) AS has_refunds",
            [
              $customerPayment->id,
              $customerPayment->id
            ]
          )->row();

          if ($usage->has_credit_allocations === 't' || $usage->has_refunds === 't') {
            throw new Exception(
              "{$customerPayment->payment_no} cannot be cancelled because its unapplied credit has already been used or refunded."
            );
          }
        }

        $this->db
            ->where('id', (int)$customerPayment->id)
            ->where_in(
              'status',
              [
                'OPEN',
                'POSTED'
              ]
            )
            ->update(
              't_customer_payments',
              [
                'status' => 'CANCELLED',
                'cancel_reason' => trim($cancelReason ?? '') !== '' ? strtoupper(trim($cancelReason)) : NULL,
                'cancelled_by' => $this->session->userdata('user_id'),
                'cancelled_on' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id'),
                'updated_on' => date('Y-m-d H:i:s')
              ]
            );

        if (!$this->db->affected_rows()) {
          throw new Exception("Unable to cancel {$customerPayment->payment_no}.");
        }
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to cancel Customer Payment.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Customer Payment(s) cancelled successfully.',
        'data' => []
      ];

    } catch (Exception $ex) {
      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data' => []
      ];
    }
  }

  public function getArAging($asOfDate, $customerId = NULL)
  {
    $sql = "SELECT
      si.customer_id,
      c.customer_name,
      SUM(CASE WHEN aging.days_past_due <= 0 THEN aging.balance ELSE 0 END) AS current_amount,
      SUM(CASE WHEN aging.days_past_due BETWEEN 1 AND 30 THEN aging.balance ELSE 0 END) AS days_1_30,
      SUM(CASE WHEN aging.days_past_due BETWEEN 31 AND 60 THEN aging.balance ELSE 0 END) AS days_31_60,
      SUM(CASE WHEN aging.days_past_due BETWEEN 61 AND 90 THEN aging.balance ELSE 0 END) AS days_61_90,
      SUM(CASE WHEN aging.days_past_due > 90 THEN aging.balance ELSE 0 END) AS over_90,
      SUM(aging.balance) AS total_balance
    FROM t_sales_invoices si
    INNER JOIN m_customers c ON c.id = si.customer_id
    LEFT JOIN m_terms t ON t.id = si.terms_id
    CROSS JOIN LATERAL (
      SELECT
        si.invoice_date + COALESCE(t.days_due, 0) AS due_date,
        ?::date - (si.invoice_date + COALESCE(t.days_due, 0)) AS days_past_due,
        si.total_amount
          - COALESCE((
            SELECT SUM(a.amount_applied)
            FROM t_customer_payment_allocations a
            INNER JOIN t_customer_payments cp ON cp.id = a.customer_payment_id
            WHERE a.sales_invoice_id = si.id
            AND cp.status = 'POSTED'
            AND (
              (a.allocation_type = 'PAYMENT' AND cp.payment_date <= ?::date)
              OR
              (a.allocation_type = 'CREDIT' AND a.applied_on::date <= ?::date)
            )
          ), 0)
          - COALESCE((
            SELECT SUM(cm.amount - cm.available_credit)
            FROM t_credit_memos cm
            WHERE cm.sales_invoice_id = si.id
            AND cm.status = 'POSTED'
            AND cm.credit_memo_date <= ?::date
          ), 0)
          - COALESCE((
            SELECT SUM(cma.amount_applied)
            FROM t_credit_memo_allocations cma
            WHERE cma.sales_invoice_id = si.id
            AND cma.applied_on::date <= ?::date
          ), 0) AS balance
    ) aging
    WHERE si.status = 'POSTED'
    AND si.invoice_date <= ?::date ";

    $params = [
      $asOfDate,
      $asOfDate,
      $asOfDate,
      $asOfDate,
      $asOfDate,
      $asOfDate
    ];

    if (!empty($customerId)) {
      $sql .= "AND si.customer_id = ? ";
      $params[] = (int)$customerId;
    }

    $sql .= "GROUP BY si.customer_id, c.customer_name
      HAVING SUM(aging.balance) > 0
      ORDER BY c.customer_name";

    return $this->db->query($sql, $params)->result();
  }

  public function getArAgingDetails($asOfDate, $customerId)
  {
    return $this->db->query(
      "SELECT
        si.id,
        si.si_no,
        si.invoice_date,
        si.total_amount,
        si.total_amount
          - COALESCE((
            SELECT SUM(a.amount_applied)
            FROM t_customer_payment_allocations a
            INNER JOIN t_customer_payments cp ON cp.id = a.customer_payment_id
            WHERE a.sales_invoice_id = si.id
            AND cp.status = 'POSTED'
            AND (
              (a.allocation_type = 'PAYMENT' AND cp.payment_date <= ?::date)
              OR
              (a.allocation_type = 'CREDIT' AND a.applied_on::date <= ?::date)
            )
          ), 0)
          - COALESCE((
            SELECT SUM(cm.amount - cm.available_credit)
            FROM t_credit_memos cm
            WHERE cm.sales_invoice_id = si.id
            AND cm.status = 'POSTED'
            AND cm.credit_memo_date <= ?::date
          ), 0)
          - COALESCE((
            SELECT SUM(cma.amount_applied)
            FROM t_credit_memo_allocations cma
            WHERE cma.sales_invoice_id = si.id
            AND cma.applied_on::date <= ?::date
          ), 0) AS balance,
        COALESCE((
          SELECT SUM(a.amount_applied)
          FROM t_customer_payment_allocations a
          INNER JOIN t_customer_payments cp ON cp.id = a.customer_payment_id
          WHERE a.sales_invoice_id = si.id
          AND cp.status = 'POSTED'
          AND (
            (a.allocation_type = 'PAYMENT' AND cp.payment_date <= ?::date)
            OR
            (a.allocation_type = 'CREDIT' AND a.applied_on::date <= ?::date)
          )
        ), 0) AS amount_paid,
        COALESCE((
          SELECT SUM(cm.amount - cm.available_credit)
          FROM t_credit_memos cm
          WHERE cm.sales_invoice_id = si.id
          AND cm.status = 'POSTED'
          AND cm.credit_memo_date <= ?::date
        ), 0)
        +
        COALESCE((
          SELECT SUM(cma.amount_applied)
          FROM t_credit_memo_allocations cma
          WHERE cma.sales_invoice_id = si.id
          AND cma.applied_on::date <= ?::date
        ), 0) AS credit_memo
      FROM t_sales_invoices si
      WHERE si.customer_id = ?
      AND si.status = 'POSTED'
      AND si.invoice_date <= ?::date
      ORDER BY si.invoice_date, si.id",
      [
        $asOfDate,
        $asOfDate,
        $asOfDate,
        $asOfDate,
        $asOfDate,
        $asOfDate,
        $asOfDate,
        $asOfDate,
        (int)$customerId,
        $asOfDate
      ]
    )->result();
  }

  public function getAvailableCustomerCredits($customerId)
  {
    $customerId = (int)$customerId;

    if ($customerId <= 0) {
      return [];
    }

    return $this->db
        ->select("
          cm.id,
          cm.cm_no,
          cm.credit_memo_date,
          cm.sales_invoice_id,
          cm.sales_return_id,
          cm.amount,
          COALESCE(a.amount_applied, 0) AS amount_applied,
          cm.amount - COALESCE(a.amount_applied, 0) AS available_credit
        ", FALSE)
        ->from('t_credit_memos cm')
        ->join(
          "(
            SELECT credit_memo_id, SUM(amount_applied) AS amount_applied
            FROM t_credit_memo_allocations
            GROUP BY credit_memo_id
          ) a",
          'a.credit_memo_id = cm.id',
          'left',
          FALSE
        )
        ->where('cm.customer_id', $customerId)
        ->where('cm.status', 'POSTED')
        ->where('cm.amount - COALESCE(a.amount_applied, 0) > 0', NULL, FALSE)
        ->order_by('cm.credit_memo_date', 'ASC')
        ->order_by('cm.id', 'ASC')
        ->get()
        ->result();
  }

  public function getAvailableCreditMemos($customerId)
  {
    $customerId = (int)$customerId;

    if ($customerId <= 0) {
      return [];
    }

    return $this->db->query(
      "SELECT
        cm.id,
        cm.cm_no,
        cm.credit_memo_date,
        cm.sales_invoice_id,
        si.si_no,
        cm.amount,
        cm.available_credit,
        COALESCE(SUM(a.amount_applied), 0) AS amount_applied,
        cm.available_credit - COALESCE(SUM(a.amount_applied), 0) AS balance
      FROM t_credit_memos cm
      INNER JOIN t_sales_invoices si ON si.id = cm.sales_invoice_id
      LEFT JOIN t_credit_memo_allocations a ON a.credit_memo_id = cm.id
      WHERE cm.customer_id = ?
      AND cm.status = 'POSTED'
      AND cm.available_credit > 0
      GROUP BY
        cm.id,
        cm.cm_no,
        cm.credit_memo_date,
        cm.sales_invoice_id,
        si.si_no,
        cm.amount,
        cm.available_credit
      HAVING cm.available_credit - COALESCE(SUM(a.amount_applied), 0) > 0
      ORDER BY cm.credit_memo_date, cm.id",
      [$customerId]
    )->result();
  }

  public function applyCreditMemo($creditMemoId, $salesInvoiceId, $amount, $remarks = null)
  {
    try {
      $creditMemoId = (int)$creditMemoId;
      $salesInvoiceId = (int)$salesInvoiceId;
      $amount = round((float)$amount, 2);

      if ($creditMemoId <= 0 || $salesInvoiceId <= 0 || $amount <= 0) {
        throw new Exception('Invalid Credit Memo allocation.');
      }

      $this->db->trans_begin();

      /*** validate Credit Memo */
      $creditMemo = $this->db
        ->select('id, cm_no, customer_id, sales_invoice_id, available_credit, status')
        ->where('id', $creditMemoId)
        ->get('t_credit_memos')
        ->row();

      if (!$creditMemo || $creditMemo->status !== 'POSTED') {
        throw new Exception('Invalid or non-posted Credit Memo.');
      }

      /*** validate target Sales Invoice */
      $salesInvoice = $this->db
        ->select('id, si_no, customer_id, total_amount, status')
        ->where('id', $salesInvoiceId)
        ->get('t_sales_invoices')
        ->row();

      if (!$salesInvoice || $salesInvoice->status !== 'POSTED') {
        throw new Exception('Invalid or non-posted Sales Invoice.');
      }

      if ((int)$creditMemo->customer_id !== (int)$salesInvoice->customer_id) {
        throw new Exception('Credit Memo and Sales Invoice must belong to the same Customer.');
      }

      if ((int)$creditMemo->sales_invoice_id === $salesInvoiceId) {
        throw new Exception('Reusable Credit Memo cannot be allocated to its source Sales Invoice.');
      }

      /*** remaining reusable Credit Memo balance */
      $row = $this->db->query(
        "SELECT COALESCE(SUM(amount_applied), 0) AS amount_applied
        FROM t_credit_memo_allocations
        WHERE credit_memo_id = ?",
        [$creditMemoId]
      )->row();

      $creditUsed = round((float)$row->amount_applied, 2);
      $creditBalance = max(0, round((float)$creditMemo->available_credit - $creditUsed, 2));

      if ($amount > $creditBalance) {
        throw new Exception("Amount exceeds available Credit Memo balance of " . number_format($creditBalance, 2) . ".");
      }

      /*** current target SI balance */
      $paymentRow = $this->db->query(
        "SELECT COALESCE(SUM(a.amount_applied), 0) AS amount_paid
        FROM t_customer_payment_allocations a
        INNER JOIN t_customer_payments cp ON cp.id = a.customer_payment_id
        WHERE a.sales_invoice_id = ?
        AND cp.status = 'POSTED'",
        [$salesInvoiceId]
      )->row();

      $creditRow = $this->db->query(
        "SELECT COALESCE(SUM(cm.amount - cm.available_credit), 0) AS amount_credited
        FROM t_credit_memos cm
        WHERE cm.sales_invoice_id = ?
        AND cm.status = 'POSTED'",
        [$salesInvoiceId]
      )->row();

      $allocationRow = $this->db->query(
        "SELECT COALESCE(SUM(amount_applied), 0) AS credit_applied
        FROM t_credit_memo_allocations
        WHERE sales_invoice_id = ?",
        [$salesInvoiceId]
      )->row();

      $invoiceBalance = max(
        0,
        round(
          (float)$salesInvoice->total_amount
          - (float)$paymentRow->amount_paid
          - (float)$creditRow->amount_credited
          - (float)$allocationRow->credit_applied,
          2
        )
      );

      if ($invoiceBalance <= 0) {
        throw new Exception("Sales Invoice {$salesInvoice->si_no} has no outstanding balance.");
      }

      if ($amount > $invoiceBalance) {
        throw new Exception("Amount exceeds Sales Invoice balance of " . number_format($invoiceBalance, 2) . ".");
      }

      /*** save allocation */
      $this->db->insert('t_credit_memo_allocations', [
        'credit_memo_id'  => $creditMemoId,
        'sales_invoice_id' => $salesInvoiceId,
        'amount_applied'   => $amount,
        'applied_by'       => $this->session->userdata('user_id'),
        'applied_on'       => date('Y-m-d H:i:s'),
        'remarks'          => $remarks
      ]);

      if (!$this->db->affected_rows()) {
        throw new Exception('Unable to apply Credit Memo.');
      }

      if (!$this->db->trans_status()) {
        throw new Exception('Unable to apply Credit Memo.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Credit Memo applied successfully.',
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

  public function applyPaymentCredit($customerPaymentId, $salesInvoiceId, $amount, $remarks = null)
  {
    try {
      $customerPaymentId = (int)$customerPaymentId;
      $salesInvoiceId = (int)$salesInvoiceId;
      $amount = round((float)$amount, 2);

      if ($customerPaymentId <= 0 || $salesInvoiceId <= 0 || $amount <= 0) {
        throw new Exception('Invalid Customer Payment credit allocation.');
      }

      $this->db->trans_begin();

      $payment = $this->db
        ->select('id, payment_no, customer_id, amount_received, available_credit, status')
        ->where('id', $customerPaymentId)
        ->get('t_customer_payments')
        ->row();

      $invoice = $this->db
        ->select('id, si_no, customer_id, total_amount, status')
        ->where('id', $salesInvoiceId)
        ->get('t_sales_invoices')
        ->row();

      if (!$payment || $payment->status !== 'POSTED') {
        throw new Exception('Invalid or non-posted Customer Payment.');
      }

      if (!$invoice || $invoice->status !== 'POSTED') {
        throw new Exception('Invalid or non-posted Sales Invoice.');
      }

      if ((int)$payment->customer_id !== (int)$invoice->customer_id) {
        throw new Exception('Customer Payment and Sales Invoice must belong to the same Customer.');
      }

      $row = $this->db->query(
        "SELECT
          ? - COALESCE((
            SELECT SUM(amount_applied)
            FROM t_customer_payment_allocations
            WHERE customer_payment_id = ?
            AND allocation_type = 'CREDIT'
          ), 0)
          - COALESCE((
            SELECT SUM(amount)
            FROM t_customer_payment_refunds
            WHERE customer_payment_id = ?
            AND status = 'POSTED'
          ), 0) AS payment_balance,

          ? - COALESCE((
            SELECT SUM(a.amount_applied)
            FROM t_customer_payment_allocations a
            INNER JOIN t_customer_payments cp
              ON cp.id = a.customer_payment_id
            WHERE a.sales_invoice_id = ?
            AND cp.status = 'POSTED'
          ), 0)
          - COALESCE((
            SELECT SUM(amount - available_credit)
            FROM t_credit_memos
            WHERE sales_invoice_id = ?
            AND status = 'POSTED'
          ), 0)
          - COALESCE((
            SELECT SUM(amount_applied)
            FROM t_credit_memo_allocations
            WHERE sales_invoice_id = ?
          ), 0) AS invoice_balance",
        [
          $payment->available_credit,
          $customerPaymentId,
          $customerPaymentId,
          $invoice->total_amount,
          $salesInvoiceId,
          $salesInvoiceId,
          $salesInvoiceId
        ]
      )->row();

      $paymentBalance = max(0, round((float)$row->payment_balance, 2));
      $invoiceBalance = max(0, round((float)$row->invoice_balance, 2));

      if ($amount > $paymentBalance) {
        throw new Exception('Amount exceeds available Customer Payment credit of ' . number_format($paymentBalance, 2) . '.');
      }

      if ($invoiceBalance <= 0) {
        throw new Exception("Sales Invoice {$invoice->si_no} has no outstanding balance.");
      }

      if ($amount > $invoiceBalance) {
        throw new Exception('Amount exceeds Sales Invoice balance of ' . number_format($invoiceBalance, 2) . '.');
      }

      $this->db->insert('t_customer_payment_allocations', [
        'customer_payment_id' => $customerPaymentId,
        'sales_invoice_id' => $salesInvoiceId,
        'amount_applied' => $amount,
        'allocation_type' => 'CREDIT',
        'applied_by' => $this->session->userdata('user_id'),
        'applied_on' => date('Y-m-d H:i:s'),
        'remarks' => $remarks
      ]);

      if (!$this->db->affected_rows()) {
        throw new Exception('Unable to apply Customer Payment credit.');
      }

      if (!$this->db->trans_status()) {
        throw new Exception('Unable to apply Customer Payment credit.');
      }

      $this->db->trans_commit();

      return [
        'success' => TRUE,
        'message' => 'Customer Payment credit applied successfully.',
        'data' => []
      ];

    } catch (Exception $ex) {
      $this->db->trans_rollback();

      return [
        'success' => FALSE,
        'message' => $ex->getMessage(),
        'data' => []
      ];
    }
  }

  public function getAvailablePaymentCredits($customerId)
  {
    $customerId = (int)$customerId;

    if ($customerId <= 0) {
      return [];
    }

    return $this->db->query(
      "SELECT
        cp.id,
        cp.payment_no,
        cp.payment_date,
        cp.amount_received,
        cp.available_credit,
        COALESCE(a.amount_applied, 0) AS amount_applied,
        COALESCE(r.amount_refunded, 0) AS amount_refunded,
        cp.available_credit
          - COALESCE(a.amount_applied, 0)
          - COALESCE(r.amount_refunded, 0) AS balance
      FROM t_customer_payments cp
      LEFT JOIN (
        SELECT
          customer_payment_id,
          SUM(amount_applied) AS amount_applied
        FROM t_customer_payment_allocations
        WHERE allocation_type = 'CREDIT'
        GROUP BY customer_payment_id
      ) a ON a.customer_payment_id = cp.id
      LEFT JOIN (
        SELECT
          customer_payment_id,
          SUM(amount) AS amount_refunded
        FROM t_customer_payment_refunds
        WHERE status = 'POSTED'
        GROUP BY customer_payment_id
      ) r ON r.customer_payment_id = cp.id
      WHERE cp.customer_id = ?
      AND cp.status = 'POSTED'
      AND cp.available_credit > 0
      AND cp.available_credit
        - COALESCE(a.amount_applied, 0)
        - COALESCE(r.amount_refunded, 0) > 0
      ORDER BY cp.payment_date, cp.id",
      [$customerId]
    )->result();
  }

  public function getPaymentCreditRefunds($customerId)
  {
    $customerId = (int)$customerId;

    if ($customerId <= 0) {
      return [];
    }

    return $this->db->query(
      "SELECT
        r.id,
        r.refund_date,
        r.amount,
        r.reference_no,
        r.remarks,
        r.status,
        r.cancel_reason,
        r.cancelled_on,
        cp.id AS customer_payment_id,
        cp.payment_no AS source_no
      FROM t_customer_payment_refunds r
      INNER JOIN t_customer_payments cp
        ON cp.id = r.customer_payment_id
      WHERE cp.customer_id = ?
      ORDER BY r.refund_date DESC, r.id DESC",
      [$customerId]
    )->result();
  }

  public function refundPaymentCredit( $customerPaymentId, $refundDate, $amount, $referenceNo = NULL, $remarks = NULL)
  {
    $customerPaymentId = (int)$customerPaymentId;
    $amount = round((float)$amount, 2);
    $userId = (int)$this->session->userdata('user_id');

    if ($customerPaymentId <= 0 || $amount <= 0) {
      throw new Exception('Invalid refund request.');
    }

    $this->db->trans_begin();

    try {
      $payment = $this->db->query(
        "SELECT
          cp.id,
          cp.status,
          cp.available_credit,
          cp.available_credit
            - COALESCE((
              SELECT SUM(a.amount_applied)
              FROM t_customer_payment_allocations a
              WHERE a.customer_payment_id = cp.id
              AND a.allocation_type = 'CREDIT'
            ), 0)
            - COALESCE((
              SELECT SUM(r.amount)
              FROM t_customer_payment_refunds r
              WHERE r.customer_payment_id = cp.id
              AND r.status = 'POSTED'
            ), 0) AS balance
        FROM t_customer_payments cp
        WHERE cp.id = ?
        FOR UPDATE",
        [$customerPaymentId]
      )->row();

      if (!$payment) {
        throw new Exception('Customer Payment not found.');
      }

      if ($payment->status !== 'POSTED') {
        throw new Exception('Only POSTED Customer Payments can be refunded.');
      }

      $balance = round((float)$payment->balance, 2);

      if ($amount > $balance) {
        throw new Exception(
          'Refund amount cannot exceed the available customer credit.'
        );
      }

      $this->db->insert('t_customer_payment_refunds', [
        'customer_payment_id' => $customerPaymentId,
        // 'refund_date' => $refundDate,
        'refund_date' => date('Y-m-d'),
        'amount' => $amount,
        'reference_no' => $referenceNo ?: NULL,
        'remarks' => $remarks ?: NULL,
        'entered_by' => $userId ?: NULL
      ]);

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to save Customer Credit Refund.');
      }

      $this->db->trans_commit();
      return TRUE;

    } catch (Exception $e) {
      $this->db->trans_rollback();
      throw $e;
    }
  }

  public function cancelPaymentCreditRefund($refundId, $cancelReason = NULL)
  {
    $refundId = (int)$refundId;
    $userId = (int)$this->session->userdata('user_id');

    if ($refundId <= 0) {
      throw new Exception('Invalid Customer Credit Refund.');
    }

    $this->db->trans_begin();

    try {
      $refund = $this->db->query(
        "SELECT
          id,
          customer_payment_id,
          amount,
          status
        FROM t_customer_payment_refunds
        WHERE id = ?
        FOR UPDATE",
        [$refundId]
      )->row();

      if (!$refund) {
        throw new Exception('Customer Credit Refund not found.');
      }

      if ($refund->status === 'CANCELLED') {
        throw new Exception('Customer Credit Refund is already CANCELLED.');
      }

      if ($refund->status !== 'POSTED') {
        throw new Exception('Only POSTED Customer Credit Refunds can be cancelled.');
      }

      $this->db
        ->where('id', $refundId)
        ->where('status', 'POSTED')
        ->update('t_customer_payment_refunds', [
          'status' => 'CANCELLED',
          'cancel_reason' => trim($cancelReason ?? '') !== '' ? strtoupper(trim($cancelReason)) : NULL,
          'cancelled_by' => $userId ?: NULL,
          'cancelled_on' => date('Y-m-d H:i:s')
        ]);

      if (!$this->db->affected_rows()) {
        throw new Exception('Unable to cancel Customer Credit Refund.');
      }

      if ($this->db->trans_status() === FALSE) {
        throw new Exception('Unable to cancel Customer Credit Refund.');
      }

      $this->db->trans_commit();
      return TRUE;

    } catch (Exception $e) {
      $this->db->trans_rollback();
      throw $e;
    }
  }

}