<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">

      <?php //*** Purchase Order */ ?>
      <div class="card-header">

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Order Information
          </h3>

          <?php /*** status class set in purchase_orders.js */ ?>
          <div class="ls-wider" style="font-weight:500"></div>
        </div>
      </div>

      <?php /*** header */ ?>
      <?php $this->load->view('purchase_orders/header') ?>

    </div>

    <?php /*** order details */ ?>
    <?php $this->load->view('purchase_orders/details_table') ?>

    <?php /*** footer */ ?>
    <?php $this->load->view('purchase_orders/footer') ?>

  </div>
</section>

<script>
  window.purchaseOrderId = <?= (int) ($purchaseOrderId ?? 0); ?>;
  window.atlasUoms = <?= json_encode(array_map(function($uom) {
    return [
      'id' => (int)$uom->id,
      'uom' => $uom->uom
    ];
  }, $uoms)); ?>;
</script>