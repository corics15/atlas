<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <h4>
          <?= isset($inventoryAdjustment)
              ? htmlspecialchars($inventoryAdjustment->adjustment_no)
              : 'New Inventory Adjustment'; ?>
        </h4>

      </div>
    </div>
  </div>
</section>