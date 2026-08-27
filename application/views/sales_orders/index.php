<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="align-items-md-end align-items-start d-flex flex-column flex-md-row justify-content-between">

          <?php $this->load->view('goods_receipts/search_toolbar'); ?>
          <?php $this->load->view('partials/toolbar'); ?>

        </div>

        <?php /*** so summary */ ?>
        <?php $this->load->view('sales_orders/mini_summary'); ?>


        <div class="table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover" id="tblSOList">

            <?php $this->load->view('partials/table'); ?>

          </table>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
  window.totalAmount = <?= (float)$total_amount ?>;
  window.itemCount = <?= (float)$item_count ?>;
</script>