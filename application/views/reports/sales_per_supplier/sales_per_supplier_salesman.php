<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">
          Sales Per Supplier / Salesman
        </h1>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="card">

      <div class="card-header">
        <h3 class="card-title">
          Sales Per Supplier / Salesman
        </h3>
      </div>

      <div class="card-body">

        <?php $this->load->view('reports/search_toolbar'); ?>

        <div class="mt-4 table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover mb-0" id="tblSalesPerSupplierSalesman">

            <?php $this->load->view('reports/sales_per_supplier/sales_per_supplier_salesman_table'); ?>

          </table>
        </div>
      </div>

    </div>
  </div>
</section>