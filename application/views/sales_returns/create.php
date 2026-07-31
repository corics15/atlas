<?php $this->load->view('partials/page_header'); ?>
<?php $this->load->view('sales_returns/form'); ?>

  <?php if (!empty($error_message)): ?>

    <div class="container-fluid">
      <div class="alert alert-default-warning col-md-6" role="alert">
        <h5 class="alert-heading">
          <i class="fas fa-exclamation-triangle mr-1"></i>
          Notice
        </h5>
        <p><?= $error_message ?></p>
        <hr>
        <a href="<?= base_url('sales_orders') ?>"
          class="btn btn-default btn-sm btn-no-underline">
            <i class="fas fa-arrow-left mr-1"></i>
            Back to Sales Orders
        </a>
      </div>
    </div>

  <?php else : ?>

    <?php $this->load->view('sales_returns/details_table'); ?>

  <?php endif; ?>