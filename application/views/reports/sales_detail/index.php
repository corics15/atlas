<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="align-items-md-end align-items-start d-flex flex-column flex-md-row justify-content-between">

          <?php $this->load->view('reports/search_toolbar'); ?>
          <?php $this->load->view('partials/toolbar'); ?>

        </div>

        <?php /*** sales detail summary */ ?>
        <?php $this->load->view('reports/sales_detail/mini_summary'); ?>

        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover" id="tblSalesDetail">

            <?php $this->load->view('partials/table'); ?>

          </table>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
  window.salesDetailTotalQty = <?= (float)$total_qty ?>;
  window.salesDetailTotalGross = <?= (float)$total_gross ?>;
  window.salesDetailTotalNet = <?= (float)$total_net ?>;
  window.salesDetailTotalUnitPrice = <?= (float)$total_unit_price ?>;
  window.salesDetailTotalDiscountAmt = <?= (float)$total_discount_amount ?>;
  window.salesDetailTotalDiscountPct = <?= (float)$total_discount_percent ?>;
</script>