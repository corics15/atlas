<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_ledger extends MY_Controller
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Customer_model');
    $this->load->model('Customer_payment_model');
  }

  public function index()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF',
      'VIEWER',
    ]);

    $this->setPage('Customer Ledger');
    $this->pageScript = 'customer_ledger';
    $this->data['customers'] = $this->Customer_model->getDropdown();

    $this->render('customer_ledger/index');
  }

  public function ledger()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $customerId = (int)$this->getJsonRequest('customer_id');
    $dateFrom = trim($this->getJsonRequest('date_from') ?? '');
    $dateTo = trim($this->getJsonRequest('date_to') ?? '');

    $result = $this->Customer_payment_model->getCustomerLedger(
      $customerId,
      $dateFrom,
      $dateTo
    );

    return $this->jsonResponse(
      TRUE,
      '',
      [
        'opening_balance' => $result['opening_balance'],
        'ledger' => $result['transactions']
      ]
    );
  }

}