<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_transfers extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $this->load->model('Branch_model');
    $this->load->model('Inventory_model');
    $this->load->model('Stock_transfer_model');
  }

  public function index()
  {
    $this->setPage(
      'Stock Transfer List',
      [
        'id'   => 'btnNewStockTransfer',
        'icon' => 'fa fa-plus',
        'text' => 'New Stock Transfer',
      ]
    );

    $this->pageScript = 'stock_transfers';

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
      'date_to'   => trim($filter['date_to'] ?? $this->input->get('date_to')),
      'status'    => trim($filter['status'] ?? $this->input->get('status')),
      'keyword' => trim($filter['keyword'] ?? $this->input->get('keyword')),
    ];

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $this->data['stockTransfers'] = $this->Stock_transfer_model->getAll($filters);
    foreach ($this->data['stockTransfers'] as $st) {
      $st->url = base_url('stock-transfers/edit/' . $this->encodeId($st->id));
    }

    $this->data['searchPlaceHolder'] = 'Search Transfer #, Branch';
    $this->data['recordCount'] = count($this->data['stockTransfers']);
    $this->data['tableContent'] =
        $this->load->view(
            'stock_transfers/table',
            $this->data,
            TRUE
        );

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditStockTransfer',
        'text' => 'Edit',
        'icon' => 'fas fa-edit'
      ],
      'post' => [
        'id'   => 'btnPostStockTransfer',
        'text' => 'Post',
        'icon' => 'fas fa-check-circle'
      ],
      'print' => [
        'id'   => 'btnPrintStockTransfer',
        'text' => 'Print',
        'icon' => 'fas fa-print'
      ],
      'cancel' => [
        'id'   => 'btnCancelStockTransfer',
        'text' => 'Cancel',
        'icon' => 'fas fa-ban'
      ],
      'refresh' => [
        'id'   => 'btnRefreshStockTransfer',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync',
        'url'  => 'stock-transfers',
      ]
    ];

    $this->render('stock_transfers/index');
  }

  public function create()
  {
    $this->setPage('New Stock Transfer');
    $this->pageScript = 'stock_transfers';

    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['currentBranchId'] = $this->session->userdata('branch_id');
    $this->render('stock_transfers/create');
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
    $stockTransferId = (int) $id;

    $this->setPage('Edit Stock Transfer');
    $this->pageScript = 'stock_transfers';
    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['stockTransfer'] = $this->Stock_transfer_model->get($stockTransferId);
    $this->data['details'] = $this->Stock_transfer_model->getDetails($stockTransferId);
    $this->data['stockTransferId'] = $stockTransferId;
    $this->render('stock_transfers/create');
  }

  public function save()
  {
    $postData = $this->input->raw_input_stream;
    $stockTransfer = json_decode($postData);
    $result = $this->Stock_transfer_model->save($stockTransfer);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function post()
  {
    $ids = $this->getJsonRequest('ids');
    // $result = $this->Inventory_model->postStockTransfer($ids[0]);
    $result = $this->Stock_transfer_model->post($ids);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data'] ?? []
    );
  }

  public function cancel()
  {
    $ids = $this->getJsonRequest('ids');
    $cancelReason = $this->getJsonRequest('cancel_reason');

    $result = $this->Stock_transfer_model->cancel($ids, $cancelReason);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function getProducts()
  {
    $result = $this->Inventory_model->getInventoryList();

    return $this->jsonResponse(
        $result['success'],
        $result['message'],
        $result['data']
    );
  }

  public function getById($id)
  {
    return $this->jsonResponse(
      TRUE,
      '',
      [
        'header' => $this->Stock_transfer_model->get($id),
        'details' => $this->Stock_transfer_model->getDetails($id)
      ]
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
      $header = $this->Stock_transfer_model->get($id);

      if (!$header) {
        continue;
      }

      $documents[] = (object)[
        'header'  => $header,
        'details' => $this->Stock_transfer_model->getDetails($id)
      ];
    }

    $this->load->view(
      'stock_transfers/print',
      [
        'documents' => $documents,
        'title' => 'STOCK TRANSFER'
      ]
    );
  }

}