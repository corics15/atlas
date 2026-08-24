<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-body">

        <?php $this->load->view('customer_ledger/search_toolbar'); ?>

      </div>
    </div>

    <?php if ($customer_id > 0): ?>

      <?php /*** summary */ ?>
      <?php $this->load->view('customer_ledger/summary'); ?>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Ledger Transactions</h3>
        </div>

        <div class="card-body p-0">

          <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">

              <?php $this->load->view('partials/table'); ?>

            </table>
          </div>

        </div>
      </div>

    <?php endif; ?>

  </div>
</section>