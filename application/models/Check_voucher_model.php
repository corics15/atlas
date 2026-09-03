<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Check_voucher_model extends CI_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Document_number_model');
  }

  public function get($id)
  {
    return $this->db
        ->select("
          cv.*,
          b.branch_code,
          b.branch_name,
          s.supplier_name,
          ba.bank_name,
          ba.account_name AS bank_account_name,
          ba.account_no
        ")
        ->from('t_check_vouchers cv')
        ->join('m_branches b', 'b.id = cv.branch_id', 'left')
        ->join('m_suppliers s', 's.id = cv.supplier_id', 'left')
        ->join('m_bank_accounts ba', 'ba.id = cv.bank_account_id', 'left')
        ->where('cv.id', $id)
        ->get()
        ->row();
  }

  public function getDetails($checkVoucherId)
  {
    return $this->db
        ->select("
          cvd.id,
          cvd.account_id,
          coa.account_code,
          coa.account_name,
          cvd.debit,
          cvd.credit,
          cvd.remarks,
          cvd.line_no
        ")
        ->from('t_check_voucher_details cvd')
        ->join('m_chart_of_accounts coa', 'coa.id = cvd.account_id')
        ->where(
          'cvd.check_voucher_id',
          $checkVoucherId
        )
        ->order_by('cvd.line_no', 'ASC')
        ->get()
        ->result();
  }

  public function getAll($filters = [])
  {
    $this->db
        ->select("
          cv.id,
          cv.cv_no,
          cv.voucher_date,
          cv.payee_name,
          cv.payment_method,
          cv.check_no,
          cv.reference_no,
          cv.status,
          ba.bank_name,
          ba.account_name AS bank_account_name,
          ba.account_no,
          COALESCE(SUM(cvd.debit), 0) AS amount
        ")
        ->from('t_check_vouchers cv')
        ->join('m_bank_accounts ba', 'ba.id = cv.bank_account_id', 'left')
        ->join('t_check_voucher_details cvd', 'cvd.check_voucher_id = cv.id', 'left');

    if (!empty($filters['date_from'])) {
      $this->db->where('cv.voucher_date >=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $this->db->where('cv.voucher_date <=', $filters['date_to']);
    }

    if (!empty($filters['status'])) {
      $this->db->where('cv.status', strtoupper($filters['status']));
    }

    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("cv.cv_no ILIKE '%{$escaped}%'")
        ->or_where("cv.payee_name ILIKE '%{$escaped}%'")
        ->or_where("cv.check_no ILIKE '%{$escaped}%'")
        ->or_where("cv.reference_no ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
        ->group_by([
          'cv.id',
          'cv.cv_no',
          'cv.voucher_date',
          'cv.payee_name',
          'cv.payment_method',
          'cv.check_no',
          'cv.reference_no',
          'cv.status',
          'ba.bank_name',
          'ba.account_name',
          'ba.account_no'
        ])
        ->order_by('cv.voucher_date', 'DESC')
        ->order_by('cv.id', 'DESC')
        ->get()
        ->result();
  }

  public function getTransactionDetails($filters = [])
  {
    $this->db
      ->select("
        cv.id AS check_voucher_id,
        cv.cv_no,
        cv.voucher_date,
        cv.branch_id,
        b.branch_code,
        b.branch_name,
        cv.payee_name,
        cv.payment_method,
        cv.check_no,
        cv.reference_no,
        cv.status,
        cvd.id AS detail_id,
        cvd.line_no,
        cvd.account_id,
        coa.account_code,
        coa.account_name,
        cvd.debit,
        cvd.credit,
        cvd.remarks
      ")
      ->from('t_check_vouchers cv')
      ->join('t_check_voucher_details cvd', 'cvd.check_voucher_id = cv.id')
      ->join('m_chart_of_accounts coa', 'coa.id = cvd.account_id')
      ->join('m_branches b', 'b.id = cv.branch_id');

    if (!empty($filters['date_from'])) {
      $this->db->where('cv.voucher_date >=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $this->db->where('cv.voucher_date <=', $filters['date_to']);
    }

    if (!empty($filters['branch_id'])) {
      $this->db->where('cv.branch_id', (int) $filters['branch_id']);
    }

    if (!empty($filters['status'])) {
      $this->db->where('cv.status', strtoupper(trim($filters['status'])));
    }

    if (!empty($filters['keyword'])) {
      $escaped = $this->db->escape_like_str($filters['keyword']);

      $this->db->group_start()
        ->where("cv.cv_no ILIKE '%{$escaped}%'")
        ->or_where("cv.payee_name ILIKE '%{$escaped}%'")
        ->or_where("cv.check_no ILIKE '%{$escaped}%'")
        ->or_where("cv.reference_no ILIKE '%{$escaped}%'")
        ->or_where("coa.account_code ILIKE '%{$escaped}%'")
        ->or_where("coa.account_name ILIKE '%{$escaped}%'")
      ->group_end();
    }

    return $this->db
      ->order_by('cv.voucher_date', 'DESC')
      ->order_by('cv.id', 'DESC')
      ->order_by('cvd.line_no', 'ASC')
      ->get()
      ->result();
  }

  public function save($postData)
  {
    try {
      $this->db->trans_begin();

      if (empty($postData)) {
        throw new Exception('Invalid Check Voucher data.');
      }

      if (empty($postData->voucher_date)) {
        throw new Exception('Voucher Date is required.');
      }
      if (empty($postData->branch_id)) {
        throw new Exception('Branch is required.');
      }

      $payeeType = strtoupper(trim($postData->payee_type ?? ''));
      if (!in_array($payeeType, ['SUPPLIER', 'OTHER'], true)) {
        throw new Exception('Invalid Payee Type.');
      }

      $supplierId = null;
      $payeeName  = strtoupper(trim($postData->payee_name ?? ''));

      if ($payeeType === 'SUPPLIER') {
        if (empty($postData->supplier_id)) {
          throw new Exception('Please select a Supplier.');
        }

        $supplier = $this->db->select('id, supplier_name')
            ->where('id', (int) $postData->supplier_id)
            ->where('is_active', true)
            ->get('m_suppliers')
            ->row();

        if (!$supplier) {
          throw new Exception('Invalid or inactive Supplier.');
        }

        $supplierId = (int) $supplier->id;
        $payeeName  = strtoupper(trim($supplier->supplier_name));

      } else {

        if ($payeeName === '') {
          throw new Exception('Payee Name is required.');
        }
      }

      $paymentMethod = strtoupper(trim($postData->payment_method ?? ''));
      if (!in_array($paymentMethod, ['CHECK', 'BANK_TRANSFER', 'CASH', 'OTHER'], true)) {
        throw new Exception('Invalid Payment Method.');
      }

      $bankAccountId = !empty($postData->bank_account_id) ? (int) $postData->bank_account_id : null;
      if (in_array($paymentMethod, ['CHECK', 'BANK_TRANSFER'], true)) {
        if (!$bankAccountId) {
          throw new Exception('Please select a Bank Account.');
        }

        $bankAccount = $this->db->where('id', $bankAccountId)
            ->where('is_active', true)
            ->get('m_bank_accounts')
            ->row();

        if (!$bankAccount) {
          throw new Exception('Invalid or inactive Bank Account.');
        }

        $branchId = (int) $postData->branch_id;
        if ($bankAccount->branch_id !== null && (int) $bankAccount->branch_id !== $branchId) {
          throw new Exception('The selected Bank Account is not available for this Branch.');
        }

        if ($paymentMethod === 'CHECK') {
          $isCheckEnabled = in_array($bankAccount->is_check_enabled, [true, 1, '1', 't', 'true'], true);
          if (!$isCheckEnabled) {
            throw new Exception('The selected Bank Account is not enabled for checks.');
          }
        }
      }

      if (empty($postData->details)) {
        throw new Exception('Please add at least one accounting entry.');
      }

      $details = [];
      foreach ($postData->details as $index => $detail) {
        if (empty($detail->account_id)) {
          throw new Exception('Please select an Account for all accounting entries.');
        }

        $account = $this->db->select("id, account_code, account_name")
            ->where('id', (int) $detail->account_id)
            ->where('is_active', true)
            ->where('is_posting', true)
            ->get('m_chart_of_accounts')
            ->row();

        if (!$account) {
          throw new Exception('Invalid or non-posting Account selected.');
        }

        $debit  = round((float) ($detail->debit ?? 0), 2);
        $credit = round((float) ($detail->credit ?? 0), 2);

        if (!(($debit > 0 && $credit == 0) || ($credit > 0 && $debit == 0))) {
            throw new Exception("Accounting entry line " . ($index + 1) . " must contain either a Debit or a Credit amount.");
        }

        $details[] = [
          'account_id' => (int) $account->id,
          'debit'      => $debit,
          'credit'     => $credit,
          'remarks'    => trim($detail->remarks ?? '') !== '' ? strtoupper(trim($detail->remarks)) : null,
          'line_no'    => $index + 1
        ];
      }

      if (empty($postData->id)) {
        $header = [
          'cv_no'          => $this->Document_number_model->generate('CV'),
          'voucher_date'   => $postData->voucher_date,
          'branch_id'      => (int) $postData->branch_id,
          'payee_type'     => $payeeType,
          'supplier_id'    => $supplierId,
          'payee_name'     => $payeeName,
          'payment_method' => $paymentMethod,
          'bank_account_id'=> $bankAccountId,
          'check_no'       => trim($postData->check_no ?? '') !== '' ? strtoupper(trim($postData->check_no)) : null,
          'check_date'     => !empty($postData->check_date) ? $postData->check_date : null,
          'reference_no'   => trim($postData->reference_no ?? '') !== '' ? strtoupper(trim($postData->reference_no)) : null,
          'particulars'    => trim($postData->particulars ?? '') !== '' ? strtoupper(trim($postData->particulars)) : null,
          'status'         => 'DRAFT',
          'entered_by'     => $this->session->userdata('user_id'),
          'entered_on'     => date('Y-m-d H:i:s')
        ];

        $this->db->insert('t_check_vouchers', $header);
        $checkVoucherId = $this->db->insert_id();
        $cvNo           = $header['cv_no'];

      } else {

        $current = $this->db->select('cv_no, status')
            ->where('id', (int) $postData->id)
            ->get('t_check_vouchers')
            ->row();

        if (!$current) {
          throw new Exception('Check Voucher not found.');
        }
        if ($current->status !== 'DRAFT') {
          throw new Exception("Cannot modify a {$current->status} Check Voucher.");
        }

        $checkVoucherId = (int) $postData->id;
        $cvNo           = $current->cv_no;

        $this->db->where('id', $checkVoucherId)
          ->update('t_check_vouchers', [
            'voucher_date'   => $postData->voucher_date,
            'branch_id'      => (int) $postData->branch_id,
            'payee_type'     => $payeeType,
            'supplier_id'    => $supplierId,
            'payee_name'     => $payeeName,
            'payment_method' => $paymentMethod,
            'bank_account_id'=> $bankAccountId,
            'check_no'       => trim($postData->check_no ?? '') !== '' ? strtoupper(trim($postData->check_no)) : null,
            'check_date'     => !empty($postData->check_date) ? $postData->check_date : null,
            'reference_no'   => trim($postData->reference_no ?? '') !== '' ? strtoupper(trim($postData->reference_no)) : null,
            'particulars'    => trim($postData->particulars ?? '') !== '' ? strtoupper(trim($postData->particulars)) : null,
            'updated_by'     => $this->session->userdata('user_id'),
            'updated_on'     => date('Y-m-d H:i:s')
          ]);

        $this->db->where('check_voucher_id', $checkVoucherId)->delete('t_check_voucher_details');
      }

      foreach ($details as $detail) {
        $detail['check_voucher_id'] = $checkVoucherId;
        $this->db->insert('t_check_voucher_details', $detail);
      }

      if ($this->db->trans_status() === false) {
        throw new Exception('Unable to save Check Voucher.');
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => empty($postData->id) ? 'Check Voucher saved.' : 'Check Voucher updated.',
        'data'    => [
          'check_voucher_id' => $checkVoucherId,
          'cv_no'            => $cvNo
        ]
      ];
    } catch (Exception $ex) {
      $this->db->trans_rollback();
      return [
        'success' => false,
        'message' => $ex->getMessage(),
        'data'    => []
      ];
    }
  }

  public function post($id)
  {
    try {
      $id = (int) $id;

      if ($id <= 0) {
        throw new Exception('Invalid Check Voucher.');
      }

      $this->db->trans_begin();

      /*** lock voucher */
      $cv = $this->db
          ->query("
            SELECT *
            FROM t_check_vouchers
            WHERE id = ?
            FOR UPDATE
          ", [$id])
          ->row();

      if (!$cv) {
        throw new Exception('Check Voucher not found.');
      }

      if ($cv->status !== 'DRAFT') {
        throw new Exception('Only DRAFT Check Vouchers can be posted.');
      }

      if (!empty($cv->journal_entry_id)) {
        throw new Exception('Check Voucher already has a Journal Entry.');
      }

      /*** payment validation */
      if ($cv->payment_method === 'CHECK') {
        if (empty($cv->bank_account_id)) {
          throw new Exception('Bank Account is required for CHECK payment.');
        }

        if (trim($cv->check_no ?? '') === '') {
          throw new Exception('Check No. is required before posting.');
        }

        if (empty($cv->check_date)) {
          throw new Exception('Check Date is required before posting.');
        }
      }

      if ($cv->payment_method === 'BANK_TRANSFER') {
        if (empty($cv->bank_account_id)) {
          throw new Exception('Bank Account is required for BANK TRANSFER.');
        }

        if (trim($cv->reference_no ?? '') === '') {
          throw new Exception('Reference No. is required for BANK TRANSFER.');
        }
      }

      if ($cv->payment_method === 'CASH' && !empty($cv->bank_account_id)) {
        throw new Exception('CASH payment must not use a Bank Account.');
      }

      /*** bank validation */
      if (!empty($cv->bank_account_id)) {
        $bankAccount = $this->db
            ->where('id', (int) $cv->bank_account_id)
            ->where('is_active', true)
            ->get('m_bank_accounts')
            ->row();

        if (!$bankAccount) {
          throw new Exception('Invalid or inactive Bank Account.');
        }

        if ($bankAccount->branch_id !== null && (int) $bankAccount->branch_id !== (int) $cv->branch_id) {
          throw new Exception('The selected Bank Account is not available for this Branch.');
        }

        if ($cv->payment_method === 'CHECK') {
          $isCheckEnabled = in_array(
            $bankAccount->is_check_enabled,
            [true, 1, '1', 't', 'true'],
            true
          );

          if (!$isCheckEnabled) {
            throw new Exception('The selected Bank Account is not enabled for checks.');
          }
        }
      }

      /*** accounting distribution */
      $details = $this->db
          ->select("cvd.account_id, cvd.debit, cvd.credit, cvd.remarks, cvd.line_no, coa.is_active, coa.is_posting")
          ->from('t_check_voucher_details cvd')
          ->join('m_chart_of_accounts coa', 'coa.id = cvd.account_id')
          ->where('cvd.check_voucher_id', $id)
          ->order_by('cvd.line_no', 'ASC')
          ->get()
          ->result();

      if (empty($details)) {
        throw new Exception('Check Voucher has no accounting entries.');
      }

      $totalDebit = 0.00;
      $totalCredit = 0.00;

      foreach ($details as $index => $detail) {
        $isActive = in_array($detail->is_active, [true, 1, '1', 't', 'true'], true);
        $isPosting = in_array($detail->is_posting, [true, 1, '1', 't', 'true'], true);

        if (!$isActive || !$isPosting) {
          throw new Exception('Accounting entry line ' . ($index + 1) . ' contains an inactive or non-posting Account.');
        }

        $debit = round((float) $detail->debit, 2);
        $credit = round((float) $detail->credit, 2);

        if (!(($debit > 0 && $credit == 0) || ($credit > 0 && $debit == 0))) {
          throw new Exception('Accounting entry line ' . ($index + 1) . ' must contain either a Debit or a Credit amount.');
        }

        $totalDebit += $debit;
        $totalCredit += $credit;
      }

      $totalDebit = round($totalDebit, 2);
      $totalCredit = round($totalCredit, 2);

      if ($totalDebit <= 0 || $totalCredit <= 0) {
        throw new Exception('Check Voucher total must be greater than zero.');
      }

      if (abs($totalDebit - $totalCredit) > 0.005) {
        throw new Exception('Check Voucher is not balanced. Debit and Credit totals must be equal.');
      }

      /*** create journal */
      $userId = $this->session->userdata('user_id');
      $now = date('Y-m-d H:i:s');
      $journalNo = $this->Document_number_model->generate('JV');

      $this->db->insert('t_journal_entries', [
        'journal_no' => $journalNo,
        'journal_date' => $cv->voucher_date,
        'branch_id' => (int) $cv->branch_id,
        'transaction_type' => 'CHECK_VOUCHER',
        'source_id' => $id,
        'reference_no' => $cv->cv_no,
        'payee_name' => $cv->payee_name,
        'explanation' => $cv->particulars,
        'status' => 'POSTED',
        'entered_by' => $userId,
        'entered_on' => $now,
        'posted_by' => $userId,
        'posted_on' => $now
      ]);

      $journalEntryId = $this->db->insert_id();

      /*** journal details */
      foreach ($details as $detail) {
        $this->db->insert('t_journal_entry_details', [
          'journal_entry_id' => $journalEntryId,
          'account_id' => (int) $detail->account_id,
          'debit' => round((float) $detail->debit, 2),
          'credit' => round((float) $detail->credit, 2),
          'remarks' => $detail->remarks,
          'line_no' => (int) $detail->line_no
        ]);
      }

      /*** post voucher */
      $this->db
          ->where('id', $id)
          ->update('t_check_vouchers', [
            'status' => 'POSTED',
            'journal_entry_id' => $journalEntryId,
            'posted_by' => $userId,
            'posted_on' => $now,
            'updated_by' => $userId,
            'updated_on' => $now
          ]);

      if ($this->db->trans_status() === false) {
        throw new Exception('Unable to post Check Voucher.');
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => 'Check Voucher posted successfully.',
        'data' => [
          'check_voucher_id' => $id,
          'cv_no' => $cv->cv_no,
          'journal_entry_id' => $journalEntryId,
          'journal_no' => $journalNo
        ]
      ];

    } catch (Exception $ex) {
      $this->db->trans_rollback();

      return [
        'success' => false,
        'message' => $ex->getMessage(),
        'data' => []
      ];
    }
  }

  public function cancel($id, $reason)
  {
    $id = (int) $id;
    $reason = trim($reason);

    if ($id <= 0) {
      return ['success' => false, 'message' => 'Invalid Check Voucher.', 'data' => []];
    }

    if ($reason === '') {
      return ['success' => false, 'message' => 'Cancellation reason is required.', 'data' => []];
    }

    $this->db->trans_begin();

    try {
      $cv = $this->db
        ->query('SELECT * FROM t_check_vouchers WHERE id = ? FOR UPDATE', [$id])
        ->row();

      if (!$cv) {
        throw new Exception('Check Voucher not found.');
      }

      if ($cv->status === 'CANCELLED') {
        throw new Exception('Check Voucher is already cancelled.');
      }

      if (!in_array($cv->status, ['DRAFT', 'POSTED'], true)) {
        throw new Exception('Only DRAFT or POSTED Check Vouchers can be cancelled.');
      }

      $userId = (int) $this->session->userdata('user_id');
      $now = date('Y-m-d H:i:s');
      $reversalJournalId = null;
      $reversalJournalNo = null;

      /*** posted CV requires reversal */
      if ($cv->status === 'POSTED') {
        if (empty($cv->journal_entry_id)) {
          throw new Exception('Posted Check Voucher has no linked Journal Voucher.');
        }

        if (!empty($cv->reversal_journal_entry_id)) {
          throw new Exception('Check Voucher already has a reversal Journal Voucher.');
        }

        $journal = $this->db
          ->where('id', $cv->journal_entry_id)
          ->get('t_journal_entries')
          ->row();

        if (!$journal || $journal->status !== 'POSTED') {
          throw new Exception('Original posted Journal Voucher could not be verified.');
        }

        $journalDetails = $this->db
          ->where('journal_entry_id', $journal->id)
          ->order_by('line_no', 'ASC')
          ->get('t_journal_entry_details')
          ->result();

        if (empty($journalDetails)) {
          throw new Exception('Original Journal Voucher has no accounting entries.');
        }

        $reversalJournalNo = $this->Document_number_model->generate('JV');

        $this->db->insert('t_journal_entries', [
          'journal_no' => $reversalJournalNo,
          'journal_date' => date('Y-m-d'),
          'branch_id' => $cv->branch_id,
          'transaction_type' => 'CHECK_VOUCHER_REVERSAL',
          'source_id' => $cv->id,
          'reference_no' => $cv->cv_no,
          'payee_name' => $cv->payee_name,
          'explanation' => 'REVERSAL: ' . $reason,
          'status' => 'POSTED',
          'entered_by' => $userId,
          'entered_on' => $now,
          'posted_by' => $userId,
          'posted_on' => $now,
        ]);

        $reversalJournalId = (int) $this->db->insert_id();

        foreach ($journalDetails as $detail) {
          $this->db->insert('t_journal_entry_details', [
            'journal_entry_id' => $reversalJournalId,
            'account_id' => $detail->account_id,
            'debit' => (float) $detail->credit,
            'credit' => (float) $detail->debit,
            'remarks' => $detail->remarks,
            'line_no' => $detail->line_no,
          ]);
        }
      }

      $this->db
        ->where('id', $id)
        ->update('t_check_vouchers', [
          'status' => 'CANCELLED',
          'reversal_journal_entry_id' => $reversalJournalId,
          'cancel_reason' => $reason,
          'cancelled_by' => $userId,
          'cancelled_on' => $now,
          'updated_by' => $userId,
          'updated_on' => $now,
        ]);

      if ($this->db->trans_status() === false) {
        throw new Exception('Unable to cancel Check Voucher.');
      }

      $this->db->trans_commit();

      return [
        'success' => true,
        'message' => $cv->status === 'POSTED'
          ? 'Check Voucher cancelled and reversal Journal Voucher created.'
          : 'Check Voucher cancelled.',
        'data' => [
          'check_voucher_id' => $id,
          'cv_no' => $cv->cv_no,
          'reversal_journal_entry_id' => $reversalJournalId,
          'reversal_journal_no' => $reversalJournalNo,
        ],
      ];

    } catch (Throwable $e) {
      $this->db->trans_rollback();
      return ['success' => false, 'message' => $e->getMessage(), 'data' => []];
    }
  }

}