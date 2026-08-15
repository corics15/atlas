<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_returns extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();

    $this->load->model('Goods_receipt_model');
    $this->load->model('Supplier_model');
    $this->load->model('Term_model');
    $this->load->model('Inventory_model');
    $this->load->model('Purchase_return_model');
  }

  public function index()
  {
    $this->setPage('Purchase Returns');
    $this->pageScript = 'purchase_returns';

    /*** filters */
    $this->data['statuses'] = [
      'OPEN',
      'POSTED',
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
    $this->data['searchPlaceHolder'] = 'Search...';
    /*** end filters */

    $this->data['purchaseReturns'] = $this->Purchase_return_model->getAll($filters);
    foreach ($this->data['purchaseReturns'] as $pr) {
      $pr->gr_url = base_url('goods-receipts/view/' . $this->encodeId($pr->goods_receipt_id));
      $pr->url = base_url('purchase-returns/edit/' . $this->encodeId($pr->id));
    }

    $this->data['recordCount'] = count($this->data['purchaseReturns']);

    $this->data['toolbar'] = [
        'edit' => [
            'id'   => 'btnEditPurchaseReturn',
            'text' => 'Edit',
            'icon' => 'fas fa-edit'
        ],
        'post' => [
            'id'   => 'btnPostPurchaseReturn',
            'text' => 'Post',
            'icon' => 'fas fa-check-circle'
        ],
        'print' => [
            'id'   => 'btnPrintPurchaseReturn',
            'text' => 'Print',
            'icon' => 'fas fa-print'
        ],
        'cancel' => [
            'id'   => 'btnCancelPurchaseReturn',
            'text' => 'Cancel',
            'icon' => 'fas fa-ban'
        ],
        'refresh' => [
            'id'   => 'btnRefreshPurchaseReturn',
            'text' => 'Refresh',
            'icon' => 'fas fa-sync'
        ]
    ];

    $this->data['tableContent'] =
        $this->load->view(
            'purchase_returns/table',
            $this->data,
            TRUE
        );

    $this->render('purchase_returns/index');
  }

  public function create($goodsReceiptId = null)
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $decodedId = $this->decodeId($goodsReceiptId);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $goodsReceiptId = (int) $id;

    if (!$goodsReceiptId) {
      show_404();
    }

    $goodsReceipt = $this->Purchase_return_model->getGoodsReceipt($goodsReceiptId);

    if (!$goodsReceipt) {
      show_404();
    }

    if ($goodsReceipt->status !== 'POSTED') {
      $this->data['error_message'] = 'Only <span class="font-weight-500">POSTED</span> Goods Receipts can be used to create a Purchase Return.';
    }

    $this->setPage('New Purchase Return');
    $this->pageScript = 'purchase_returns';

    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();

    $this->data['goodsReceipt'] = $goodsReceipt;
    $this->data['details'] = $this->Purchase_return_model->getGoodsReceiptDetails($goodsReceiptId);
    $urlLink = $this->encodeId($this->data['goodsReceipt']->id);
    $this->data['goodsReceipt']->url = base_url('goods-receipts/view/'.$urlLink);

    $this->render('purchase_returns/create');
  }

  public function edit($purchaseReturnId)
  {
    $decodedId = $this->decodeId($purchaseReturnId);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $id = (int) $id;

    $this->setPage('Edit Purchase Return');
    $this->pageScript = 'purchase_returns';

    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['terms'] = $this->Term_model->getDropdown();

    $this->data['purchaseReturn'] = $this->Purchase_return_model->get($id);

    if (!$this->data['purchaseReturn']) {
      show_404();
    }

    $this->data['goodsReceipt'] = $this->Purchase_return_model->getGoodsReceipt(
      $this->data['purchaseReturn']->goods_receipt_id
    );
    $this->data['goodsReceipt']->url = base_url('goods-receipts/view/' . $this->encodeId($this->data['purchaseReturn']->goods_receipt_id));

    $this->data['details'] = $this->Purchase_return_model->getDetails($id);

    $this->data['isEditable'] = in_array(
      $this->session->userdata('access_level'),
      ['ADMIN', 'MANAGER', 'STAFF'],
      TRUE
    );

    $this->render('purchase_returns/create');
  }

  public function save()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $purchaseReturn = json_decode($this->input->raw_input_stream);
    $result = $this->Purchase_return_model->save($purchaseReturn);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function post()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $request = $this->getJsonRequest();
    $result = $this->Purchase_return_model->post($request['ids']);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function cancel()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $ids = $this->getJsonRequest('ids');
    $cancelReason = $this->getJsonRequest('cancel_reason');
    $result = $this->Purchase_return_model->cancel($ids, $cancelReason);

    return $this->jsonResponse(
      $result['success'],
      $result['message'],
      $result['data']
    );
  }

  public function print()
  {
    $ids = $this->input->post('ids');

    if (!$ids) {
      show_404();
    }

    $documents = [];

    foreach ($ids as $id) {
      $header = $this->Purchase_return_model->get($id);

      if (!$header) {
        continue;
      }

      $documents[] = (object)[
        'header'  => $header,
        'details' => $this->Purchase_return_model->getDetails($id)
      ];
    }

    $this->load->view(
      'purchase_returns/print',
      [
        'documents' => $documents,
        'title'     => 'PURCHASE RETURN'
      ]
    );
  }

}