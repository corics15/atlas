<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <?php $this->load->view('purchase_returns/form'); ?>

    <div class="card">

      <?php if (!empty($error_message)): ?>

        <div class="card-body">
          <div class="alert alert-default-warning col-md-6" role="alert">
            <h5 class="alert-heading">
              <i class="fas fa-exclamation-triangle mr-1"></i>
              Notice
            </h5>
            <p><?= $error_message ?></p>
            <hr>
            <a href="<?= base_url('goods_receipts') ?>"
              class="btn btn-link btn-sm btn-no-underline">
                <i class="fas fa-arrow-circle-left mr-1"></i>
                Back to Goods Receiving List
            </a>
          </div>
        </div>

      <?php else : ?>

        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Purchase Return Details
          </h3>
          <div class="ml-auto">
            <a href="<?= base_url('purchase-returns') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
            <button type="button" class="btn btn-sm btn-link" id="btnPostPurchaseReturn"><i class="fa fa-check mr-2"></i>Post</button>
            <button type="button" class="btn btn-sm btn-link" id="btnPrintPurchaseReturn"><i class="fa fa-print mr-2"></i>Print</button>
            <button type="button" class="btn btn-sm btn-link" id="btnCancelPurchaseReturn"><i class="fas fa-ban mr-2"></i>Cancel</button>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive table-scroll">

            <table class="table table-sm table-bordered table-hover" id="tblPurchaseReturnDetails">

              <?php $this->load->view('purchase_returns/details_table'); ?>

            </table>

          </div>
        </div>

        <div class="card-body">
          <div class="row justify-content-end">
            <div class="col-md-2">
              <button
                type="button"
                id="btnSavePurchaseReturn"
                class="btn btn-sm btn-default btn-block">
                  Save Purchase Return
              </button>
            </div>
          </div>
        </div>

      <?php endif ?>

    </div>
  </div>
</section>