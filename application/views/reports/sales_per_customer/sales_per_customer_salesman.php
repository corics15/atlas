<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">
          Sales Per Customer / Salesman
        </h1>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="card">

      <div class="card-body">

        <?php $this->load->view('reports/search_toolbar'); ?>

        <div class="mt-4 table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover mb-0" id="tblSalesPerCustomerSalesman">

            <?php $this->load->view('reports/sales_per_customer/sales_per_customer_salesman_table'); ?>

          </table>
        </div>
      </div>

    </div>
  </div>
</section>