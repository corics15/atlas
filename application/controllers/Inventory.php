<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Product_model');
    $this->load->model('Inventory_model');
  }

  public function index()
  {
    $this->setPage('Inventory Inquiry');
    $this->pageScript = 'inventory';

    $filters = [
      'keyword' => trim($this->input->get('keyword')),
    ];

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;

    $this->data['toolbar'] = [
      'stockLedger' => [
        'id'   => 'btnViewStockLedger',
        'icon' => 'fas fa-history',
        'text' => 'View Stock Ledger'
      ],

      'refresh' => [
        'id'   => 'btnRefreshInventory',
        'icon' => 'fas fa-sync-alt',
        'text' => 'Refresh'
      ]
    ];

    $this->data['inventoryInquiry'] = $this->Inventory_model->getAll($filters);
    $this->data['recordCount'] = count($this->data['inventoryInquiry']);
    $this->data['searchPlaceHolder'] = 'Search Barcode, Descr, Supplier...';

    $this->data['tableContent'] = $this->load->view(
      'inventory/inventory_table',
      $this->data,
      TRUE
    );
    $this->render('inventory/index');
  }

  public function ledger($productId)
  {
    $this->setPage('Stock Ledger');
    $this->pageScript = 'inventory';
    $this->data['product'] = $this->Product_model->get($productId);

    if (!$this->data['product']) {
      show_404();
    }

    $this->data['transaction_types'] = [
      'GRN',
      'ADJUSTMENT',
    ];

    $filters = [
      'date_from' => trim($this->input->get('date_from')),
      'date_to' => trim($this->input->get('date_to')),
      'transType' => trim($this->input->get('transType')),
    ];
    $this->data = array_merge(
      $this->data,
      $filters
    );
    $this->data['ledger'] = $this->Inventory_model->getStockLedger($productId, $filters);

    $this->data['tableContent'] = $this->load->view(
      'inventory/ledger_table',
      $this->data,
      TRUE
    );

    $this->render('inventory/ledger');
  }

  public function getInventoryList()
  {
    $result = $this->Inventory_model->getInventoryList();

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function ledger_print()
  {
    $productId = $this->input->post('product_id', TRUE);
    $fromDate = $this->input->post('from_date', TRUE);
    $toDate = $this->input->post('to_date', TRUE);
    $transactionType = $this->input->post('transaction_type', TRUE);

    $data['product'] = $this->Product_model->get($productId);

    $data['ledger'] = $this->Inventory_model->getStockLedger(
        $productId,
        $fromDate,
        $toDate,
        $transactionType
    );

    $data['title'] = 'Stock Ledger';

    // $data['subtitle'] = $data['product']->description;

    $data['period'] =
        (!empty($fromDate) && !empty($toDate))
            ? date('m/d/Y', strtotime($fromDate))
                .' to '.
              date('m/d/Y', strtotime($toDate))
            : null;

    $this->load->view('inventory/ledger_print', $data);
  }
}