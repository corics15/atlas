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
    $this->load->model('Product_uom_model');
  }

  public function index()
  {
    $this->setPage('Goods Receiving List');

    /*** filters */
    $this->data['statuses'] = [
      'DRAFT',
      'POSTED',
      'CANCELLED',
    ];
    $filter = $this->decodeFilter($this->input->get('filter'));
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

    $this->pageScript = 'goods_receipts';
    $this->data['goodsReceipts'] = $this->Goods_receipt_model->getAll($filters);
    $itemCount = 0;
    $totalAmount = 0;
    foreach ($this->data['goodsReceipts'] as $gr) {
      $gr->url = base_url('goods-receipts/view/' . $this->encodeId($gr->id));
      $gr->po_url = base_url('purchase-orders?id=' . $this->encodeId($gr->po_id));
      $itemCount += $gr->item_count;
      $totalAmount += $gr->total_amount;
    }

    $this->data['recordCount'] = count($this->data['goodsReceipts']);
    $this->data['itemCount'] = $itemCount;
    $this->data['totalAmount'] = $totalAmount;
    $this->data['searchPlaceHolder'] = 'Search GRN, PO, Supplier...';

    $this->data['toolbar'] = [
      'print' => [
        'id'   => 'btnPrintGoodsReceipt',
        'icon' => 'fas fa-print',
        'text' => 'Print GRN'
      ],
      'create' => [
        'id'   => 'btnCreatePurchaseReturn',
        'text' => 'Create Purchase Return',
        'icon' => 'fas fa-exchange-alt'
      ],
      'refresh' => [
        'id'   => 'btnRefreshGoodsReceipt',
        'icon' => 'fas fa-sync-alt',
        'text' => 'Refresh',
        'url'  => 'goods-receipts',
      ]
    ];

    $this->data['tableContent'] = $this->load->view(
      'goods_receipts/table',
      $this->data,
      TRUE
    );

    $this->data['isEditable'] = in_array(
      $this->session->userdata('access_level'),
      ['ADMIN', 'MANAGER', 'STAFF'],
      TRUE
    );

    $this->render('goods_receipts/index');
  }

  public function save()
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

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
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

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
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

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
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

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
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    $decodedId = $this->decodeId($this->input->get('po'));
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $poId = (int) $id;

    if ($poId <= 0) {
      show_404();
    }

    /*** check if a DRAFT GRN already exists for this PO */
    $draft = $this->Goods_receipt_model->getDraftByPurchaseOrder($poId);

    if ($draft) {
      redirect('goods-receipts/view/' . $draft['id']);
      return;
    }

    $this->data['poId'] = $poId;
    $this->data['purchaseOrder'] = $this->Purchase_order_model->get($poId);

    foreach ($this->data['purchaseOrder']['details'] as $detail) {
      /*** same as product base UOM */
      if ((int)$detail->uom_id === (int)$detail->base_uom_id) {
        $detail->conversion_factor = 1;
        continue;
      }
      /*** check known product/UOM conversion */
      $productUom = $this->Product_uom_model->get(
        $detail->product_id,
        $detail->uom_id
      );
      $detail->conversion_factor = $productUom
        ? (float)$productUom->conversion_factor
        : NULL;
    }

    $this->data['error_message'] = NULL;

    if (!$this->data['purchaseOrder']) {
      show_404();
    }

    if (!in_array($this->data['purchaseOrder']['header']->status, ['OPEN', 'PARTIAL'])) {
      $this->data['error_message'] = 'Only OPEN or PARTIAL Purchase Orders can receive goods.';
    }

    $this->setPage('Receive Goods');
    $this->pageScript = 'goods_receipts';
    $this->render('goods_receipts/create');
  }

  public function view($id = 0)
  {
    $decodedId = $this->decodeId($id);
    if ($decodedId !== NULL) {
      $id = $decodedId;
    }
    if (!ctype_digit((string) $id) || (int) $id <= 0) {
      show_404();
    }
    $goodsReceiptId = (int) $id;

    $goodsReceipt = $this->Goods_receipt_model->get($goodsReceiptId);
    if (!$goodsReceipt)
        show_404();

    $goodsReceipt->url = base_url('purchase-orders?id=' . $this->encodeId($goodsReceipt->po_id));

    $this->data['goodsReceipt'] = $goodsReceipt;
    $this->data['details'] = $this->Goods_receipt_model->getDetails($goodsReceiptId);

    $this->data['isEditable'] = in_array(
      $this->session->userdata('access_level'),
      ['ADMIN', 'MANAGER', 'STAFF'],
      TRUE
    );

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