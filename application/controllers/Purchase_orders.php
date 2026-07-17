<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_orders extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Supplier_model');
    $this->load->model('Product_model');
    $this->load->model('Purchase_order_model');
    $this->load->model('Term_model');

    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Purchase Orders',
      [
        'id'   => 'btnNewPurchaseOrder',
        'icon' => 'fas fa-cart-plus',
        'text' => 'New Purchase Order',
      ]
    );

    $this->pageScript = 'purchase_orders';

    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['products'] = $this->Product_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();

    $this->data['purchaseOrderId'] = (int) $this->input->get('id');

    $this->render('purchase_orders/index');
  }

  public function save()
  {
    $payload = json_decode($this->input->raw_input_stream);
    $result = $this->Purchase_order_model->save($payload);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data'] ?? null,
    );
  }

  public function update()
  {
    $payload = json_decode($this->input->raw_input_stream);

    $result = $this->Purchase_order_model->update($payload);

    return $this->jsonResponse(
        $result['success'],
        $result['message'],
        $result['data'] ?? null
    );
  }

  public function get($id)
  {
    $data = $this->Purchase_order_model->get($id);

    return $this->jsonResponse(
      true,
      '',
      $data
    );
  }

  public function list()
  {
    $this->setPage(
      'Purchase Order List',
      [
        'id'   => 'btnNewPurchaseOrder',
        'icon' => 'fa fa-plus',
        'text' => 'New Purchase Order',
      ]
    );

    $this->data['statuses'] = [
      'OPEN',
      'PARTIAL',
      'COMPLETED',
      'CANCELLED',
      'CLOSED',
    ];

    $this->pageScript = 'purchase_order_list';

    $filters = [
      'date_from' => trim($this->input->get('date_from')),
      'date_to' => trim($this->input->get('date_to')),
      'supplier_id' => trim($this->input->get('supplier_id')),
      'status' => trim($this->input->get('status')),
    ];

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $this->data['purchaseOrders'] = $this->Purchase_order_model->getAll($filters);
    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['recordCount'] = count($this->data['purchaseOrders']);
    $this->data['tableContent'] = $this->load->view(
      'purchase_orders/table',
      $this->data,
      TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditPurchaseOrder',
        'text' => 'Edit',
        'icon' => 'fas fa-edit'
      ],
      'receive' => [
        'id'   => 'btnReceiveGoods',
        'icon' => 'fas fa-dolly',
        'text' => 'Receive Goods'
      ],
      'print' => [
        'id'   => 'btnPrintPurchaseOrder',
        'text' => 'Print',
        'icon' => 'fas fa-print'
      ],
      'cancel' => [
        'id'   => 'btnCancelPurchaseOrder',
        'text' => 'Cancel',
        'icon' => 'fas fa-ban'
      ],
      'refresh' => [
        'id'   => 'btnRefreshPurchaseOrder',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync'
      ]
    ];

    $this->render('purchase_orders/list');
  }

  public function cancel()
  {
    $ids = $this->input->post('ids');

    if (!is_array($ids) || empty($ids)) {
      return $this->jsonResponse(
        false,
        'Please select at least one Purchase Order.'
      );
    }

    $result = $this->Purchase_order_model->cancelMany($ids);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
  );
  }

  public function print()
  {
    $ids = $this->input->post('ids');

    if (empty($ids)) {
      show_error('No Purchase Order selected.');
    }

    $this->data['documents'] = $this->Purchase_order_model->getDocument($ids);

    $this->load->view(
      'purchase_orders/print',
      $this->data
    );
  }
}