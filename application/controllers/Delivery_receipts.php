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

    $filter = $this->decodeFilter($this->input->get('filter'));
    $keyword = trim($filter['keyword'] ?? $this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
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

    $this->data['searchPlaceHolder'] = 'Search...';
    $this->data['deliveryReceipts'] = $this->Delivery_receipt_model->getAll($filters);
    foreach ($this->data['deliveryReceipts'] as $dr) {
      $dr->url = base_url('delivery-receipts/edit/' . $this->encodeId($dr->id));
      $dr->so_url = base_url('sales-orders/edit/' . $this->encodeId($dr->so_id));
    }

    $this->data['recordCount'] = count($this->data['deliveryReceipts']);
    $this->pageScript = 'delivery_receipts';
    $this->data['tableContent']
        = $this->load->view(
            'delivery_receipts/table',
            $this->data,
            TRUE
        );

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

    $this->render('delivery_receipts/index');
  }

  public function create($id)
  {
    $decodedId = $this->decodeId($id);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $salesOrderId = (int) $id;

    $this->setPage('New Delivery Receipt');
    $this->pageScript = 'delivery_receipts';

    $this->data['header'] = $this->Delivery_receipt_model->getSalesOrder($salesOrderId);
    $urlLink = isset($this->data['header']->sales_order_id) ? $this->encodeId($this->data['header']->sales_order_id) : $this->encodeId($this->data['header']->id);
    $this->data['header']->url = base_url('sales-orders/edit/'.$urlLink);

    $this->data['details'] = $this->Delivery_receipt_model->getSalesOrderDetails($salesOrderId);
    $this->data['salesOrderId'] = $salesOrderId;
    $this->data['isEdit'] = false;

    $this->render('delivery_receipts/create');
  }

  public function edit($id = 0)
  {
    $decodedId = $this->decodeId($id);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $deliveryReceiptId = (int) $id;

    $this->setPage('Edit Delivery Receipt');
    $this->pageScript = 'delivery_receipts';
    $this->data['header'] = $this->Delivery_receipt_model->get($deliveryReceiptId);
    $urlLink = isset($this->data['header']->sales_order_id) ? $this->encodeId($this->data['header']->sales_order_id) : $this->encodeId($this->data['header']->id);
    $this->data['header']->url = base_url('sales-orders/edit/'.$urlLink);

    if (!$this->data['header']) {
      show_404();
    }

    $this->data['details'] = $this->Delivery_receipt_model->getDetails($deliveryReceiptId);
    $this->data['deliveryReceiptId'] = $deliveryReceiptId;
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