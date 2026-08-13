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
    $this->setPage('Inventory Adjustment List',
      [
        'id'   => 'btnNewInventoryAdjustment',
        'icon' => 'fa fa-plus',
        'text' => 'New Adjustment',
      ]
    );
    $this->pageScript = 'inventory_adjustments';

    /*** filters */
    $this->data['statuses'] = [
      'DRAFT',
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

    $this->data['inventoryAdjustments'] = $this->Inventory_adjustment_model->getAll($filters);
    foreach ($this->data['inventoryAdjustments'] as $ia) {
      $ia->url = base_url('inventory-adjustments/view/' . $this->encodeId($ia->id));
    }

    $this->data['recordCount'] = count($this->data['inventoryAdjustments']);
    $this->data['tableContent'] = $this->load->view(
        'inventory_adjustments/table',
        $this->data,
        TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditInventoryAdjustment',
        'icon' => 'fas fa-edit',
        'text' => 'Edit Adjustment'
      ],
      'cancel' => [
        'id'   => 'btnCancelInventoryAdjustment',
        'icon' => 'fas fa-ban',
        'text' => 'Cancel Adjustment'
      ],
      'post' => [
        'id'   => 'btnPostInventoryAdjustment',
        'icon' => 'fas fa-check-circle',
        'text' => 'Post Adjustment'
      ],
      'print' => [
        'id'   => 'btnPrintInventoryAdjustment',
        'icon' => 'fas fa-print',
        'text' => 'Print'
      ],
      'refresh' => [
        'id'   => 'btnRefreshInventoryAdjustment',
        'icon' => 'fas fa-sync-alt',
        'text' => 'Refresh'
      ]
    ];

    $this->render('inventory_adjustments/index');
  }

  public function view($id = null)
  {
    if (empty($id)) {
      show_404();
    }

    $decodedId = $this->decodeId($id);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $inventoryAdjustmentId = (int) $id;

    $inventoryAdjustment = $this->Inventory_adjustment_model->get($inventoryAdjustmentId);

    if (!$inventoryAdjustment) {
      show_404();
    }

    $this->data['inventoryAdjustment'] = $inventoryAdjustment;
    $this->data['inventoryAdjustmentDetails'] = $this->Inventory_adjustment_model->getDetails($inventoryAdjustmentId);

    $this->setPage('Inventory Adjustment');
    $this->pageScript = 'inventory_adjustments';
    $this->data['isEditable'] = ($inventoryAdjustment->status === 'DRAFT');

    $this->render(
      'inventory_adjustments/create',
      $this->data,
      TRUE
    );
  }

  public function get($id)
  {
    return $this->db
        ->where('id', $id)
        ->get('t_inventory_adjustments')
        ->row();
  }

  public function create()
  {
    $this->setPage('New Inventory Adjustment');
    $this->pageScript = 'inventory_adjustments';
    $this->data['isEditable'] = true;

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

    if (empty($request['adjustment_id'])) {
      $request['entered_by'] = $this->session->userdata('user_id');
    } else {
      $request['updated_by'] = $this->session->userdata('user_id');
    }

    $result = $this->Inventory_adjustment_model->save($request);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function post()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $adjustmentId = (int) $this->getJsonRequest('adjustment_id');
    $postedBy     = (int) $this->session->userdata('user_id');

    $result = $this->Inventory_adjustment_model->post(
      $adjustmentId,
      $postedBy
    );

    $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function cancel()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $adjustmentId = (int) $this->getJsonRequest('adjustment_id');
    $cancelReason = $this->getJsonRequest('cancel_reason');
    $cancelledBy  = (int) $this->session->userdata('user_id');

    $result = $this->Inventory_adjustment_model->cancel(
      $adjustmentId,
      $cancelledBy,
      $cancelReason
    );

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function print($adjustmentId = null)
  {
    if (empty($adjustmentId)) {
      show_404();
    }

    $inventoryAdjustment = $this->Inventory_adjustment_model->get($adjustmentId);

    if (!$inventoryAdjustment) {
      show_404();
    }

    $this->data['inventoryAdjustment'] = $inventoryAdjustment;
    $this->data['details'] = $this->Inventory_adjustment_model ->getDetails($adjustmentId);

    $this->load->view(
      'inventory_adjustments/print',
      $this->data
    );
  }

}