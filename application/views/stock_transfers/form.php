<?php $isEdit = isset($stockTransfer); $status = null; ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if (isset($stockTransfer)) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Stock Header
          </h3>

          <?php
            $statusClass = NULL;
            switch ($stockTransfer->status) {
              case 'POSTED':
                $statusClass = 'text-success';
                break;
              case 'DRAFT':
                $statusClass = 'text-secondary';
                break;
              default:
                $statusClass = 'text-danger';
                break;
            }
          ?>

          <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= $stockTransfer->status ?>]</div>
        </div>

      <?php else : ?>
        <h3 class="card-title">
          Adjustment Header
        </h3>
      <?php endif; ?>

      </div>

      <div class="card-body">
        <div class="row">

          <div class="col-md-3">
            <div class="form-group">
              <label for="txtAdjustmentNo">Adjustment No.</label>
              <input
                type="text"
                id="txtAdjustmentNo"
                class="form-control form-control-sm"
                value="<?= isset($stockTransfer) ? htmlspecialchars($stockTransfer->adjustment_no) : 'AUTO-GENERATED'; ?>"
                readonly>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label for="dtAdjustmentDate">Adjustment Date</label>
              <input
                type="date"
                id="dtAdjustmentDate"
                class="form-control form-control-sm"
                value="<?= isset($stockTransfer) ? $stockTransfer->adjustment_date : date('Y-m-d'); ?>">
            </div>
          </div>

        </div>

        <div class="row">

          <div class="col-md-12">
            <div class="form-group">
              <label for="txtAdjustmentRemarks">Remarks</label>
              <textarea
                id="txtAdjustmentRemarks"
                class="form-control form-control-sm text-uppercase"
                rows="3"><?= isset($stockTransfer) ? htmlspecialchars($stockTransfer->remarks) : ''; ?></textarea>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<input
    type="hidden"
    id="hidStockTransferId"
    value="<?= isset($stockTransfer) ? $stockTransfer->id : ''; ?>">