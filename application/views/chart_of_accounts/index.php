<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover">

            <?php $this->load->view('chart_of_accounts/table'); ?>

          </table>
        </div>

      </div>
    </div>
  </div>
</section>