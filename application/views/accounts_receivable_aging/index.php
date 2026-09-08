<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-body">

        <?php $this->load->view('accounts_receivable_aging/search_toolbar'); ?>

      </div>
    </div>

    <div class="card">

      <div class="card-header">
        <h3 class="card-title">
          Accounts Receivable Aging
        </h3>

        <div class="card-tools">
          <span class="text-muted font-sm">As of <?= date('m/d/Y', strtotime($as_of_date)) ?></span>
          <span class="text-muted font-smr">:: Click on Total to view details.</span>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">

          <table class="table table-sm table-bordered table-hover mb-0">

            <?php $this->load->view('partials/table'); ?>

          </table>
        </div>
      </div>
    </div>

     <?php $this->load->view('accounts_receivable_aging/modal'); ?>

  </div>
</section>