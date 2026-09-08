<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Credit_memos extends MY_Controller
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Credit_memo_model');
  }

  public function view($id)
  {
    $this->requireAccess(['ADMIN', 'MANAGER', 'STAFF', 'VIEWER']);

    $decodedId = $this->decodeId($id);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }

    if (!ctype_digit((string)$id) || (int)$id <= 0) {
      show_404();
    }

    $this->data['creditMemo'] = $this->Credit_memo_model->get((int)$id);
    if (!$this->data['creditMemo']) {
      show_404();
    }

    $creditMemo = $this->data['creditMemo'];
    $creditMemo->si_url = base_url('sales-invoices/edit/' . $this->encodeId($creditMemo->sales_invoice_id));
    $creditMemo->sr_url = base_url('sales-returns/edit/' . $this->encodeId($creditMemo->sales_return_id));

    $this->setPage('Credit Memo');

    $this->render('credit_memos/view');
  }

}