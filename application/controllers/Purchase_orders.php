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
    $decodedId = $this->decodeId($this->input->get('id'));
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $id = (int) $id;

    $this->data['purchaseOrderId'] = $id;
    $mode = $id ? 'Edit' : 'New';
    $this->setPage($mode.' Purchase Order');
    $this->pageScript = 'purchase_orders';

    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['products'] = $this->Product_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();

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

    $this->pageScript = 'purchase_order_list';

    /*** filters */
    $this->data['statuses'] = [
      'OPEN',
      'PARTIAL',
      'COMPLETED',
      'CANCELLED',
      'CLOSED',
    ];

    $filter = $this->decodeFilter($this->input->get('filter'));
    $keyword = trim($filter['keyword'] ?? $this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $filters = [
      'date_from' => trim($filter['date_from'] ?? $this->input->get('date_from')),
      'date_to' => trim($filter['date_to'] ?? $this->input->get('date_to')),
      'supplier_id' => trim($filter['supplier_id'] ?? $this->input->get('supplier_id')),
      'status' => trim($filter['status'] ?? $this->input->get('status')),
      'keyword' => $keyword,
    ];

    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data = array_merge(
      $this->data,
      $filters
    );
    /*** end filters */

    $this->data['purchaseOrders'] = $this->Purchase_order_model->getAll($filters);
    foreach ($this->data['purchaseOrders'] as $po) {
      $po->url = base_url('purchase-orders?id=' . $this->encodeId($po->id));
    }

    $this->data['recordCount'] = count($this->data['purchaseOrders']);
    $this->data['tableContent'] = $this->load->view(
      'purchase_orders/table',
      $this->data,
      TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
        'id'   => 'btnEditPurchaseOrder',
        'text' => 'Edit PO',
        'icon' => 'fas fa-edit'
      ],
      'receive' => [
        'id'   => 'btnReceiveGoods',
        'icon' => 'fas fa-dolly',
        'text' => 'Receive Goods'
      ],
      'print' => [
        'id'   => 'btnPrintPurchaseOrder',
        'text' => 'Print PO',
        'icon' => 'fas fa-print'
      ],
      'cancel' => [
        'id'   => 'btnCancelPurchaseOrder',
        'text' => 'Cancel PO',
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
    $cancelReason = $this->input->post('cancel_reason');

    if (!is_array($ids) || empty($ids)) {
      return $this->jsonResponse(
        false,
        'Please select at least one Purchase Order.'
      );
    }

    $result = $this->Purchase_order_model->cancelMany($ids, $cancelReason);

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