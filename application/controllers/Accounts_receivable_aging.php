<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts_receivable_aging extends MY_Controller
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
      'VIEWER'
    ]);

    $this->setPage('Accounts Receivable Aging');
    $this->pageScript = 'accounts_receivable_aging';

    $this->data['customers'] = $this->Customer_model->getDropdown();
    $filter = $this->decodeFilter($this->input->get('filter'));
    $asOfDate = trim($filter['as_of_date'] ?? $this->input->get('as_of_date') ?? date('Y-m-d'));
    $customerId = (int)($filter['customer_id'] ?? $this->input->get('customer_id'));

    $this->data['as_of_date'] = $asOfDate;
    $this->data['customer_id'] = $customerId;

    $this->data['aging'] = $this->Customer_payment_model->getArAging($asOfDate, $customerId > 0 ? $customerId : NULL);
    $this->data['currentTotal'] = 0;
    $this->data['days1To30Total'] = 0;
    $this->data['days31To60Total'] = 0;
    $this->data['days61To90Total'] = 0;
    $this->data['over90Total'] = 0;
    $this->data['grandTotal'] = 0;

    foreach ($this->data['aging'] as $row) {
      $this->data['currentTotal'] += (float)$row->current_amount;
      $this->data['days1To30Total'] += (float)$row->days_1_30;
      $this->data['days31To60Total'] += (float)$row->days_31_60;
      $this->data['days61To90Total'] += (float)$row->days_61_90;
      $this->data['over90Total'] += (float)$row->over_90;
      $this->data['grandTotal'] += (float)$row->total_balance;
    }

    $this->data['recordCount'] = count($this->data['aging']);
    $this->data['tableContent'] = $this->load->view(
          'accounts_receivable_aging/aging_table',
          $this->data,
          TRUE
        );

    $this->render('accounts_receivable_aging/index');
  }

}