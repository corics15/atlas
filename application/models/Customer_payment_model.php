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
        ->order_by(
          'cp.payment_date',
          'DESC'
        )
        ->order_by(
          'cp.id',
          'DESC'
        )
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
          si.total_amount - COALESCE(p.amount_paid, 0) AS balance
        ", FALSE)
        ->from('t_sales_invoices si')
        ->join(
          "(
            SELECT
              cpa.sales_invoice_id,
              SUM(cpa.amount_applied) AS amount_paid
            FROM t_customer_payment_allocations cpa
            INNER JOIN t_customer_payments cp
              ON cp.id = cpa.customer_payment_id
            WHERE cp.status = 'POSTED'
            GROUP BY cpa.sales_invoice_id
          ) p",
          'p.sales_invoice_id = si.id',
          'left',
          FALSE
        )
        ->where('si.customer_id', $customerId)
        ->where('si.status', 'POSTED')
        ->where(
          'si.total_amount - COALESCE(p.amount_paid, 0) > 0',
          NULL,
          FALSE
        )
        ->order_by('si.invoice_date', 'ASC')
        ->order_by('si.id', 'ASC')
        ->get()
        ->result();
  }

  public function getCustomerLedger( $customerId, $dateFrom = null, $dateTo = null)
  {
    $customerId = (int)$customerId;
    $dateFrom = trim($dateFrom ?? '');
    $dateTo = trim($dateTo ?? '');

    if ($customerId <= 0) {
      return [
        'opening_balance' => 0,
        'transactions' => []
      ];
    }

    /*** opening balance */
    $openingBalance = 0;

    if ($dateFrom !== '') {
      $row = $this->db
          ->query(
            "SELECT COALESCE(SUM(x.debit - x.credit), 0) AS opening_balance
            FROM (
              SELECT si.total_amount AS debit, 0::numeric AS credit
              FROM t_sales_invoices si
              WHERE si.customer_id = ?
              AND si.status = 'POSTED'
              AND si.invoice_date < ?
              UNION ALL
              SELECT 0::numeric AS debit, COALESCE(a.amount_applied, 0) AS credit
              FROM t_customer_payments cp
              LEFT JOIN (
                SELECT customer_payment_id, SUM(amount_applied) AS amount_applied
                FROM t_customer_payment_allocations
                GROUP BY customer_payment_id
              ) a ON a.customer_payment_id = cp.id
              WHERE cp.customer_id = ?
              AND cp.status = 'POSTED'
              AND cp.payment_date < ?
            ) x",
            [
              $customerId,
              $dateFrom,
              $customerId,
              $dateFrom
            ]
          )
          ->row();

      $openingBalance = round((float)$row->opening_balance, 2);
    }

    /*** transaction date conditions */
    $invoiceWhere = '';
    $paymentWhere = '';
    $params = [
      $openingBalance,
      $customerId
    ];

    if ($dateFrom !== '') {
      $invoiceWhere .= ' AND si.invoice_date >= ?';
      $params[] = $dateFrom;
    }

    if ($dateTo !== '') {
      $invoiceWhere .= ' AND si.invoice_date <= ?';
      $params[] = $dateTo;
    }

    $params[] = $customerId;

    if ($dateFrom !== '') {
      $paymentWhere .= ' AND cp.payment_date >= ?';
      $params[] = $dateFrom;
    }

    if ($dateTo !== '') {
      $paymentWhere .= ' AND cp.payment_date <= ?';
      $params[] = $dateTo;
    }

    $transactions = $this->db
        ->query(
          "SELECT
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
              COALESCE(a.amount_applied, 0) AS credit,
              cp.id AS transaction_id,
              2 AS sort_order
            FROM t_customer_payments cp
            LEFT JOIN (
              SELECT customer_payment_id, SUM(amount_applied) AS amount_applied
              FROM t_customer_payment_allocations
              GROUP BY customer_payment_id
            ) a ON a.customer_payment_id = cp.id
            WHERE cp.customer_id = ?
            AND cp.status = 'POSTED'
            {$paymentWhere}
          ) x
          ORDER BY x.transaction_date, x.sort_order, x.transaction_id",
          $params
        )
        ->result();

    return [
      'opening_balance' => $openingBalance,
      'transactions' => $transactions
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
        throw new Exception(
          'Amount received must be greater than zero.'
        );
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
            ->where(
              'id',
              (int)$customerPayment->id
            )
            ->get('t_customer_payments')
            ->row();

        if (!$current) {
          throw new Exception(
            'Customer Payment not found.'
          );
        }

        if ($current->status !== 'OPEN') {
          throw new Exception(
            "Cannot modify a {$current->status} Customer Payment."
          );
        }

        $customerPaymentId = (int)$current->id;
        $paymentNo = $current->payment_no;

        $this->db
            ->where(
              'id',
              $customerPaymentId
            )
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
        $this->db
            ->where(
              'customer_payment_id',
              $customerPaymentId
            )
            ->delete(
              't_customer_payment_allocations'
            );
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
          throw new Exception(
            'Invalid Sales Invoice allocation.'
          );
        }

        /*** authoritative invoice */
        $invoice = $this->db
            ->select(
              'id, si_no, customer_id, status, total_amount'
            )
            ->where(
              'id',
              $salesInvoiceId
            )
            ->get(
              't_sales_invoices'
            )
            ->row();

        if (!$invoice) {
          throw new Exception(
            'Sales Invoice not found.'
          );
        }

        if ($invoice->status !== 'POSTED') {
          throw new Exception(
            "Sales Invoice {$invoice->si_no} is not POSTED."
          );
        }

        if (
          (int)$invoice->customer_id !==
          $customerId
        ) {
          throw new Exception(
            "Sales Invoice {$invoice->si_no} does not belong to the selected customer."
          );
        }

        /*** previously POSTED payments only */
        $previous = $this->db
            ->select(
              'COALESCE(SUM(cpa.amount_applied), 0) AS amount_paid',
              FALSE
            )
            ->from(
              't_customer_payment_allocations cpa'
            )
            ->join(
              't_customer_payments cp',
              'cp.id = cpa.customer_payment_id'
            )
            ->where(
              'cpa.sales_invoice_id',
              $salesInvoiceId
            )
            ->where(
              'cp.status',
              'POSTED'
            )
            ->get()
            ->row();

        $amountPaid = round((float)$previous->amount_paid, 2);
        $balance = round((float)$invoice->total_amount - $amountPaid, 2);

        if ($amountApplied > $balance) {
          throw new Exception(
            "Applied amount for {$invoice->si_no} exceeds its outstanding balance."
          );
        }

        $totalApplied += $amountApplied;

        if ($totalApplied > $amountReceived) {
          throw new Exception(
            'Total applied amount cannot exceed the amount received.'
          );
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
        throw new Exception(
          'Unable to save Customer Payment.'
        );
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
            ->where(
              'customer_payment_id',
              (int)$customerPayment->id
            )
            ->get(
              't_customer_payment_allocations'
            )
            ->result();

        $totalApplied = 0;

        foreach ($allocations as $allocation) {
          $invoice = $this->db
              ->select(
                'id, si_no, customer_id, status, total_amount'
              )
              ->where(
                'id',
                (int)$allocation->sales_invoice_id
              )
              ->get(
                't_sales_invoices'
              )
              ->row();

          if (!$invoice) {
            throw new Exception('Sales Invoice not found.');
          }

          if ($invoice->status !== 'POSTED') {
            throw new Exception(
              "Sales Invoice {$invoice->si_no} is not POSTED."
            );
          }

          if (
            (int)$invoice->customer_id !==
            (int)$customerPayment->customer_id
          ) {
            throw new Exception("Sales Invoice {$invoice->si_no} does not belong to the payment customer.");
          }

          /*** authoritative previously posted amount */
          $previous = $this->db
              ->select(
                'COALESCE(SUM(cpa.amount_applied), 0) AS amount_paid',
                FALSE
              )
              ->from(
                't_customer_payment_allocations cpa'
              )
              ->join(
                't_customer_payments cp',
                'cp.id = cpa.customer_payment_id'
              )
              ->where(
                'cpa.sales_invoice_id',
                (int)$invoice->id
              )
              ->where(
                'cp.status',
                'POSTED'
              )
              ->get()
              ->row();

          $amountPaid = round((float)$previous->amount_paid, 2);
          $balance = round((float)$invoice->total_amount - $amountPaid, 2);
          $amountApplied = round((float)$allocation->amount_applied, 2);

          if ($amountApplied > $balance) {
            throw new Exception("Applied amount for {$invoice->si_no} exceeds its current outstanding balance.");
          }

          $totalApplied += $amountApplied;
        }

        $totalApplied = round($totalApplied, 2);

        if (
          $totalApplied >
          round((float)$customerPayment->amount_received, 2)
        ) {
          throw new Exception("Applied amount for {$customerPayment->payment_no} exceeds the amount received.");
        }

        /*** post customer payment */
        $this->db
            ->where(
              'id',
              (int)$customerPayment->id
            )
            ->where(
              'status',
              'OPEN'
            )
            ->update(
              't_customer_payments',
              [
                'status' => 'POSTED',
                'posted_by' => $this->session->userdata('user_id'),
                'posted_on' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id'),
                'updated_on' => date('Y-m-d H:i:s')
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
              SUM(
                CASE
                  WHEN aging.days_past_due <= 0
                  THEN aging.balance
                  ELSE 0
                END
              ) AS current_amount,
              SUM(
                CASE
                  WHEN aging.days_past_due BETWEEN 1 AND 30
                  THEN aging.balance
                  ELSE 0
                END
              ) AS days_1_30,
              SUM(
                CASE
                  WHEN aging.days_past_due BETWEEN 31 AND 60
                  THEN aging.balance
                  ELSE 0
                END
              ) AS days_31_60,
              SUM(
                CASE
                  WHEN aging.days_past_due BETWEEN 61 AND 90
                  THEN aging.balance
                  ELSE 0
                END
              ) AS days_61_90,
              SUM(
                CASE
                  WHEN aging.days_past_due > 90
                  THEN aging.balance
                  ELSE 0
                END
              ) AS over_90,
              SUM(aging.balance) AS total_balance
            FROM t_sales_invoices si
            INNER JOIN m_customers c ON c.id = si.customer_id
            LEFT JOIN m_terms t ON t.id = si.terms_id
            CROSS JOIN LATERAL (
              SELECT
                (
                  si.invoice_date +
                  COALESCE(t.days_due, 0)
                ) AS due_date,
                (
                  ?::date -
                  (
                    si.invoice_date +
                    COALESCE(t.days_due, 0)
                  )
                ) AS days_past_due,
                (
                  si.total_amount -
                  COALESCE(
                    (
                      SELECT SUM(a.amount_applied)
                      FROM t_customer_payment_allocations a
                      INNER JOIN t_customer_payments cp ON cp.id = a.customer_payment_id
                      WHERE a.sales_invoice_id = si.id
                        AND cp.status = 'POSTED'
                        AND cp.payment_date <= ?::date
                    ),
                    0
                  )
                ) AS balance
            ) aging
            WHERE si.status = 'POSTED' AND si.invoice_date <= ?::date
          ";

    $params = [
      $asOfDate,
      $asOfDate,
      $asOfDate
    ];

    if (!empty($customerId)) {
      $sql .= "AND si.customer_id = ? ";
      $params[] = (int)$customerId;
    }

    $sql .= "GROUP BY
              si.customer_id,
              c.customer_name
            HAVING SUM(aging.balance) > 0
            ORDER BY c.customer_name";

    return $this->db
        ->query($sql, $params)
        ->result();
  }

}