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
                class="btn btn-default btn-sm btn-no-underline">
                  <i class="fas fa-arrow-left mr-1"></i>
                  Back to Goods Receiving List
              </a>
            </div>
          </div>

        <?php else : ?>

        <div class="card-header">
          <h3 class="card-title">
            Purchase Return Details
          </h3>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive table-scroll">

            <table class="table table-sm table-bordered table-hover" id="tblPurchaseReturnDetails">

              <?php $this->load->view('purchase_returns/details_table'); ?>

            </table>

          </div>
        </div>

        <?php endif ?>

    </div>
  </div>
</section>