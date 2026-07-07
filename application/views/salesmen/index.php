<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-center">

          <?php $this->load->view('partials/search_toolbar'); ?>
          <?php $this->load->view('partials/toolbar'); ?>

        </div>

        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover">
            <?php $this->load->view('partials/table'); ?>
          </table>
        </div>

      </div>
    </div>
  </div>
</section>

<?php $this->load->view('salesmen/modal'); ?>