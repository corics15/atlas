<?php $isEdit = isset($stockTransfer); $status = null; ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if (isset($stockTransfer)) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Stock Information
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
          Stock Information
        </h3>
      <?php endif; ?>

      </div>

      <?php /*** header */ ?>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">

            <input type="hidden" id="txtTransferNo" value="<?= isset($stockTransfer) ? htmlspecialchars($stockTransfer->transfer_no) : 'AUTO-GENERATED'; ?>">
            <table class="table table-sm table-borderless">
              <tr>
                <th width="180">ST No.</th>
                <td class="font-weight-500 text-brown" id="tdRefNo"><?= isset($stockTransfer) ? htmlspecialchars($stockTransfer->transfer_no) : 'AUTO-GENERATED'; ?></td>
              </tr>
              <tr>
                <th>Transfer Date</th>
                <td>
                  <input type="date" id="dtTransferDate" class="form-control form-control-sm w-auto" value="<?= isset($stockTransfer) ? $stockTransfer->transfer_date : date('Y-m-d'); ?>">
                </td>
              </tr>
              <tr>
                <th>From Branch</th>
                <td>
                  <select id="selFromBranch" class="form-control form-control-sm no-event" disabled>
                    <option value="">Select Branch</option>
                    <?php foreach ($branches as $branch): ?>
<option
  value="<?= $branch->id ?>"
  <?php
    if (isset($stockTransfer)) {
      echo $stockTransfer->from_branch_id == $branch->id ? 'selected' : '';
    } else {
      echo $currentBranchId == $branch->id ? 'selected' : '';
    }
  ?>>
  <?= htmlspecialchars($branch->branch_name) ?>
</option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <tr>
                <th>To Branch</th>
                <td>
                  <select id="selToBranch" class="form-control form-control-sm">
                    <option value="">Select Branch</option>
                    <?php foreach ($branches as $branch): ?>
                      <option
                          value="<?= $branch->id ?>"
                          <?= isset($stockTransfer) && $stockTransfer->to_branch_id == $branch->id ? 'selected' : '' ?>>
                          <?= $branch->branch_name ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <tr>
                <th>Remarks</th>
                <td>
                  <input type="text" id="txtStockTransferRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks" value="<?= isset($stockTransfer) ? htmlspecialchars($stockTransfer->remarks) : ''; ?>">
                </td>
              </tr>
            </table>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<input type="hidden" id="hidStockTransferId" value="<?= isset($stockTransferId) ? $stockTransferId : ''; ?>">