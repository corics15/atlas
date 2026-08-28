<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <?php $this->load->view('goods_receipts/form'); ?>

    <div class="card">

      <div class="card-header">
        <h3 class="card-title">
          Receiving Details
        </h3>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">

          <table class="table table-sm table-bordered table-hover" id="tblGoodsReceiptDetails">

            <?php $this->load->view('goods_receipts/details_table'); ?>

          </table>
        </div>
      </div>

      <div class="card-body">
        <?php if (!$error_message) : ?>
          <div class="row">
            <div class="col md-9 d-flex align-items-end">

              <div class="alert alert-light font-sm mb-2" role="alert">
                <div class="font-weight-500 mb-1">
                  <i class="fas fa-info-circle mr-1 text-info"></i>
                  Goods Receipt Guide
                </div>

                <div>
                  <span class="font-weight-500">1.</span>
                  Review the products and quantities from the selected <span class="font-weight-500 text-success">Purchase Order</span>.
                </div>

                <div>
                  <span class="font-weight-500">2.</span>
                  Check the <span class="font-weight-500 text-danger">Ordered</span>, <span class="font-weight-500 text-danger">Received</span>, and <span class="font-weight-500 text-danger">Remaining</span> quantities for each item.
                </div>

                <div>
                  <span class="font-weight-500">3.</span>
                  Enter the quantity currently received under <span class="font-weight-500 text-danger">Receive Now</span>.
                  The quantity cannot exceed the remaining PO quantity.
                </div>

                <div>
                  <span class="font-weight-500">4.</span>
                  Review the <span class="font-weight-500 text-danger">Conversion</span> for each UOM.
                  Change it only when the actual received packing conversion differs from the current value.
                </div>

                <div>
                  <span class="font-weight-500">5.</span>
                  Click <span class="font-weight-500 text-brown">Save Goods Receipt</span> after verifying the received quantities.
                </div>

                <div>
                  <span class="font-weight-500">6.</span>
                  Once verified, click <span class="font-weight-500 text-orange">Post</span> to update inventory quantities.
                  Posting cannot be undone.
                </div>

                <div>
                  <span class="font-weight-500">7.</span>
                  After posting, a <span class="font-weight-500 text-olive">Purchase Return</span> may be created if received items need to be returned to the supplier.
                </div>
              </div>

            </div>
            <div class="col-md-3">
              <button type="button" id="btnSaveGoodsReceipt" class="btn btn-sm btn-default btn-block">
                  Save Draft
              </button>
            </div>
          </div>

        <?php else : ?>

          <div class="row">
            <div class="col-auto">
              <div class="alert alert-default-warning" role="alert">
                <h5 class="alert-heading">
                  <i class="fas fa-exclamation-triangle mr-1"></i>
                  Notice
                </h5>
                <p><?= $error_message ?></p>
                <hr>
                <a href="<?= base_url('purchase-orders/list') ?>"
                  class="btn btn-link btn-sm btn-no-underline">
                    <i class="fas fa-arrow-circle-left mr-1"></i>
                    Back to Purchase Order List
                </a>
              </div>
            </div>
          </div>

        <?php endif; ?>
      </div>

    </div>
  </div>
</section>