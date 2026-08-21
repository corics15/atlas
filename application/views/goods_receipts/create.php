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
          <div class="row justify-content-end">
            <div class="col-md-2">
              <button
                type="button"
                id="btnSaveGoodsReceipt"
                class="btn btn-sm btn-default btn-block">
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