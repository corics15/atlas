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

    $this->pageScript = 'goods_receipts';
    $this->data['goodsReceipts'] = $this->Goods_receipt_model->getAll();

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

  public function create()
  {
    $poId = (int) $this->input->get('po');

    $this->data['poId'] = $poId;
    $this->data['purchaseOrder'] = null;

    if ($poId <= 0) {
      show_404();
    }

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
    $this->setPage('View Goods Receipt');
    $this->render('goods_receipts/view');
  }
}