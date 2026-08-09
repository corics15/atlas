<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="card">

      <div class="card-header">
        <h3 class="card-title">
          Sales per Supplier
        </h3>
      </div>

      <div class="card-body">

        <div class="form-row">

          <div class="form-group col-auto">
            <label for="dtDateFrom">Date From</label>
            <input
              type="date"
              id="dtDateFrom"
              class="form-control form-control-sm">
          </div>

          <div class="form-group col-auto">
            <label for="dtDateTo">Date To</label>
            <input
              type="date"
              id="dtDateTo"
              class="form-control form-control-sm">
          </div>

          <div class="form-group col-auto">
            <label for="selBranch">Branch</label>
            <select
              id="selBranch"
              class="form-control form-control-sm custom-control">

              <?php foreach ($branches as $branch): ?>

                <option
                  value="<?= $branch->id; ?>" <?= (int)$branch->id === (int)$selectedBranchId ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($branch->branch_name); ?>
                </option>

              <?php endforeach; ?>

            </select>
          </div>

          <div class="form-group col-md-3 d-flex align-items-end">

            <button
              type="button"
              id="btnGenerateSalesPerSupplier"
              class="btn btn-sm btn-default mr-2">
              <i class="fas fa-search mr-1"></i>
              Generate
            </button>

            <button
              type="button"
              id="btnResetSalesPerSupplier"
              class="btn btn-sm btn-default">
              <i class="fas fa-sync-alt mr-1"></i>
              Reset
            </button>

          </div>

        </div>

      </div>

    </div>

  </div>
</section>