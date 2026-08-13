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

    /*** filters */
    $filter = $this->decodeFilter($this->input->get('filter'));
    $this->data['statuses'] = [
      'OPEN',
      'POSTED',
      'COMPLETED',
      'CANCELLED',
    ];
    $filters = [
      'date_from' => trim($filter['date_from'] ?? $this->input->get('date_from')),
      'date_to' => trim($filter['date_to'] ?? $this->input->get('date_to')),
      'keyword' => trim($filter['keyword'] ?? $this->input->get('keyword')),
      'status' => trim($filter['status'] ?? $this->input->get('status')),
    ];
    $this->data = array_merge(
      $this->data,
      $filters
    );
    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['searchPlaceHolder'] = 'Search...';

    $this->pageScript = 'sales_orders';
    $this->data['salesOrders'] = $this->Sales_order_model->getAll($filters);
    foreach ($this->data['salesOrders'] as $so) {
      $so->url = base_url('sales-orders/edit/' . $this->encodeId($so->id));
    }

    $this->data['recordCount'] = count($this->data['salesOrders']);

    $this->data['tableContent'] =
        $this->load->view(
            'sales_orders/table',
            $this->data,
            TRUE
        );

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditSalesOrder',
        'text' => 'Edit SO',
        'icon' => 'fas fa-edit'
      ],
      'post' => [
        'id'   => 'btnPostSalesOrder',
        'text' => 'Post SO',
        'icon' => 'fas fa-check-circle'
      ],
      'create-dr' => [
        'id'   => 'btnCreateDeliveryReceipt',
        'text' => 'Create Delivery Receipt',
        'icon' => 'fas fa-truck',
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

  public function post()
  {
    $request = $this->getJsonRequest();
    $result = $this->Sales_order_model->post($request['ids']);

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
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $salesOrderId = (int) $id;

    $this->setPage('Edit Sales Order');
    $this->pageScript = 'sales_orders';

    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesOrder'] = $this->Sales_order_model->get($salesOrderId);
    $this->data['details'] = $this->Sales_order_model->getDetails($salesOrderId);

    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();

    $this->data['salesOrderId'] = $salesOrderId;
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