<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Check_vouchers extends MY_Controller
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model([
      'Check_voucher_model',
      'Branch_model',
      'Supplier_model',
      'Bank_account_model',
      'Chart_of_account_model',
    ]);
  }

  public function index()
  {
    $this->setPage(
      'Check Vouchers List',
      [
        'id' => 'btnNewCheckVoucher',
        'icon' => 'fa fa-plus',
        'text' => 'New Check Voucher',
      ]
    );

    $this->data['statuses'] = ['DRAFT', 'POSTED', 'CANCELLED'];

    $filter = $this->decodeFilter($this->input->get('filter'));
    $filters = [
      'date_from' => trim($filter['date_from'] ?? $this->input->get('date_from')),
      'date_to' => trim($filter['date_to'] ?? $this->input->get('date_to')),
      'keyword' => trim($filter['keyword'] ?? $this->input->get('keyword')),
      'status' => trim($filter['status'] ?? $this->input->get('status')),
    ];

    $this->data = array_merge($this->data, $filters);
    $this->data['checkVouchers'] = $this->Check_voucher_model->getAll($filters);
    $this->data['recordCount'] = count($this->data['checkVouchers']);
    foreach ($this->data['checkVouchers'] as $cv) {
      $cv->url = base_url('check-vouchers/edit/' . $this->encodeId($cv->id));
    }

    $this->pageScript = 'check_vouchers';
    $this->data['searchPlaceHolder'] = 'Search CV No., Payee, Check No., Reference...';
    $this->data['tableContent'] = $this->load->view('check_vouchers/table', $this->data, true);

    $this->data['toolbar'] = [
      'transactions' => [
        'id'   => 'btnTransactionDetails',
        'text' => 'Transaction Details',
        'icon' => 'fas fa-align-justify',
        'url'  => 'check-vouchers/transaction-details',
      ],
      'refresh' => [
        'id'   => 'btnRefresh',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync',
        'url'  => 'check-vouchers'
      ],
    ];
    $this->data['showDateFilter'] = TRUE;
    $this->data['showStatusFilter'] = TRUE;

    $this->render('check_vouchers/index');
  }

  public function create()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $this->setPage('New Check Voucher');
    $this->pageScript = 'check_vouchers';

    $branchId = (int) $this->session->userdata('branch_id');

    $this->data['checkVoucher'] = NULL;
    $this->data['details'] = [];
    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['bankAccounts'] = $this->Bank_account_model->getDropdown($branchId);
    $this->data['isEditable'] = TRUE;

    $this->render('check_vouchers/create');
  }

  public function edit($id)
  {
    $decodedId = $this->decodeId($id);

    if ($decodedId !== NULL) {
      $id = $decodedId;
    }

    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }

    $checkVoucherId = (int) $id;

    $checkVoucher = $this->Check_voucher_model->get($checkVoucherId);
    if (!$checkVoucher) {
      show_404();
    }

    $this->setPage('Edit Check Voucher');
    $this->pageScript = 'check_vouchers';

    $this->data['checkVoucher'] = $checkVoucher;
    $this->data['details'] = $this->Check_voucher_model->getDetails($checkVoucherId);
    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['bankAccounts'] = $this->Bank_account_model->getDropdown($checkVoucher->branch_id);
    $hasAccess = in_array(
      $this->session->userdata('access_level'),
      ['ADMIN', 'MANAGER', 'STAFF'],
      TRUE
    );
    $this->data['isEditable'] = $hasAccess && $checkVoucher->status === 'DRAFT';

    $this->render('check_vouchers/create');
  }

  public function save()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $postData = $this->input->raw_input_stream;
    $checkVoucher = json_decode($postData);

    $result = $this->Check_voucher_model->save(
      $checkVoucher
    );

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function post($id)
  {
    $result = $this->Check_voucher_model->post((int) $id);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function cancel($id)
  {
    $post = json_decode($this->input->raw_input_stream, true) ?: [];
    $result = $this->Check_voucher_model->cancel((int) $id, $post['reason'] ?? '');

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function search_accounts()
  {
    $keyword = trim($this->input->get('q', TRUE) ?? '');

    if (strlen($keyword) < 2) {
      return $this->jsonResponse(
        TRUE,
        '',
        []
      );
    }

    $accounts = $this->Chart_of_account_model->searchPostingAccounts($keyword, 10);
    $data = [];
    foreach ($accounts as $account) {
      $data[] = [
        'id' => (int) $account->id,
        'account_code' => $account->account_code,
        'account_name' => $account->account_name,
        'account_type' => $account->account_type,
        'normal_balance' => $account->normal_balance
      ];
    }

    return $this->jsonResponse(
      TRUE,
      '',
      $data
    );
  }

  public function transaction_details()
  {
    $this->setPage('Check Voucher Transaction Details');

    $filter = $this->decodeFilter($this->input->get('filter'));
    $filters = [
      'date_from' => trim($filter['date_from'] ?? $this->input->get('date_from')),
      'date_to' => trim($filter['date_to'] ?? $this->input->get('date_to')),
      'branch_id' => trim($filter['branch_id'] ?? $this->input->get('branch_id')),
      'status' => trim($filter['status'] ?? $this->input->get('status')),
      'keyword' => trim($filter['keyword'] ?? $this->input->get('keyword')),
    ];

    $details = $this->Check_voucher_model->getTransactionDetails($filters);
    $cvIds = [];
    $totalDebit = 0;
    $totalCredit = 0;

    foreach ($details as $detail) {
      $cvIds[(int) $detail->check_voucher_id] = TRUE;
      $totalDebit += (float) $detail->debit;
      $totalCredit += (float) $detail->credit;
      $detail->url = base_url('check-vouchers/edit/' . $this->encodeId($detail->check_voucher_id));
    }

    $this->data = array_merge($this->data, $filters);
    $this->data['details'] = $details;
    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['statuses'] = ['DRAFT', 'POSTED', 'CANCELLED'];
    $this->data['summary'] = [
      'cv_count' => count($cvIds),
      'line_count' => count($details),
      'total_debit' => $totalDebit,
      'total_credit' => $totalCredit,
    ];

    $this->data['toolbar'] = [
      'transactions' => [
        'id'   => 'btnRegistry',
        'text' => 'Back to Registry',
        'icon' => 'fas fa-align-justify',
        'url'  => 'check-vouchers',
      ],
      'refresh' => [
        'id'   => 'btnRefresh',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync',
        'url'  => 'check-vouchers/transaction-details'
      ],
    ];

    $this->pageScript = 'check_vouchers';
    $this->data['searchPlaceHolder'] = 'Search CV No., Payee, Account, Check No., Reference...';
    $this->data['tableContent'] = $this->load->view('check_vouchers/transaction_details_table', $this->data, TRUE);
    $this->data['showDateFilter'] = TRUE;

    $this->render('check_vouchers/transaction_details');
  }

}