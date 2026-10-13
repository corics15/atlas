<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('Product_model');
    $this->load->model('Product_uom_model');
    $this->load->model('Supplier_model');
    $this->load->model('Uom_model');

    $this->load->library('form_validation');
  }

  public function index()
  {
    $this->setPage(
      'Products',
      [
        'id'   => 'btnNewProduct',
        'icon' => 'fas fa-plus',
        'text' => 'New Product',
      ]
    );

    $this->pageScript = 'products';
    $filter = $this->decodeFilter($this->input->get('filter'));
    $filters = [
      'keyword' => trim($filter['keyword'] ?? $this->input->get('keyword')),
    ];
    $this->data = array_merge(
      $this->data,
      $filters
    );
    $this->data['products'] = $this->Product_model->getAll($filters['keyword']);
    $this->data['recordCount'] = count($this->data['products']);

    $this->data['tableContent'] = $this->load->view(
        'products/table',
        $this->data,
        TRUE
    );

    $this->data['toolbar'] = [
      'edit' => [
        'id' => 'btnEditProduct',
        'text' => 'Edit',
        'icon' => 'fas fa-edit'
      ],
      'activate' => [
        'id' => 'btnActivateProduct',
        'text' => 'Activate',
        'icon' => 'fas fa-check-circle'
      ],
      'deactivate' => [
        'id' => 'btnDeactivateProduct',
        'text' => 'Deactivate',
        'icon' => 'fas fa-ban'
      ],
      'inventoryInquiry' => [
        'id'   => 'btnInventoryInquiry',
        'icon' => 'fas fa-history',
        'text' => 'Inventory Inquiry'
      ],
      'excel' => [
        'id'   => 'btnDownloadProductExcel',
        'icon' => 'fas fa-file-excel',
        'text' => 'Download as Excel'
      ],
      'refresh' => [
        'id' => 'btnRefreshProduct',
        'text' => 'Refresh',
        'icon' => 'fas fa-sync',
        'url'  => 'products',
      ]
    ];

    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['uoms'] = $this->Uom_model->getDropdown();
    $this->data['searchPlaceHolder'] = 'Search products, barcode, packing...';

    $this->render('products/index');
  }

  public function get($id)
  {
      $product = $this->Product_model->get($id);

      if (!$product) {
          return $this->jsonResponse(
            false,
            'Product not found.'
          );
      }

      return $this->jsonResponse(
          true,
          '',
          $product
      );
  }

  /*
   * BASE UOM INTEGRITY
   * Read-only inspection. No repair or database changes.
   */
  public function inspectBaseUom($id)
  {
    $id = (int)$id;

    if ($id <= 0) {
      return $this->jsonResponse(
        false,
        'Invalid Product ID.'
      );
    }

    $inspection = $this->Product_model->inspectBaseUom($id);

    if ($inspection['status'] === 'NOT_FOUND') {
      return $this->jsonResponse(
        false,
        $inspection['message']
      );
    }

    return $this->jsonResponse(
      true,
      $inspection['message'],
      $inspection
    );
  }

  /*
   * BASE UOM INTEGRITY
   * Explicit repair only. Never invoked by normal Product Save.
   */
  public function repairBaseUom($id)
  {
    $this->requireAccess([
      'ADMIN',
      'MANAGER',
      'STAFF'
    ]);

    if (strtoupper($this->input->method()) !== 'POST') {
      return $this->jsonResponse(FALSE, 'POST request required.');
    }

    $id = (int)$id;
    if ($id <= 0) {
      return $this->jsonResponse(FALSE, 'Invalid Product ID.');
    }

    $confirmed = $this->input->post('confirmed');
    if ($confirmed !== '1') {
      return $this->jsonResponse(FALSE, 'Explicit repair confirmation is required.');
    }

    $inspection = $this->Product_model->inspectBaseUom($id);
    if (empty($inspection['can_repair'])) {
      return $this->jsonResponse(FALSE, $inspection['message']);
    }

    $sales = $this->input->post('is_sales_uom');
    $purchase = $this->input->post('is_purchase_uom');

    if ($inspection['status'] === 'MISSING') {
      if (!in_array($sales, ['0', '1'], TRUE) ||
          !in_array($purchase, ['0', '1'], TRUE)) {
        return $this->jsonResponse(FALSE, 'Explicit Sales and Purchasing eligibility choices are required.');
      }
    }

    $result = $this->Product_model->repairBaseUom(
      $id,
      $sales === '1' ? TRUE : ($sales === '0' ? FALSE : NULL),
      $purchase === '1' ? TRUE : ($purchase === '0' ? FALSE : NULL)
    );

    return $this->jsonResponse(
      $result['success'],
      $result['message']
    );
  }

  public function getUoms($id)
  {
    $product = $this->Product_model->get($id);

    if (!$product) {
      return $this->jsonResponse(
        false,
        'Product not found.'
      );
    }

    $uoms = $this->Product_uom_model->getByProduct($id);

    return $this->jsonResponse(
      true,
      '',
      $uoms
    );
  }

  public function saveUom()
  {
    $this->requireAccess(['ADMIN', 'MANAGER', 'STAFF']);

    $request = $this->getJsonRequest();

    $productId = (int)($request['product_id'] ?? 0);
    $uomId = (int)($request['uom_id'] ?? 0);
    $conversionFactor = (float)($request['conversion_factor'] ?? 0);
    $lastCost = (float)($request['last_cost'] ?? 0);
    $sellingPrice = (float)($request['selling_price'] ?? 0);
    $isSalesUom = !empty($request['is_sales_uom']);
    $isPurchaseUom = !empty($request['is_purchase_uom']);

    $product = $this->Product_model->get($productId);

    if (!$product) {
      return $this->jsonResponse(
        false,
        'Product not found.'
      );
    }

    if ($uomId <= 0) {
      return $this->jsonResponse(
        false,
        'Please select a UOM.'
      );
    }

    if ($uomId === (int)$product->uom_id) {
      return $this->jsonResponse(
        false,
        'The Base UOM cannot be added as an Additional UOM.'
      );
    }

    if ($conversionFactor <= 0) {
      return $this->jsonResponse(
        false,
        'Units in Base UOM must be greater than zero.'
      );
    }

    if ($lastCost < 0) {
      return $this->jsonResponse(
        false,
        'Cost cannot be negative.'
      );
    }

    if ($sellingPrice < 0) {
      return $this->jsonResponse(
        false,
        'SRP cannot be negative.'
      );
    }

    $saved = $this->Product_uom_model->save(
      $productId,
      $uomId,
      $conversionFactor,
      $sellingPrice,
      $lastCost,
      $isSalesUom,
      $isPurchaseUom
    );

    if (!$saved) {
      return $this->jsonResponse(
        false,
        'Unable to save the Additional UOM.'
      );
    }

    return $this->jsonResponse(
      true,
      'Additional UOM saved successfully.'
    );
  }

  public function save()
  {
    $postData = $this->input->post();
    $id = (int) $postData['id'];

    $product = $id ? $this->Product_model->get($id) : NULL;

    $barcode = trim($postData['barcode']);
    if ($barcode !== '' && $this->Product_model->barcodeInUse($barcode, $id)) {
      return $this->jsonResponse(
        false,
        'Item Barcode is already being used by another product or UOM.'
      );
    }

    $this->form_validation->set_rules(
      'supplier_id',
      'Supplier',
      'required|trim',
      [
        'required' => 'The %s field is mandatory.'
      ]
    );

    if (empty($id)) {
      $this->form_validation->set_rules(
        'uom_id',
        'UOM',
        'required|trim',
        [
          'required' => 'The %s field is mandatory.'
        ]
      );
    }

    $this->form_validation->set_rules(
      'description',
      'Product Name',
      'required|trim',
      [
        'required' => 'The %s field is mandatory.'
      ]
    );

    if (!$this->form_validation->run()) {
      return $this->validationResponse();
    }

    $data = [
      'supplier_id' => trim($postData['supplier_id']),
      'barcode' => $barcode !== '' ? $barcode : NULL,
      'case_barcode' => trim($postData['case_barcode']),
      'description' => trim($postData['description']) <> '' ? strtoupper(trim($postData['description'])) : NULL,
      'uom_id' => empty($id) ? $postData['uom_id'] : $product->uom_id,
      'cost' => $postData['cost'],
      'srp' => $postData['srp'],
      'pkg' => trim($postData['pkg']) <> '' ? strtoupper(trim($postData['pkg'])) : NULL,
    ];

    if (empty($id)) {
      $data['entered_by'] = $this->session->userdata('user_id');
      $data['entered_on'] = date('Y-m-d H:i:s');
    } else {
      $data['updated_by'] = $this->session->userdata('user_id');
      $data['updated_on'] = date('Y-m-d H:i:s');
    }

    if (!empty($id)) {
      $baseUom = $this->db
        ->where('product_id', $id)
        ->where('uom_id', (int)$product->uom_id)
        ->get('m_product_uom')
        ->result();

      $validBaseUom = count($baseUom) === 1
        && in_array($baseUom[0]->is_active, [TRUE, 't'], TRUE)
        && (float)$baseUom[0]->conversion_factor == 1;

      if (!$validBaseUom) {
        return $this->jsonResponse(
          false,
          'This product has an invalid base UOM configuration. Changes were not saved. Please verify the product UOM settings.'
        );
      }
    }

    $isSalesUom = (int)($postData['is_sales_uom'] ?? 0) === 1;
    $isPurchaseUom = (int)($postData['is_purchase_uom'] ?? 0) === 1;

    $saved = $this->Product_model->save(
      $data,
      $id,
      $isSalesUom,
      $isPurchaseUom
    );

    if (!$saved) {
      return $this->jsonResponse(
        false,
        'Unable to save the product. Please try again.'
      );
    }

    return $this->jsonResponse(
      true,
      empty($id)
          ? 'Product saved successfully.'
          : 'Product updated successfully.'
    );
  }

  public function activate($id)
  {
    if (!$this->Product_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Product not found.'
      );
    }

    $this->Product_model->activate($id);

    return $this->jsonResponse(
      true,
      'Product activated successfully.'
    );
  }

  public function deactivate($id)
  {
    if (!$this->Product_model->get($id)) {
      return $this->jsonResponse(
        false,
        'Product not found.'
      );
    }

    $this->Product_model->deactivate($id);

    return $this->jsonResponse(
      true,
      'Product deactivated successfully.'
    );
  }

  public function deactivateUom($id)
  {
    $this->requireAccess(['ADMIN', 'MANAGER', 'STAFF']);

    $id = (int)$id;

    if ($id <= 0) {
      return $this->jsonResponse(
        false,
        'Invalid Product UOM.'
      );
    }

    if (!$this->Product_uom_model->deactivate($id)) {
      $uom = $this->db
        ->select('pu.uom_id, p.uom_id AS base_uom_id')
        ->from('m_product_uom pu')
        ->join('m_products p', 'p.id = pu.product_id')
        ->where('pu.id', $id)
        ->get()
        ->row();

      $message = $uom && (int)$uom->uom_id === (int)$uom->base_uom_id
        ? 'This UOM cannot be deactivated because it is the product\'s assigned Base UOM.'
        : 'Unable to deactivate the Product UOM. Please try again.';

      return $this->jsonResponse(false, $message);
    }

    return $this->jsonResponse(
      true,
      'Product UOM deactivated successfully.'
    );
  }

  public function generateBarcode()
  {
    $barcode = $this->Product_model->generateBarcode();

    if (!$barcode) {
      return $this->jsonResponse(
        false,
        'Unable to generate barcode.'
      );
    }

    return $this->jsonResponse(
      true,
      'Barcode generated successfully.',
      [
        'barcode' => $barcode
      ]
    );
  }

}