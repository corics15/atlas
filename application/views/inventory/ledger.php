<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-body">

        <?php $this->load->view('inventory/summary'); ?>

      </div>

      <div class="card-footer">

        <div class="d-flex justify-content-between align-items-center">
          <button
            type="button"
            id="btnBackInventoryInquiry"
            class="btn btn-default btn-sm mr-2"><i class="fas fa-arrow-left mr-2"></i>
            Back
          </button>

          <button
            type="button"
            id="btnPrintStockLedger"
            target="_blank"
            class="btn btn-default btn-sm mr-2"><i class="fas fa-print mr-2"></i>
            Print
          </button>
        </div>

      </div>
    </div>

    <div class="card">
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