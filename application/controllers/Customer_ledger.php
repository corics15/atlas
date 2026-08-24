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
      'VIEWER'
    ]);

    $this->setPage('Customer Ledger');
    $this->pageScript = 'customer_ledger';

    $this->data['customers'] = $this->Customer_model->getDropdown();

    $filter = $this->decodeFilter($this->input->get('filter'));
    $customerId = (int)($filter['customer_id'] ?? $this->input->get('customer_id'));
    $dateFrom = trim($filter['date_from'] ?? $this->input->get('date_from') ?? date('Y-m-01'));
    $dateTo = trim($filter['date_to'] ?? $this->input->get('date_to') ?? date('Y-m-d'));

    $this->data['customer_id'] = $customerId;
    $this->data['date_from'] = $dateFrom;
    $this->data['date_to'] = $dateTo;

    $this->data['customer'] = NULL;
    $this->data['openingBalance'] = 0;
    $this->data['ledger'] = [];
    $this->data['periodInvoiced'] = 0;
    $this->data['periodPaid'] = 0;
    $this->data['currentBalance'] = 0;

    if ($customerId > 0) {
      $this->data['customer'] = $this->Customer_model->get($customerId);
      $result = $this->Customer_payment_model->getCustomerLedger($customerId, $dateFrom, $dateTo);

      $this->data['openingBalance'] = (float)$result['opening_balance'];
      $this->data['ledger'] = $result['transactions'];

      foreach ($this->data['ledger'] as $row) {
        $this->data['periodInvoiced'] += (float)$row->debit;
        $this->data['periodPaid'] += (float)$row->credit;

        if ($row->transaction_type === 'SALES INVOICE') {
          $row->si_url = base_url('sales-invoices/edit/' . $this->encodeId($row->transaction_id));
        } elseif ($row->transaction_type === 'CUSTOMER PAYMENT') {
          $row->cp_url = base_url('customer-payments/edit/' . $this->encodeId($row->transaction_id));
        }
      }

      $this->data['currentBalance'] = $this->data['openingBalance'] + $this->data['periodInvoiced'] - $this->data['periodPaid'];
      $this->data['tableContent'] = $this->load->view(
          'customer_ledger/ledger_table',
          $this->data,
          TRUE
        );
    }

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