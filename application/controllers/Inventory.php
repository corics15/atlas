<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Product_model');
    $this->load->model('Inventory_model');
    $this->load->model('Branch_model');
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
    foreach ($this->data['inventoryInquiry'] as $iq) {
      $iq->url = base_url('inventory/ledger/' . $this->encodeId($iq->product_id));
    }

    $this->data['recordCount'] = count($this->data['inventoryInquiry']);
    $this->data['searchPlaceHolder'] = 'Search Barcode, Descr, Supplier...';

    /*** inventory summary */
    $totalQty = 0;
    $totalAmount = 0;

    foreach ($this->data['inventoryInquiry'] as $row) {
      $totalQty += (float) $row->qty_on_hand;
      $totalAmount += (float) $row->inventory_value;
    }

    $this->data['totalQty'] = $totalQty;
    $this->data['totalAmount'] = $totalAmount;
    /*** end inventory summary */

    $this->data['tableContent'] = $this->load->view(
      'inventory/inventory_table',
      $this->data,
      TRUE
    );
    $this->render('inventory/index');
  }

  public function ledger($productId)
  {
    $decodedId = $this->decodeId($productId);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $productId = (int) $id;

    $this->setPage('Stock Ledger');
    $this->pageScript = 'inventory';
    $this->data['product'] = $this->Product_model->get($productId);

    if (!$this->data['product']) {
      show_404();
    }

    $this->data['transaction_types'] = [
      'GRN',
      'ADJUSTMENT',
      'SI',
      'TRANSFER',
      'PURCHASE RETURN',
      'SALES RETURN'
    ];

    $filters = [
      'date_from' => trim($this->input->get('date_from')),
      'date_to' => trim($this->input->get('date_to')),
      'transType' => trim($this->input->get('transType')),
      'branch_id' => $this->input->get('branch_id') ?: $this->session->userdata('branch_id')
    ];
    $this->data = array_merge(
      $this->data,
      $filters
    );
    $this->data['ledger'] = $this->Inventory_model->getStockLedger($productId, $filters);
    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['selectedBranchId'] = $filters['branch_id'];

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
    $branch_id = $this->input->post('branch_id', TRUE);

    $data['product'] = $this->Product_model->get($productId);

    $filters = [
      'date_from' => $fromDate,
      'date_to'   => $toDate,
      'transType' => $transactionType,
      'branch_id' => $branch_id,
    ];

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $data['ledger'] = $this->Inventory_model->getStockLedger(
      $productId,
      $filters
      // $fromDate,
      // $toDate,
      // $transactionType
    );

    $data['title'] = 'Stock Ledger';

    $data['period'] =
        (!empty($fromDate) && !empty($toDate))
            ? date('m/d/Y', strtotime($fromDate))
                .' to '.
              date('m/d/Y', strtotime($toDate))
            : null;

    $this->load->view('inventory/ledger_print', $data);
  }
}