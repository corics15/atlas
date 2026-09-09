<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Dashboard_model');
  }

  public function index()
  {
    $this->data['activeCustomerCount'] = $this->Dashboard_model->getActiveCustomerCount();
    $this->data['activeProductCount'] = $this->Dashboard_model->getActiveProductCount();
    $inventorySummary = $this->Dashboard_model->getInventorySummary();
    $this->data['totalInventoryQty'] = $inventorySummary['totalQty'];
    $this->data['totalInventoryAmount'] = $inventorySummary['totalAmount'];

    $salesTodayFilter = $this->encodeFilter([
      'date_from' => date('Y-m-d'),
      'date_to'   => date('Y-m-d')
    ]);
    $salesThisMonthFilter = $this->encodeFilter([
      'date_from' => date('Y-m-01'),
      'date_to'   => date('Y-m-t')
    ]);
    $this->data['salesTodayUrl'] = base_url('sales-invoices/?filter=' . urlencode($salesTodayFilter));
    $this->data['salesThisMonthUrl'] = base_url('sales-invoices/?filter=' . urlencode($salesThisMonthFilter));
    $this->data['salesToday'] = $this->Dashboard_model->getSalesToday();
    $this->data['salesThisMonth'] = $this->Dashboard_model->getSalesThisMonth();

    $openSalesOrdersFilter = $this->encodeFilter([
      'date_from' => date('Y-m-01'),
      'date_to'   => date('Y-m-t'),
      'status'    => 'OPEN',
      'keyword'   => ''
    ]);
    $this->data['openSalesOrdersUrl'] = base_url('sales-orders?filter=' . urlencode($openSalesOrdersFilter));
    $this->data['openSalesOrderCount'] = $this->Dashboard_model->getOpenSalesOrderCount();

    $openPurchaseOrdersFilter = $this->encodeFilter([
      'date_from' => date('Y-m-01'),
      'date_to'   => date('Y-m-t'),
      'status'    => 'OPEN',
      'keyword'   => '',
      'supplier_id' => ''
    ]);
    $this->data['openPurchaseOrdersUrl'] = base_url('purchase-orders/list?filter=' . urlencode($openPurchaseOrdersFilter));
    $this->data['openPurchaseOrderCount'] = $this->Dashboard_model->getOpenPurchaseOrderCount();

    $this->data['outOfStockCount'] = $this->Dashboard_model->getOutOfStockCount();

    $this->data['recentSales'] = $this->Dashboard_model->getRecentSales(5);
    foreach ($this->data['recentSales'] as $sale) {
      $sale->url = base_url('sales-invoices/edit/' . $this->encodeId($sale->id));
    }

    $this->data['salesTrend'] = $this->Dashboard_model->getSalesTrend(6);
    $this->data['topSellingProducts'] = $this->Dashboard_model->getTopSellingProducts(5);

    $this->pageScript = 'dashboard';

    $this->render('dashboard/index');
  }
}