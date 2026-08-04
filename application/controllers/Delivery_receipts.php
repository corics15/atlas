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

    $this->data['toolbar'] = [
      'edit' => [
          'id' => 'btnEditDeliveryReceipt',
          'text' => 'Edit',
          'icon' => 'fas fa-edit'
      ],
      'post' => [
          'id' => 'btnPostDeliveryReceipt',
          'text' => 'Post',
          'icon' => 'fas fa-check-circle'
      ],
      'print' => [
          'id' => 'btnPrintDeliveryReceipt',
          'text' => 'Print',
          'icon' => 'fas fa-print'
      ],
      'cancel' => [
          'id' => 'btnCancelDeliveryReceipt',
          'text' => 'Cancel',
          'icon' => 'fas fa-ban'
      ],
      'refresh' => [
          'id' => 'btnRefreshDeliveryReceipt',
          'text' => 'Refresh',
          'icon' => 'fas fa-sync'
        ]
    ];

    $this->data['deliveryReceipts'] = $this->Delivery_receipt_model->getAll();
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

  // public function queue()
  // {
  //   $this->setPage(
  //     'Delivery Queue',
  //   );

  //   $this->data['toolbar'] = [
  //     'create' => [
  //       'id'   => 'btnCreateDeliveryReceipt',
  //       'text' => 'Create Delivery Receipt',
  //       'icon' => 'fas fa-truck',
  //     ],
  //     'refresh' => [
  //       'id'   => 'btnRefreshDeliveryReceipt',
  //       'text' => 'Refresh',
  //       'icon' => 'fas fa-sync',
  //     ]
  //   ];

  //   $this->pageScript = 'delivery_receipts';
  //   $this->data['salesOrders'] = $this->Delivery_receipt_model->getSalesOrdersForDelivery();
  //   $this->data['recordCount'] = count($this->data['salesOrders']);
  //   $this->data['dr'] = false;
  //   $this->data['dq'] = true;

  //   $this->data['tableContent'] =
  //       $this->load->view(
  //           'delivery_receipts/table',
  //           $this->data,
  //           TRUE
  //       );

  //   $this->render('delivery_receipts/index');
  // }

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

}