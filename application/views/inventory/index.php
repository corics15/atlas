<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="align-items-md-end align-items-start d-flex flex-column flex-md-row justify-content-between">

          <?php $this->load->view('partials/search_toolbar'); ?>
          <?php $this->load->view('partials/toolbar'); ?>

        </div>

        <?php /*** inventory summary */ ?>
        <div class="row mb-3">

          <div class="col-md-4">
            <div class="small-box bg-light mb-0">
              <div class="inner">
                <h4><?= number_format($recordCount); ?></h4>
                <p>Total Items</p>
              </div>

              <div class="icon">
                <i class="fas fa-boxes"></i>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="small-box bg-light mb-0">
              <div class="inner">
                <h4><?= number_format($totalQty, 0); ?></h4>
                <p>Total Quantity</p>
              </div>

              <div class="icon">
                <i class="fas fa-cubes"></i>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="small-box bg-light mb-0">
              <div class="inner">
                <h4><?= number_format($totalAmount, 2); ?></h4>
                <p>Total Inventory Amount</p>
              </div>

              <div class="icon">
                <i class="fas fa-coins"></i>
              </div>
            </div>
          </div>

        </div>

        <div class="table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover" id="tblInventory">

            <?php $this->load->view('partials/table'); ?>

          </table>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
  window.inventoryTotalQty = <?= (float)$totalQty ?>;
  window.inventoryTotalAmount = <?= (float)$totalAmount ?>;
</script>