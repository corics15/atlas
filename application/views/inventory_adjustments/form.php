<?php $isEdit = isset($inventoryAdjustment); $status = null; ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if (isset($inventoryAdjustment)) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Adjustment Header
          </h3>

          <?php
            $statusClass = NULL;
            switch ($inventoryAdjustment->status) {
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

          <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= $inventoryAdjustment->status ?>]</div>
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
                value="<?= isset($inventoryAdjustment) ? htmlspecialchars($inventoryAdjustment->adjustment_no) : 'AUTO-GENERATED'; ?>"
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
                value="<?= isset($inventoryAdjustment) ? $inventoryAdjustment->adjustment_date : date('Y-m-d'); ?>">
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
                rows="3"><?= isset($inventoryAdjustment) ? htmlspecialchars($inventoryAdjustment->remarks) : ''; ?></textarea>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<input
    type="hidden"
    id="hidInventoryAdjustmentId"
    value="<?= isset($inventoryAdjustment) ? $inventoryAdjustment->id : ''; ?>">