<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery_receipts extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Delivery_receipt_model');
    $this->load->model('Sales_order_model');
  }

  public function index()
  {
    $this->setPage(
      'Delivery Receipts',
    );

    /*** filters */
    $this->data['statuses'] = [
      'OPEN',
      'POSTED',
      'CANCELLED',
    ];
    $filters = [
      'date_from' => trim($this->input->get('date_from')),
      'date_to' => trim($this->input->get('date_to')),
      'keyword' => trim($this->input->get('keyword')),
      'status' => trim($this->input->get('status')),
    ];
    $this->data = array_merge(
      $this->data,
      $filters
    );
    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $this->data['searchPlaceHolder'] = 'Search...';

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditDeliveryReceipt',
          'text' => 'Edit DR',
          'icon' => 'fas fa-edit'
      ],
      'post' => [
          'id' => 'btnPostDeliveryReceipt',
          'text' => 'Post DR',
          'icon' => 'fas fa-check-circle'
      ],
      'print' => [
          'id' => 'btnPrintDeliveryReceipt',
          'text' => 'Print DR',
          'icon' => 'fas fa-print'
      ],
      'cancel' => [
          'id' => 'btnCancelDeliveryReceipt',
          'text' => 'Cancel DR',
          'icon' => 'fas fa-ban'
      ],
      'create' => [
        'id'   => 'btnCreateSalesInvoice',
        'text' => 'Create Sales Invoice',
        'icon' => 'fas fa-file-contract'
      ],
      'refresh' => [
          'id' => 'btnRefreshDeliveryReceipt',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync'
        ]
    ];

    $this->data['deliveryReceipts'] = $this->Delivery_receipt_model->getAll($filters);
    $this->data['recordCount'] = count($this->data['deliveryReceipts']);
    $this->pageScript = 'delivery_receipts';
    $this->data['tableContent']
        = $this->load->view(
            'delivery_receipts/table',
            $this->data,
            TRUE
        );

    $this->render('delivery_receipts/index');
  }

  public function create($salesOrderId)
  {
    $this->setPage('New Delivery Receipt');
    $this->pageScript = 'delivery_receipts';

    $this->data['header'] = $this->Delivery_receipt_model->getSalesOrder($salesOrderId);
    $this->data['details'] = $this->Delivery_receipt_model->getSalesOrderDetails($salesOrderId);
    $this->data['salesOrderId'] = $salesOrderId;
    $this->data['isEdit'] = false;

    $this->render('delivery_receipts/create');
  }

  public function edit($id)
  {
    $this->setPage('Edit Delivery Receipt');
    $this->pageScript = 'delivery_receipts';
    $this->data['header'] = $this->Delivery_receipt_model->get($id);

    if (!$this->data['header']) {
      show_404();
    }

    $this->data['details'] = $this->Delivery_receipt_model->getDetails($id);
    $this->data['deliveryReceiptId'] = $id;
    $this->data['isEdit'] = true;

    $this->render('delivery_receipts/create');
  }

  public function save()
  {
    $postData = $this->input->raw_input_stream;
    $deliveryReceipt = json_decode($postData);
    $result = $this->Delivery_receipt_model->save($deliveryReceipt);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function post()
  {
    $request = $this->getJsonRequest();
    $result = $this->Delivery_receipt_model->post($request['id']);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function cancel()
  {
    $request = $this->getJsonRequest();
    $result = $this->Delivery_receipt_model->cancel($request['ids'], $request['cancel_reason']);

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
        $header = $this->Delivery_receipt_model->get($id);

        if (!$header) {
            continue;
        }

        $documents[] = (object)[
          'header'  => $header,
          'details' => $this->Delivery_receipt_model->getDetails($id)
        ];
    }

    $this->load->view(
      'delivery_receipts/print',
      [
        'documents' => $documents,
        'title'     => 'DELIVERY RECEIPT'
      ]
    );
  }

}