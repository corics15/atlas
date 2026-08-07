<?php $this->load->view('partials/page_header'); ?>
<?php $this->load->view('delivery_receipts/form'); ?>

  <?php if (!empty($error_message)): ?>

    <div class="container-fluid">
      <div class="alert alert-default-warning col-md-6" role="alert">
        <h5 class="alert-heading">
          <i class="fas fa-exclamation-triangle mr-1"></i>
          Notice
        </h5>
        <p><?= $error_message ?></p>
        <hr>
        <a href="<?= base_url('delivery-receipts') ?>"
          class="btn btn-link btn-sm btn-no-underline">
            <i class="fas fa-arrow-circle-left mr-1"></i>
            Back to Delivery Receipts
        </a>
      </div>
    </div>

  <?php else : ?>

    <?php $this->load->view('delivery_receipts/details_table'); ?>

  <?php endif; ?>