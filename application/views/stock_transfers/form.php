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
              case 'OPEN':
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
          Stock Header
        </h3>
      <?php endif; ?>

      </div>

      <?php /*** header */ ?>
      <div class="card-body">
        <div class="row">

          <div class="col-md-3">
            <div class="form-group">
              <label for="txtTransferNo">Transfer No.</label>
              <input
                type="text"
                id="txtTransferNo"
                class="form-control form-control-sm"
                value="<?= isset($stockTransfer) ? htmlspecialchars($stockTransfer->transfer_no) : 'AUTO-GENERATED'; ?>"
                readonly>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label for="dtTransferDate">Transfer Date</label>
              <input
                type="date"
                id="dtTransferDate"
                class="form-control form-control-sm"
                value="<?= isset($stockTransfer) ? $stockTransfer->transfer_date : date('Y-m-d'); ?>">
            </div>
          </div>

        </div>

        <div class="row">

          <div class="col-md-6">
            <div class="form-group">

              <label for="selFromBranch">From Branch</label>
              <select
                id="selFromBranch"
                class="form-control form-control-sm">
                <option value="">Select Branch</option>
                <?php foreach ($branches as $branch): ?>
                  <option
                      value="<?= $branch->id ?>"
                      <?= isset($stockTransfer) && $stockTransfer->from_branch_id == $branch->id ? 'selected' : '' ?>>
                      <?= $branch->branch_name ?>
                  </option>
                <?php endforeach; ?>
              </select>

            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">

              <label for="selToBranch">To Branch</label>
              <select
                id="selToBranch"
                class="form-control form-control-sm">
                <option value="">Select Branch</option>
                <?php foreach ($branches as $branch): ?>
                  <option
                      value="<?= $branch->id ?>"
                      <?= isset($stockTransfer) && $stockTransfer->to_branch_id == $branch->id ? 'selected' : '' ?>>
                      <?= $branch->branch_name ?>
                  </option>
                <?php endforeach; ?>
              </select>

            </div>
          </div>

        </div>

        <div class="row">

          <div class="col-md-12">
            <div class="form-group">
              <label for="txtStockTransferRemarks">Remarks</label>
              <textarea
                id="txtStockTransferRemarks"
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
    value="<?= isset($stockTransferId) ? $stockTransferId : ''; ?>">