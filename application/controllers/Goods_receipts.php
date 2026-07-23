<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods_receipts extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Purchase_order_model');
    $this->load->model('Goods_receipt_model');
    $this->load->model('Inventory_model');
  }

  public function index()
  {
    $this->setPage('Goods Receipt List');

    $filters = [
      'date_from' => trim($this->input->get('date_from')),
      'date_to' => trim($this->input->get('date_to')),
      'keyword' => trim($this->input->get('keyword')),
    ];

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $keyword = trim($this->input->get('keyword'));
    $this->data['keyword'] = $keyword;

    $this->pageScript = 'goods_receipts';
    $this->data['goodsReceipts'] = $this->Goods_receipt_model->getAll($filters);
    $this->data['recordCount'] = count($this->data['goodsReceipts']);
    $this->data['searchPlaceHolder'] = 'Search GRN, PO, Supplier...';

    $this->data['toolbar'] = [
      'print' => [
        'id'   => 'btnPrintGoodsReceipt',
        'icon' => 'fas fa-print',
        'text' => 'Print'
      ],

      'refresh' => [
        'id'   => 'btnRefreshGoodsReceipt',
        'icon' => 'fas fa-sync-alt',
        'text' => 'Refresh'
      ]
    ];

    $this->data['tableContent'] = $this->load->view(
      'goods_receipts/table',
      $this->data,
      TRUE
    );

    $this->render('goods_receipts/index');
  }

  public function save()
  {
    $postData = $this->input->post();
    $data = [
      'grn_date'    => $postData['grn_date'],
      'po_id'       => $postData['po_id'],
      'supplier_id' => $postData['supplier_id'],
      'remarks'     => trim($postData['remarks']) <> '' ? strtoupper(trim($postData['remarks'])) : NULL,
      'details'     => json_decode($postData['details']),
    ];

    $result = $this->Goods_receipt_model->save($data);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function update()
  {
    $request = $this->getJsonRequest();

    $request['updated_by'] = $this->session->userdata('user_id');

    $result = $this->Goods_receipt_model->update($request);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function cancel()
  {
    $request = $this->getJsonRequest();

    $result = $this->Goods_receipt_model->cancel($request);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function post()
  {
    $request = $this->getJsonRequest();

    $result = $this->Goods_receipt_model->post($request);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function create()
  {
    $poId = (int) $this->input->get('po');

    if ($poId <= 0) {
      show_404();
    }

    /*** check if a DRAFT GRN already exists for this PO */
    $draft = $this->Goods_receipt_model->getDraftByPurchaseOrder($poId);

    if ($draft) {
      redirect('goods_receipts/view/' . $draft['id']);
      return;
    }

    $this->data['poId'] = $poId;
    $this->data['purchaseOrder'] = $this->Purchase_order_model->get($poId);

    if (!$this->data['purchaseOrder']) {
      show_404();
    }

    $this->setPage('Receive Goods');
    $this->pageScript = 'goods_receipts';
    $this->render('goods_receipts/create');
  }

  public function view($id = 0)
  {
    $goodsReceipt = $this->Goods_receipt_model->get($id);

    if (!$goodsReceipt)
        show_404();

    $this->data['goodsReceipt'] = $goodsReceipt;
    $this->data['details'] = $this->Goods_receipt_model->getDetails($id);

    $this->data['isEditable'] = false;

    $this->setPage('Goods Receipt');
    $this->pageScript = 'goods_receipts';
    $this->render('goods_receipts/view');
  }

  public function print()
  {
    $ids = $this->input->post('ids');

    if (empty($ids)) {
      show_error('No Goods Receipt selected.');
    }

    $documents = [];

    foreach ($ids as $id) {
      $documents[] = (object) [
        'header'  => $this->Goods_receipt_model->get($id),
        'details' => $this->Goods_receipt_model->getDetails($id)
      ];
    }

    $this->data['documents'] = $documents;

    $this->load->view(
      'goods_receipts/print',
      $this->data
    );
  }
}