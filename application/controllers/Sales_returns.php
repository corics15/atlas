<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_returns extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Sales_invoice_model');
    $this->load->model('Customer_model');
    $this->load->model('Salesman_model');
    $this->load->model('Term_model');
    $this->load->model('Inventory_model');
    $this->load->model('Sales_return_model');
  }

  public function index()
  {
    $this->setPage('Sales Return List');
    $this->pageScript = 'sales_returns';

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

    $this->data['salesReturns'] = $this->Sales_return_model->getAll($filters);
    foreach ($this->data['salesReturns'] as $sr) {
      $sr->url = base_url('sales-returns/edit/' . $this->encodeId($sr->id));
      $sr->si_url = base_url('sales-invoices/edit/' . $this->encodeId($sr->sales_invoice_id));
    }

    $this->data['recordCount'] = count($this->data['salesReturns']);

    $this->data['tableContent'] =
        $this->load->view(
            'sales_returns/table',
            $this->data,
            TRUE
        );

    $this->data['toolbar'] = [
        'edit' => [
            'id'   => 'btnEditSalesReturn',
            'text' => 'Edit',
            'icon' => 'fas fa-edit'
        ],
        'post' => [
            'id'   => 'btnPostSalesReturn',
            'text' => 'Post',
            'icon' => 'fas fa-check-circle'
        ],
        'print' => [
            'id'   => 'btnPrintSalesReturn',
            'text' => 'Print',
            'icon' => 'fas fa-print'
        ],
        'cancel' => [
            'id'   => 'btnCancelSalesReturn',
            'text' => 'Cancel',
            'icon' => 'fas fa-ban'
        ],
        'refresh' => [
            'id'   => 'btnRefreshSalesReturn',
            'text' => 'Refresh',
            'icon' => 'fas fa-sync'
        ]
    ];

    $this->render('sales_returns/index');
  }

  public function create($salesInvoiceId = null)
  {
    if (!$salesInvoiceId) {
      show_404();
    }

    $salesInvoice = $this->Sales_return_model->getSalesInvoice($salesInvoiceId);

    if (!$salesInvoice) {
      show_404();
    }

    $this->setPage('New Sales Return');
    $this->pageScript = 'sales_returns';

    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();

    $this->data['salesInvoice'] = $salesInvoice;
    $urlLink = $this->encodeId($this->data['salesInvoice']->id);
    $this->data['salesInvoice']->url = base_url('sales-invoices/edit/'.$urlLink);

    $this->data['details'] = $this->Sales_return_model->getSalesInvoiceDetails($salesInvoiceId);
    $this->data['isEdit'] = FALSE;

    $this->render('sales_returns/create');
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
    $salesReturnId = (int) $id;

    $this->setPage('Edit Sales Return');
    $this->pageScript = 'sales_returns';

    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();

    $this->data['header'] = $this->Sales_return_model->get($salesReturnId);

    if (!$this->data['header']) {
      show_404();
    }

    $this->data['salesInvoice'] = $this->Sales_return_model->getSalesInvoice(
      $this->data['header']->sales_invoice_id
    );
    $urlLink = $this->encodeId($this->data['salesInvoice']->id);
    $this->data['salesInvoice']->url = base_url('sales-invoices/edit/'.$urlLink);

    $this->data['details'] = $this->Sales_return_model->getDetails($salesReturnId);
    $this->data['isEdit'] = TRUE;

    $this->render('sales_returns/create');
  }

  public function save()
  {
    $salesReturn = json_decode($this->input->raw_input_stream);
    $result = $this->Sales_return_model->save($salesReturn);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function post()
  {
    $request = $this->getJsonRequest();
    $result = $this->Sales_return_model->post($request['ids']);

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
    $result = $this->Sales_return_model->cancel($ids, $cancelReason);

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
      $header = $this->Sales_return_model->get($id);

      if (!$header) {
        continue;
      }

      $documents[] = (object)[
        'header'  => $header,
        'details' => $this->Sales_return_model->getDetails($id)
      ];
    }

    $this->load->view(
      'sales_returns/print',
      [
        'documents' => $documents,
        'title'     => 'SALES RETURN'
      ]
    );
  }

}