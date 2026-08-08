<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-body">

        <?php $this->load->view('inventory/summary'); ?>

      </div>
    </div>

    <div class="card">

      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Stock Ledger Details
        </h3>
        <div class="ml-auto">
          <a href="<?= base_url('inventory') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintStockLedger"><i class="fa fa-print mr-2"></i>Print</button>
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover">

            <?php $this->load->view('partials/table'); ?>

          </table>
        </div>

      </div>
    </div>

  </div>
</section>