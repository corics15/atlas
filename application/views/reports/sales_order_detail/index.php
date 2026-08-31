<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="align-items-md-end align-items-start d-flex flex-column flex-md-row justify-content-between">

          <?php $this->load->view('reports/search_toolbar'); ?>
          <?php $this->load->view('partials/toolbar'); ?>

        </div>

        <?php /*** sales order detail summary */ ?>
        <?php $this->load->view('reports/sales_order_detail/mini_summary'); ?>

        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover" id="tblSalesOrderDetail">

            <?php $this->load->view('partials/table'); ?>

          </table>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
  window.salesOrderDetailTotalQty = <?= (float)$total_qty ?>;
  window.salesOrderDetailItemCount = <?= (float)$total_item_count ?>;
  window.salesOrderDetailTotalAmount = <?= (float)$total_amount ?>;
</script>