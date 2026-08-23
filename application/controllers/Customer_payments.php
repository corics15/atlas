<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_payments extends MY_Controller
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Customer_payment_model');
    $this->load->model('Customer_model');
    $this->load->model('Salesman_model');
    $this->load->model('Branch_model');
  }

  public function index()
  {
    $this->setPage(
      'Customer Payment List',
      [
        'id'   => 'btnNewCustomerPayment',
        'icon' => 'fa fa-plus',
        'text' => 'New Payment',
      ]
    );
    $this->pageScript = 'customer_payments';

    /*** filters */
    $this->data['statuses'] = [
      'OPEN',
      'POSTED',
      'CANCELLED',
    ];

    $this->data['payment_methods'] = [
      'CASH',
      'CHECK',
      'BANK_TRANSFER',
      'OTHER',
    ];
    $filter = $this->decodeFilter($this->input->get('filter'));
    $keyword = trim($filter['keyword'] ?? $this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $filters = [
      'date_from' => trim(
        $filter['date_from'] ??
        $this->input->get('date_from')
      ),
      'date_to' => trim(
        $filter['date_to'] ??
        $this->input->get('date_to')
      ),
      'keyword' => trim(
        $filter['keyword'] ??
        $this->input->get('keyword')
      ),
      'status' => trim(
        $filter['status'] ??
        $this->input->get('status')
      ),
      'payment_method' => trim(
        $filter['payment_method'] ??
        $this->input->get('payment_method')
      ),
    ];

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $this->data['searchPlaceHolder'] = 'Search Payment No., Reference, Customer...';
    $this->data['customerPayments'] = $this->Customer_payment_model->getAll($filters);

    foreach ($this->data['customerPayments'] as $payment) {
      $payment->url = base_url('customer-payments/edit/' . $this->encodeId($payment->id));
    }

    $this->data['recordCount'] = count($this->data['customerPayments']);
    $this->data['tableContent']
        = $this->load->view(
          'customer_payments/table',
          $this->data,
          TRUE
        );

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditCustomerPayment',
        'text' => 'Edit Payment',
        'icon' => 'fas fa-edit'
      ],
      'post' => [
        'id'   => 'btnPostCustomerPayment',
        'text' => 'Post Payment',
        'icon' => 'fas fa-check-circle'
      ],
      'print' => [
        'id'   => 'btnPrintCustomerPayment',
        'text' => 'Print Payment',
        'icon' => 'fas fa-print'
      ],
      'cancel' => [
        'id'   => 'btnCancelCustomerPayment',
        'text' => 'Cancel Payment',
        'icon' => 'fas fa-ban'
      ],
      'refresh' => [
        'id'   => 'btnRefreshCustomerPayment',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync'
      ]
    ];

    $this->render('customer_payments/index');
  }

  public function create()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $this->setPage('New Customer Payment');
    $this->pageScript = 'customer_payments';

    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['branches'] = $this->Branch_model->getDropdown();

    $this->data['isEdit'] = false;
    $this->data['isEditable'] = true;

    $this->render('customer_payments/create');
  }

  public function outstanding_invoices()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $customerId = (int)$this->getJsonRequest('customer_id');
    $invoices = $this->Customer_payment_model->getOutstandingInvoices($customerId);

    return $this->jsonResponse(
      TRUE,
      '',
      [
        'invoices' => $invoices
      ]
    );
  }

  public function save()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $customerPayment = json_decode($this->input->raw_input_stream);
    $result = $this->Customer_payment_model->save($customerPayment);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function edit($id)
  {
    $decodedId = $this->decodeId($id);

    if ($decodedId !== NULL) {
      $id = $decodedId;
    }

    if (!ctype_digit((string)$id) || (int)$id <= 0) {
      show_404();
    }

    $customerPaymentId = (int)$id;
    $customerPayment = $this->Customer_payment_model->get($customerPaymentId);

    if (!$customerPayment) {
      show_404();
    }

    $this->setPage('Edit Customer Payment');
    $this->pageScript = 'customer_payments';

    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['customerPayment'] = $customerPayment;

    $this->data['allocations'] = $this->Customer_payment_model->getAllocations($customerPaymentId);
    $this->data['customerPaymentId'] = $customerPaymentId;
    $this->data['isEdit'] = true;
    $this->data['isEditable'] = $customerPayment->status === 'OPEN';

    $this->render('customer_payments/create');
  }

  public function post()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $request = json_decode($this->input->raw_input_stream);

    $ids = $request->ids ?? [];
    $result = $this->Customer_payment_model->post($ids);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function cancel()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $request = json_decode($this->input->raw_input_stream);

    $ids = $request->ids ?? [];
    $cancelReason = $request->cancel_reason ?? null;
    $result = $this->Customer_payment_model->cancel(
      $ids,
      $cancelReason
    );

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function customer_ledger()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $customerId = (int)$this->getJsonRequest('customer_id');
    $ledger = $this->Customer_payment_model->getCustomerLedger($customerId);

    return $this->jsonResponse(
      TRUE,
      '',
      [
        'ledger' => $ledger
      ]
    );
  }

}