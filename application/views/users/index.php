<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <div class="row mb-3">

          <div class="col-md-4">
            <?php $this->load->view('partials/search_toolbar'); ?>
          </div>

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

<?php $this->load->view('users/modal'); ?>