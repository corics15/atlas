<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory_adjustments extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Inventory_adjustment_model');
  }

  public function index()
  {
    $this->setPage('Inventory Adjustments');
    $this->pageScript = 'inventory_adjustments';
    $this->data['inventoryAdjustments'] = $this->Inventory_adjustment_model->getAll();
    $this->data['tableContent'] = $this->load->view(
        'inventory_adjustments/table',
        $this->data,
        TRUE
    );

    $this->render('inventory_adjustments/index');
  }

  public function view($id = 0)
  {
    $inventoryAdjustment = $this->Inventory_adjustment_model->get($id);

    // if (!$inventoryAdjustment)
    //     show_404();

    $this->data['inventoryAdjustment'] = $inventoryAdjustment;
    $this->setPage('View Inventory Adjustment');

    $this->render(
      'inventory_adjustments/view',
      $this->data,
      TRUE
    );
  }

  public function create()
  {
    $this->setPage('New Inventory Adjustment');
    $this->pageScript = 'inventory_adjustments';

    $this->render(
        'inventory_adjustments/create',
        $this->data,
        TRUE
    );
  }

  public function get_product_stock()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $productId = (int) $this->getJsonRequest('product_id');

    $stock = $this->Inventory_adjustment_model->getProductStock($productId);

    return $this->jsonResponse(
      true,
      '',
      [
        'stock' => $stock
      ]
    );
  }

  public function save()
  {
    $request = $this->getJsonRequest();
    $request['entered_by'] = $this->session->userdata('user_id');

    $result = $this->Inventory_adjustment_model->save($request);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

}