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
            <button type="button" class="btn btn-sm btn-link" id="btnPostPurchaseReturn" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-check mr-2"></i>Post</button>
            <button type="button" class="btn btn-sm btn-link" id="btnPrintPurchaseReturn"><i class="fa fa-print mr-2"></i>Print</button>
            <button type="button" class="btn btn-sm btn-link" id="btnCancelPurchaseReturn" <?= !$isEditable ? 'disabled' : '' ?>><i class="fas fa-ban mr-2"></i>Cancel</button>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">

            <table class="table table-sm table-bordered table-hover" id="tblPurchaseReturnDetails">

              <?php $this->load->view('purchase_returns/details_table'); ?>

            </table>

          </div>
        </div>

        <div class="card-body">
          <div class="row justify-content-between">

            <div class="alert alert-light font-sm mb-0" role="alert">
              <div class="font-weight-500 mb-1">
                <i class="fas fa-info-circle mr-1 text-info"></i>
                Purchase Return Guide
              </div>

              <div>
                <span class="font-weight-500">1.</span>
                Review the products originally received under the selected Goods Receipt.
              </div>

              <div>
                <span class="font-weight-500">2.</span>
                Check the <span class="font-weight-500 text-danger">Received</span>, <span class="font-weight-500 text-danger">Returned</span>, and
                <span class="font-weight-500 text-danger">Available</span> quantities before entering a return.
              </div>

              <div>
                <span class="font-weight-500">3.</span>
                Enter the quantity to return under <span class="font-weight-500 text-danger">Return Qty</span>.
                The return quantity cannot exceed the available quantity.
              </div>

              <div>
                <span class="font-weight-500">4.</span>
                Review the return details, then click <span class="font-weight-500 text-brown">Save Purchase Return</span>.
              </div>

              <div>
                <span class="font-weight-500">5.</span>
                Once verified, <span class="font-weight-500 text-orange">Post</span> the Purchase Return to update inventory.
              </div>

              <div>
                <span class="font-weight-500">6.</span>
                Once the Purchase Return has been created, the
                <span class="font-weight-500 text-danger">Return Qty can no longer be changed</span>.
                If a correction is needed, cancel the Purchase Return and create a new one.
              </div>

            </div>


            <div class="col-md-2">
              <button type="button" id="btnSavePurchaseReturn" class="btn btn-sm btn-default btn-block" <?= !$isEditable ? 'disabled' : '' ?>>Save Purchase Return</button>
            </div>
          </div>
        </div>

      <?php endif ?>

    </div>
  </div>
</section>