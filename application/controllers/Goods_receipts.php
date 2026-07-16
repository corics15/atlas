<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods_receipts extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Purchase_order_model');
    $this->load->model('Goods_receipt_model');
  }

  public function index()
  {
    $poId = (int) $this->input->get('po');

    $this->data['poId'] = $poId;
    $this->data['purchaseOrder'] = null;

    if ($poId > 0) {
      $this->data['purchaseOrder'] = $this->Purchase_order_model->get($poId);
    }

    $this->setPage('Goods Receiving');

    $this->pageScript = 'goods_receipts';

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
}