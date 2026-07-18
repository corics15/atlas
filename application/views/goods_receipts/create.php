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
        <div class="table-responsive table-scroll">

          <table class="table table-sm table-bordered table-hover" id="tblGoodsReceiptDetails">

            <?php $this->load->view('goods_receipts/details_table'); ?>

          </table>

        </div>
      </div>
    </div>
  </div>
</section>