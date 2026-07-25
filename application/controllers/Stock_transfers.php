<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_transfers extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $this->setPage('Stock Transfers');
    $this->pageScript = 'stock_transfers';

    // $filters = [
    //   'keyword' => trim($this->input->get('keyword')),
    // ];

    // $this->data = array_merge(
    //   $this->data,
    //   $filters
    // );

    // $keyword = trim($this->input->get('keyword'));
    // $this->data['keyword'] = $keyword;

    // $this->data['toolbar'] = [
    //   'stockLedger' => [
    //     'id'   => 'btnViewStockLedger',
    //     'icon' => 'fas fa-history',
    //     'text' => 'View Stock Ledger'
    //   ],

    //   'refresh' => [
    //     'id'   => 'btnRefreshInventory',
    //     'icon' => 'fas fa-sync-alt',
    //     'text' => 'Refresh'
    //   ]
    // ];

    // $this->data['inventoryInquiry'] = $this->Inventory_model->getAll($filters);
    // $this->data['recordCount'] = count($this->data['inventoryInquiry']);
    // $this->data['searchPlaceHolder'] = 'Search Barcode, Descr, Supplier...';

    $this->data['tableContent'] = $this->load->view(
      'stock_transfers/table',
      $this->data,
      TRUE
    );

    $this->render('stock_transfers/index');
  }

  public function create()
  {
    $this->setPage('New Stock Transfer');
    // $this->pageScript = 'goods_receipts';

    $this->data['inventoryInquiry'] = [];
    $this->render('stock_transfers/create');
  }
}