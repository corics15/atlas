<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{

  public function __construct()
  {
    parent::__construct();

    $this->load->model('Reports_model');
    $this->load->model('Branch_model');
    $this->load->model('Supplier_model');
    $this->load->model('Customer_model');
    $this->load->model('Salesman_model');
  }

  public function sales_per_supplier()
  {
    $this->setPage('Sales Per Supplier');
    $this->pageScript = 'reports';

    /*** filters */
    $filter = $this->decodeFilter($this->input->get('filter'));
    $keyword = trim($filter['keyword'] ?? $this->input->get('keyword'));
    $this->data['keyword'] = $keyword;
    $filters = [
      'date_from' => trim($filter['date_from'] ?? $this->input->get('date_from')),
      'date_to' => trim($filter['date_to'] ?? $this->input->get('date_to')),
      'keyword' => trim($filter['keyword'] ?? $this->input->get('keyword')),
      'branch_id' => trim($filter['branch_id'] ?? $this->input->get('branch_id')),
      'supplier_id' => (int)($filter['supplier_id'] ?? $this->input->get('supplier_id')),
    ];

    if ($filters['branch_id'] <= 0) {
      $filters['branch_id'] = (int)$this->session->userdata('branch_id');
    }

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['suppliers'] = $this->Supplier_model->getDropdown();
    $this->data['salesPerSupplier'] = [];
    $this->data['url'] = 'sales-per-supplier';
    $this->data['showActionButton'] = false;

    if ($filters['date_from'] !== '' && $filters['date_to'] !== '') {
      $this->data['salesPerSupplier'] = $this->Reports_model->getSalesPerSupplier($filters);
    }

    $this->render('reports/sales_per_supplier/sales_per_supplier');
  }

  public function sales_per_supplier_products()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $request = $this->getJsonRequest();

    $filters = [
      'date_from'   => trim($request['date_from'] ?? ''),
      'date_to'     => trim($request['date_to'] ?? ''),
      'branch_id'   => (int)($request['branch_id'] ?? 0),
      'supplier_id' => (int)($request['supplier_id'] ?? 0),
    ];

    if ($filters['supplier_id'] <= 0) {
      return $this->jsonResponse(
        FALSE,
        'Invalid supplier.'
      );
    }

    if (
      $filters['date_from'] === '' ||
      $filters['date_to'] === ''
    ) {
      return $this->jsonResponse(
        FALSE,
        'Date From and Date To are required.'
      );
    }

    if ($filters['branch_id'] <= 0) {
      $filters['branch_id'] = (int)$this->session->userdata('branch_id');
    }

    $this->data['salesPerSupplierProducts'] = $this->Reports_model->getSalesPerSupplierProducts($filters);
    $this->data['toolbar'] = [
      'print' => [
        'id'   => 'btnPrintSalesPerSupplier',
        'text' => 'Print',
        'icon' => 'fas fa-print'
      ],
      'excel' => [
        'id'   => 'btnDownloadExcel',
        'icon' => 'fas fa-file-excel',
        'text' => 'Download as Excel'
      ],
    ];

    $html = $this->load->view('reports/sales_per_supplier/sales_per_supplier_products',
      $this->data,
      TRUE
    );

    return $this->jsonResponse(
      TRUE,
      '',
      [
        'html' => $html
      ]
    );
  }

  public function sales_per_supplier_salesman()
  {
    $this->setPage('Sales Per Supplier / Salesman');
    $this->pageScript = 'reports';

    /*** filters */
    $filter = $this->decodeFilter($this->input->get('filter'));
    $filters = [
      'date_from' => trim($filter['date_from'] ?? $this->input->get('date_from')),
      'date_to' => trim($filter['date_to'] ?? $this->input->get('date_to')),
      'branch_id' => (int)($filter['branch_id'] ?? $this->input->get('branch_id')),
      'salesman_id' => (int)($filter['salesman_id'] ?? $this->input->get('salesman_id')),
    ];

    if ($filters['branch_id'] <= 0) {
      $filters['branch_id'] = (int)$this->session->userdata('branch_id');
    }

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['salesmen'] = $this->Salesman_model->getDropdown();
    $this->data['salesPerSupplierSalesman'] = [];
    $this->data['url'] = 'sales-per-supplier-salesman';
    $this->data['showActionButton'] = false;

    if ($filters['date_from'] !== '' && $filters['date_to'] !== '') {
      $this->data['salesPerSupplierSalesman'] = $this->Reports_model->getSalesPerSupplierSalesman($filters);
      $this->data['showActionButton'] = true;
    }

    $this->data['toolbar'] = [
      'print' => [
        'id' => 'btnPrintSalesPerSupplierSalesman',
        'text' => 'Print',
        'icon' => 'fas fa-print'
      ],
      'excel' => [
        'id' => 'btnDownloadSalesPerSupplierSalesmanExcel',
        'text' => 'Download as Excel',
        'icon' => 'fas fa-file-excel'
      ],
    ];

    $this->render('reports/sales_per_supplier/sales_per_supplier_salesman');
  }

  public function print_sales_per_supplier_products()
  {
    $filters = [
      'date_from' => trim($this->input->post('date_from')),
      'date_to' => trim($this->input->post('date_to')),
      'branch_id' => (int)$this->input->post('branch_id'),
      'supplier_id' => (int)$this->input->post('supplier_id'),
    ];

    if (
      $filters['date_from'] === '' ||
      $filters['date_to'] === '' ||
      $filters['supplier_id'] <= 0
    ) {
      show_404();
    }

    if ($filters['branch_id'] <= 0) {
      $filters['branch_id'] = (int)$this->session->userdata('branch_id');
    }

    $products = $this->Reports_model->getSalesPerSupplierProducts($filters);

    if (empty($products)) {
      show_error(
        'No sales found for the selected supplier and period.'
      );
    }

    $this->load->view(
      'reports/sales_per_supplier/print',
      [
        'filters' => $filters,
        'supplierName' => $products[0]->supplier_name,
        'salesPerSupplierProducts' => $products,
        'title' => 'Sales Per Supplier'
      ]
    );
  }

  public function print_sales_per_supplier_salesman()
  {
    $filters = [
      'date_from' => trim($this->input->post('date_from')),
      'date_to' => trim($this->input->post('date_to')),
      'branch_id' => (int)($this->input->post('branch_id')),
      'supplier_id' => (int)($this->input->post('supplier_id')),
    ];

    if ($filters['date_from'] === '' || $filters['date_to'] === '') {
      show_error('Date From and Date To are required.', 400);
    }

    if ($filters['branch_id'] <= 0) {
      $filters['branch_id'] = (int)$this->session->userdata('branch_id');
    }

    $rows = $this->Reports_model->getSalesPerSupplierSalesman($filters);

    if (empty($rows)) {
      show_error('No sales found.', 404);
    }

    $this->load->view(
      'reports/sales_per_supplier/print_with_salesman',
      [
        'filters' => $filters,
        'salesPerSupplierSalesman' => $rows,
        'title' => 'Sales Per Supplier / Salesman'
      ]
    );
  }

  public function sales_per_customer()
  {
    $this->setPage('Sales Per Customer');
    $this->pageScript = 'reports';

    /*** filters */
    $filter = $this->decodeFilter($this->input->get('filter'));
    $filters = [
      'date_from' => trim($filter['date_from'] ?? $this->input->get('date_from')),
      'date_to' => trim($filter['date_to'] ?? $this->input->get('date_to')),
      'branch_id' => (int)($filter['branch_id'] ?? $this->input->get('branch_id')),
      'customer_id' => (int)($filter['customer_id'] ?? $this->input->get('customer_id')),
    ];

    if ($filters['branch_id'] <= 0) {
      $filters['branch_id'] = (int)$this->session->userdata('branch_id');
    }

    $this->data = array_merge(
      $this->data,
      $filters
    );

    $this->data['branches'] = $this->Branch_model->getDropdown();
    $this->data['customers'] = $this->Customer_model->getDropdown();
    $this->data['salesPerCustomer'] = [];
    $this->data['url'] = 'sales-per-customer';
    $this->data['showActionButton'] = false;

    if ($filters['date_from'] !== '' && $filters['date_to'] !== '') {
      $this->data['salesPerCustomer'] = $this->Reports_model->getSalesPerCustomer($filters);
    }

    $this->render('reports/sales_per_customer/sales_per_customer');
  }

  public function sales_per_customer_products()
  {
    if (!$this->input->is_ajax_request()) {
      show_404();
    }

    $request = $this->getJsonRequest();

    $filters = [
      'date_from' => trim(
        $request['date_from'] ?? ''
      ),
      'date_to' => trim(
        $request['date_to'] ?? ''
      ),
      'branch_id' => (int)(
        $request['branch_id'] ?? 0
      ),
      'customer_id' => (int)(
        $request['customer_id'] ?? 0
      ),
    ];

    if ($filters['customer_id'] <= 0) {
      return $this->jsonResponse(
        FALSE,
        'Invalid customer.'
      );
    }

    if (
      $filters['date_from'] === '' ||
      $filters['date_to'] === ''
    ) {
      return $this->jsonResponse(
        FALSE,
        'Date From and Date To are required.'
      );
    }

    if ($filters['branch_id'] <= 0) {
      $filters['branch_id'] =
        (int)$this->session->userdata('branch_id');
    }

    $this->data['salesPerCustomerProducts'] =
      $this->Reports_model
          ->getSalesPerCustomerProducts($filters);

    $this->data['toolbar'] = [
      'print' => [
        'id' => 'btnPrintSalesPerCustomer',
        'text' => 'Print',
        'icon' => 'fas fa-print'
      ],
      'excel' => [
        'id' => 'btnDownloadCustomerExcel',
        'icon' => 'fas fa-file-excel',
        'text' => 'Download as Excel'
      ],
    ];

    $html = $this->load->view(
      'reports/sales_per_customer/sales_per_customer_products',
      $this->data,
      TRUE
    );

    return $this->jsonResponse(
      TRUE,
      '',
      [
        'html' => $html
      ]
    );
  }

  public function print_sales_per_customer_products()
  {
    $filters = [
      'date_from' => trim(
        $this->input->post('date_from')
      ),
      'date_to' => trim(
        $this->input->post('date_to')
      ),
      'branch_id' => (int)(
        $this->input->post('branch_id')
      ),
      'customer_id' => (int)(
        $this->input->post('customer_id')
      ),
    ];

    if ($filters['customer_id'] <= 0) {
      show_error(
        'Invalid customer.',
        400
      );
    }

    if ($filters['date_from'] === '' || $filters['date_to'] === '') {
      show_error(
        'Date From and Date To are required.',
        400
      );
    }

    if ($filters['branch_id'] <= 0) {
      $filters['branch_id'] = (int)$this->session->userdata('branch_id');
    }

    $products =
      $this->Reports_model->getSalesPerCustomerProducts($filters);

    if (empty($products)) {
      show_error(
        'No sales found for this customer.',
        404
      );
    }

    $this->load->view(
      'reports/sales_per_customer/print',
      [
        'filters' => $filters,
        'customerName' => $products[0]->customer_name,
        'salesPerCustomerProducts' => $products,
        'title' => 'Sales Per Customer'
      ]
    );
  }

}