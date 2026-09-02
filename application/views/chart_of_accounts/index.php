<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="align-items-md-end align-items-start d-flex flex-column flex-md-row justify-content-between">

          <div><?php /*** filler */ ?></div>
          <?php $this->load->view('partials/toolbar'); ?>

        </div>

        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover" id="tblChartOfAccounts">

            <?php $this->load->view('chart_of_accounts/table'); ?>

          </table>
        </div>

      </div>
    </div>
  </div>
</section>