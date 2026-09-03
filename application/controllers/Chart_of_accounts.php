<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chart_of_accounts extends MY_Controller
{

  public function __construct()
  {
      parent::__construct();

      $this->load->model('Chart_of_account_model');
      $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Chart of Accounts',
      [
        'id'   => 'btnNewAccount',
        'icon' => 'fas fa-plus',
        'text' => 'New Account',
      ]
    );

    $this->pageScript = 'chart_of_accounts';
    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['accounts'] = $this->Chart_of_account_model->getAll($keyword);
    foreach ($this->data['accounts'] as $acc) {
      $acc->url = base_url('chart-of-accounts/edit/' . $this->encodeId($acc->id));
    }
    $this->data['recordCount'] = count($this->data['accounts']);

    $this->data['tableContent'] = $this->load->view(
      'chart_of_accounts/table',
      $this->data,
      TRUE
    );

    $this->data['toolbar'] = [
      'excel' => [
        'id'   => 'btnDownloadExcel',
        'icon' => 'fas fa-file-excel',
        'text' => 'Download as Excel'
      ],
      'refresh' => [
        'id'   => 'btnRefresh',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync',
        'url'  => 'chart-of-accounts',
      ]
    ];

    $this->render('chart_of_accounts/index');
  }

  public function create()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER'
    ]);

    $this->setPage('New Chart of Account');
    $this->pageScript = 'chart_of_accounts';
    $this->data['parentAccounts'] = $this->Chart_of_account_model->getParentAccounts();
    $this->data['accountGroups'] = $this->Chart_of_account_model->getAccountGroups();

    $this->render(
      'chart_of_accounts/create',
      $this->data,
      TRUE
    );
  }

  public function edit($id)
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER'
    ]);

    $decodedId = $this->decodeId($id);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $accountId = (int) $id;

    $account = $this->Chart_of_account_model->getById($accountId);

    if (!$account) {
      show_404();
    }

    $this->setPage('Edit Chart of Account');
    $this->pageScript = 'chart_of_accounts';
    $this->data['account'] = $account;
    $this->data['parentAccounts'] = $this->Chart_of_account_model->getParentAccounts();
    $this->data['accountGroups'] = $this->Chart_of_account_model->getAccountGroups();

    $this->render(
      'chart_of_accounts/create',
      $this->data,
      TRUE
    );
  }

  public function save()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $this->requireAccess([
      'ADMIN',
      'MANAGER'
    ]);

    $request = $this->getJsonRequest();
    $request['user_id'] = (int) $this->session->userdata('user_id');
    $result = $this->Chart_of_account_model->save($request);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

}