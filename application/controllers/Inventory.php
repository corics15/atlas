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

  public function inquiry($productId)
  {
    $this->setPage('Inventory Inquiry');

    $this->pageScript = 'inventory';

    $this->data['product'] = $this->Product_model->get($productId);

    if (!$this->data['product']) {
      show_404();
    }

    $this->data['ledger'] = $this->Inventory_model->getStockLedger($productId);

    $this->data['tableContent'] = $this->load->view(
      'inventory/table',
      $this->data,
      TRUE
    );

    $this->render('inventory/inquiry');
  }
}