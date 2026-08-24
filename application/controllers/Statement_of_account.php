<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statement_of_account extends MY_Controller
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

    $this->setPage('Statement of Account');
    $this->pageScript = 'statement_of_account';

    $this->data['customers'] = $this->Customer_model->getDropdown();

    $filter = $this->decodeFilter($this->input->get('filter'));
    $customerId = (int)($filter['customer_id'] ?? $this->input->get('customer_id'));
    $dateFrom = trim($filter['date_from'] ?? $this->input->get('date_from') ?? date('Y-m-01'));
    $dateTo = trim(      $filter['date_to'] ?? $this->input->get('date_to') ?? date('Y-m-d'));

    $this->data['customer_id'] = $customerId;
    $this->data['hashedCustomerId'] = $this->encodeFilter($customerId);
    $this->data['date_from'] = $dateFrom;
    $this->data['date_to'] = $dateTo;

    $this->data['customer'] = NULL;
    $this->data['openingBalance'] = 0;
    $this->data['transactions'] = [];
    $this->data['periodInvoiced'] = 0;
    $this->data['periodPaid'] = 0;
    $this->data['amountDue'] = 0;

    if ($customerId > 0) {
      $this->data['customer'] = $this->Customer_model->get($customerId);

      if (!$this->data['customer']) {
        show_404();
      }

      $result = $this->Customer_payment_model
              ->getCustomerLedger(
                $customerId,
                $dateFrom,
                $dateTo
              );

      $this->data['openingBalance'] = (float)$result['opening_balance'];
      $this->data['transactions'] = $result['transactions'];
      foreach ($this->data['transactions'] as $row) {
        $this->data['periodInvoiced'] += (float)$row->debit;
        $this->data['periodPaid'] += (float)$row->credit;
      }

      $this->data['amountDue'] = $this->data['openingBalance'] + $this->data['periodInvoiced'] - $this->data['periodPaid'];
      $this->data['tableContent'] =
          $this->load->view(
            'statement_of_account/soa_table',
            $this->data,
            TRUE
          );
    }

    $this->render('statement_of_account/index');
  }

  public function generate()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $customerId = (int)$this->getJsonRequest('customer_id');
    $dateFrom = trim($this->getJsonRequest('date_from') ?? '');
    $dateTo = trim($this->getJsonRequest('date_to') ?? '');

    if ($customerId <= 0) {
      return $this->jsonResponse(
        FALSE,
        'Please select a customer.',
        []
      );
    }

    if ($dateFrom === '' || $dateTo === '') {
      return $this->jsonResponse(
        FALSE,
        'Statement period is required.',
        []
      );
    }

    if ($dateFrom > $dateTo) {
      return $this->jsonResponse(
        FALSE,
        'Date From cannot be later than Date To.',
        []
      );
    }

    $customer = $this->Customer_model->get($customerId);

    if (!$customer) {
      return $this->jsonResponse(
        FALSE,
        'Customer not found.',
        []
      );
    }

    $result = $this->Customer_payment_model
        ->getCustomerLedger(
          $customerId,
          $dateFrom,
          $dateTo
        );

    return $this->jsonResponse(
      TRUE,
      '',
      [
        'customer' => $customer,
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'opening_balance' => $result['opening_balance'],
        'transactions' => $result['transactions']
      ]
    );
  }

  public function print()
  {
    $customerId = (int)$this->input->post('customer_id');
    $dateFrom = trim($this->input->post('date_from') ?? '');
    $dateTo = trim($this->input->post('date_to') ?? '');

    if ($customerId <= 0 || $dateFrom === '' || $dateTo === '') {
      show_404();
    }

    if ($dateFrom > $dateTo) {
      show_404();
    }

    $customer = $this->Customer_model->get($customerId);
    if (!$customer) {
      show_404();
    }

    $result = $this->Customer_payment_model->getCustomerLedger($customerId, $dateFrom, $dateTo);
    // $this->data['hashedCustomerId'] = $this->encodeFilter($customerId);

    $openingBalance = (float)$result['opening_balance'];
    $transactions = $result['transactions'];
    $periodInvoiced = 0;
    $periodPaid = 0;

    foreach ($transactions as $row) {
      $periodInvoiced += (float)$row->debit;
      $periodPaid += (float)$row->credit;
    }

    $amountDue = $openingBalance + $periodInvoiced - $periodPaid;

    $this->load->view(
      'statement_of_account/print',
      [
        'title' => 'Statement of Account',
        'customer' => $customer,
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'openingBalance' => $openingBalance,
        'transactions' => $transactions,
        'periodInvoiced' => $periodInvoiced,
        'periodPaid' => $periodPaid,
        'amountDue' => $amountDue,
        'hashedCustomerId' => $this->encodeFilter($customerId)
      ]
    );
  }

}