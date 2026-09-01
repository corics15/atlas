<?php $isEdit = isset($inventoryAdjustment); $status = null; ?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if (isset($inventoryAdjustment)) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Adjustment Information
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
          Adjustment Information
        </h3>
      <?php endif; ?>

      </div>

      <?php /*** header */ ?>
      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr>
            <th width="180">Adjustment No.</th>
            <td class="font-weight-500 text-brown" id="tdRefNo">
              <?= isset($inventoryAdjustment) ? htmlspecialchars($inventoryAdjustment->adjustment_no) : 'AUTO-GENERATED'; ?>
            </td>
          </tr>
          <tr>
            <th>Adjustment Date</th>
            <td>
              <input
                type="date"
                id="dtAdjustmentDate"
                class="form-control form-control-sm w-auto"
                value="<?= isset($inventoryAdjustment) ? $inventoryAdjustment->adjustment_date : date('Y-m-d'); ?>">
            </td>
          </tr>
          <tr>
            <th>Remarks</th>
            <td>
              <input id="txtAdjustmentRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter Remarks..." value="<?= isset($inventoryAdjustment) ? htmlspecialchars($inventoryAdjustment->remarks) : '' ?>">
            </td>
          </tr>
        </table>
      </div>

    </div>
  </div>
</section>

<input type="hidden" id="hidInventoryAdjustmentId" value="<?= isset($inventoryAdjustment) ? $inventoryAdjustment->id : ''; ?>">