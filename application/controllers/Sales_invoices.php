<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_invoices extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Sales_invoice_model');
    $this->load->model('Sales_order_model');
    $this->load->model('Customer_model');
    $this->load->model('Salesman_model');
    $this->load->model('Term_model');
    $this->load->model('Inventory_model');
  }

  public function index()
  {
    $this->setPage('Sales Invoice List');
    $this->pageScript = 'sales_invoices';
    $this->data['salesInvoices'] = $this->Sales_invoice_model->getAll();
    $this->data['recordCount'] = count($this->data['salesInvoices']);

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditSalesInvoice',
        'text' => 'Edit Invoice',
        'icon' => 'fas fa-edit'
      ],
      'post' => [
        'id'   => 'btnPostSalesInvoice',
        'text' => 'Post Invoice',
        'icon' => 'fas fa-check-circle'
      ],
      'print' => [
        'id'   => 'btnPrintSalesInvoice',
        'text' => 'Print Invoice',
        'icon' => 'fas fa-print'
      ],
      'cancel' => [
        'id'   => 'btnCancelSalesInvoice',
        'text' => 'Cancel Invoice',
        'icon' => 'fas fa-ban'
      ],
      'create' => [
        'id'   => 'btnCreateSalesReturn',
        'text' => 'Create Sales Return',
        'icon' => 'fas fa-exchange-alt'
      ],
      'refresh' => [
        'id'   => 'btnRefreshSalesInvoice',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync'
      ]
    ];

    $this->data['tableContent'] =
        $this->load->view(
            'sales_invoices/table',
            $this->data,
            TRUE
        );

    $this->render('sales_invoices/index');
  }

  public function create($deliveryReceiptId = null)
  {
    $this->setPage('New Sales Invoice');
    $this->pageScript = 'sales_invoices';
    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();
    $this->data['deliveryReceiptId'] = $deliveryReceiptId;

    if ($deliveryReceiptId) {

      $deliveryReceipt = $this->Sales_invoice_model ->getDeliveryReceipt($deliveryReceiptId);

      if (!$deliveryReceipt) {
        $this->data['error_message'] = 'Delivery Receipt not found or not POSTED.';

        $this->render('sales_invoices/create');
        return;
      }

      $this->data['deliveryReceipt'] = $deliveryReceipt;
      $details = $this->Sales_invoice_model->getDeliveryReceiptDetails($deliveryReceiptId);

      if (empty($details)) {
        $this->data['error_message'] = 'This Delivery Receipt has already been fully invoiced.';

        $this->render('sales_invoices/create');
        return;
      }

      $this->data['details'] = $details;
    }

    $this->render('sales_invoices/create');
  }

  public function create_old($salesOrderId = null)
  {
    $this->setPage('New Sales Invoice');
    $this->pageScript = 'sales_invoices';
    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();
    $this->data['salesOrderId'] = $salesOrderId;

    if ($salesOrderId) {
      $salesOrder = $this->Sales_invoice_model->getSalesOrder($salesOrderId);

      if (!$salesOrder) {
        $this->data['error_message'] = 'Sales Order not found or not POSTED.';
        $this->render('sales_invoices/create');
        return;
      }

      $this->data['salesOrder'] = $salesOrder;

      if (!$this->Sales_invoice_model->hasRemainingItems($salesOrderId)) {
        $this->data['error_message'] = 'This Sales Order has already been fully invoiced. No remaining quantities are available for invoicing.';
        $this->render('sales_invoices/create');
        return;
      }

      $this->data['details'] = $this->Sales_invoice_model->getSalesOrderDetails($salesOrderId);
    }

    $this->render('sales_invoices/create');
  }

  public function edit($id)
  {
    $this->setPage('Edit Sales Invoice');
    $this->pageScript = 'sales_invoices';
    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();
    $this->data['salesInvoice'] = $this->Sales_invoice_model->get($id);

    if (!$this->data['salesInvoice']) {
      show_404();
    }

    $this->data['details'] = $this->Sales_invoice_model->getDetails($id);
    $this->data['salesInvoiceId'] = $id;

    $this->render('sales_invoices/create');
  }

  public function save()
  {
    $salesInvoice =json_decode($this->input->raw_input_stream);
    $result = $this->Sales_invoice_model->save($salesInvoice);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function post()
  {
    $request = $this->getJsonRequest();
    $result = $this->Sales_invoice_model->post($request['ids']);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function cancel()
  {
    $ids = $this->getJsonRequest('ids');
    $cancelReason = $this->input->post('cancel_reason');
    $result = $this->Sales_invoice_model->cancel($ids, $cancelReason);

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
      $header = $this->Sales_invoice_model->get($id);

      if (!$header) {
        continue;
      }

      $documents[] = (object)[
        'header'  => $header,
        'details' => $this->Sales_invoice_model->getDetails($id)
      ];
    }

    $this->load->view(
      'sales_invoices/print',
      [
        'documents' => $documents,
        'title'     => 'SALES INVOICE'
      ]
    );
  }

}