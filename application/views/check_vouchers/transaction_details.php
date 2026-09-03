<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end align-items-start">

          <?php $this->load->view('partials/search_toolbar'); ?>
          <?php $this->load->view('partials/toolbar'); ?>

        </div>

        <?php $this->load->view('check_vouchers/mini_summary'); ?>

        <?php $this->load->view('partials/table'); ?>

      </div>
    </div>
  </div>
</section>