<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_orders extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Sales_order_model');
    $this->load->model('Customer_model');
    $this->load->model('Salesman_model');
    $this->load->model('Term_model');
  }

  public function index()
  {
    $this->setPage(
      'Sales Orders List',
      [
        'id'   => 'btnNewSalesOrder',
        'icon' => 'fa fa-plus',
        'text' => 'New Sales Order',
      ]
    );

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditSalesOrder',
        'text' => 'Edit',
        'icon' => 'fas fa-edit'
      ],
      'print' => [
        'id'   => 'btnPrintSalesOrder',
        'text' => 'Print',
        'icon' => 'fas fa-print'
      ],
      'cancel' => [
        'id'   => 'btnCancelSalesOrder',
        'text' => 'Cancel',
        'icon' => 'fas fa-ban'
      ],
      'refresh' => [
        'id'   => 'btnRefreshSalesOrder',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync'
      ]
    ];

    $this->pageScript = 'sales_orders';
    $this->data['salesOrders'] = $this->Sales_order_model->getAll();
    $this->data['recordCount'] = count($this->data['salesOrders']);

    $this->data['tableContent'] =
        $this->load->view(
            'sales_orders/table',
            $this->data,
            TRUE
        );

    $this->render('sales_orders/index');
  }

  public function create()
  {
    $this->setPage('New Sales Order');
    $this->pageScript = 'sales_orders';
    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();
    $this->render('sales_orders/create');
  }

  public function edit($id)
  {
    $this->setPage('Edit Sales Order');
    $this->pageScript = 'sales_orders';

    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesOrder'] = $this->Sales_order_model->get($id);
    $this->data['details'] = $this->Sales_order_model->getDetails($id);

    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();

    $this->data['salesOrderId'] = $id;
    $this->render('sales_orders/create');
  }

  public function save()
  {
    $postData = $this->input->raw_input_stream;
    $salesOrder = json_decode($postData);
    $result = $this->Sales_order_model->save($salesOrder);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function cancel()
  {
    $ids = $this->getJsonRequest('ids');
    $cancelReason = $this->getJsonRequest('cancel_reason');

    $result = $this->Sales_order_model->cancel($ids, $cancelReason);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function print()
  {
    $ids = $this->input->post('ids');

    if (!$ids) {
      show_404();
    }

    $documents = [];

    foreach ($ids as $id) {
      $header = $this->Sales_order_model->get($id);

      if (!$header) {
        continue;
      }

      $documents[] = (object)[
        'header'  => $header,
        'details' => $this->Sales_order_model->getDetails($id)
      ];
    }

    $this->load->view(
      'sales_orders/print',
      [
        'documents' => $documents,
        'title' => 'SALES ORDER'
      ]
    );
  }
}