<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">

        <div class="align-items-md-end align-items-start d-flex flex-column flex-md-row justify-content-between">

          <?php $this->load->view('partials/search_toolbar'); ?>
          <?php $this->load->view('partials/toolbar'); ?>

        </div>

        <?php $this->load->view('partials/record_count'); ?>

        <div class="table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover">
            <?php $this->load->view('partials/table'); ?>
          </table>
        </div>

      </div>
    </div>
  </div>
</section>

<?php $this->load->view('suppliers/modal'); ?>