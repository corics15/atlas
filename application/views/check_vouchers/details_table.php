<?php
  $details = $details ?? [];
  $id = $checkVoucher->id ?? null;
  $status = $checkVoucher->status ?? 'DRAFT';
?>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h3 class="card-title">Account Distribution</h3>

    <div class="ml-auto">
      <a href="<?= base_url('check-vouchers') ?>" class="btn btn-sm btn-link ml-auto">
        <i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List
      </a>

      <?php if ($isEditable): ?>
        <button type="button" id="btnAddAccount" class="btn btn-sm btn-link ml-auto">
          <i class="fa fa-plus mr-2"></i>Add Account
        </button>

        <?php if (!empty($id)): ?>
          <button type="button" id="btnPostCheckVoucher" class="btn btn-sm btn-link ml-auto">
            <i class="fa fa-check mr-2"></i>Post
          </button>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (!empty($id) && in_array($status, ['DRAFT', 'POSTED'], true)): ?>
        <button type="button" id="btnCancelCheckVoucher" class="btn btn-sm btn-link text-danger ml-auto">
          <i class="fa fa-ban mr-2"></i>Cancel
        </button>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0" id="accountingDetailsTable">
        <thead class="thead-orange">
          <tr>
            <th style="width:15%;">Account Code</th>
            <th>Account Description</th>
            <th style="width:13%;" class="text-right">Debit</th>
            <th style="width:13%;" class="text-right">Credit</th>
            <th style="width:20%;">Remarks</th>
            <?php if ($isEditable): ?>
              <th style="width:45px;"></th>
            <?php endif; ?>
          </tr>
        </thead>

        <tbody id="accountingDetailsBody">
          <?php if (!$isEditable && empty($details)): ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-3">No accounting entries.</td>
            </tr>
          <?php endif; ?>

          <?php if (!$isEditable): ?>
            <?php foreach ($details as $detail): ?>
              <tr>
                <td><?= htmlspecialchars($detail->account_code) ?></td>
                <td><?= htmlspecialchars($detail->account_name) ?></td>
                <td class="text-right"><?= $detail->debit > 0 ? number_format((float) $detail->debit, 2) : '' ?></td>
                <td class="text-right"><?= $detail->credit > 0 ? number_format((float) $detail->credit, 2) : '' ?></td>
                <td><?= htmlspecialchars($detail->remarks ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>

        <tfoot>
          <tr>
            <th colspan="2" class="text-right">Totals</th>
            <th id="totalDebit" class="text-right">0.00</th>
            <th id="totalCredit" class="text-right">0.00</th>
            <th colspan="<?= $isEditable ? 2 : 1 ?>"></th>
          </tr>
          <tr>
            <th colspan="2" class="text-right">Difference</th>
            <th id="balanceDifference" colspan="2" class="text-right">0.00</th>
            <th colspan="<?= $isEditable ? 2 : 1 ?>"></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?php /*** footer */ ?>
<div class="card">
  <div class="card-body">
    <div class="form-row">
      <div class="align-items-end col-md-8 d-flex">
        <div class="alert alert-light font-sm mb-0" role="alert">
          <div class="font-weight-500 mb-1">
            <i class="fas fa-info-circle mr-1 text-info"></i>
            Check Voucher Guide
          </div>

          <div>
            <span class="font-weight-500">1.</span>
            Select the <span class="font-weight-500 text-danger">Payee</span> and appropriate
            <span class="font-weight-500 text-danger">Payment Method</span>.
          </div>

          <div>
            <span class="font-weight-500">2.</span>
            For <span class="font-weight-500 text-brown">Check</span> or
            <span class="font-weight-500 text-brown">Bank Transfer</span>, select the correct Bank Account and enter the required payment reference.
          </div>

          <div>
            <span class="font-weight-500">3.</span>
            Add the accounting distribution and enter the appropriate
            <span class="font-weight-500 text-danger">Debit</span> and
            <span class="font-weight-500 text-danger">Credit</span> amounts.
          </div>

          <div>
            <span class="font-weight-500">4.</span>
            Review the totals, then click
            <span class="font-weight-500 text-brown">Save Check Voucher</span>.
          </div>

          <div>
            <span class="font-weight-500">5.</span>
            When finalized and balanced, click
            <span class="font-weight-500 text-orange">Post Check Voucher</span>.
            Posting creates the corresponding Journal Voucher and prevents further editing.
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <?php if ($isEditable): ?>
          <button type="button" id="btnSaveCheckVoucher" class="btn btn-sm btn-default btn-block">Save Check Voucher</button>

          <?php //if (!empty($id)): ?>
            <!-- <button type="button" id="btnPostCheckVoucher" class="btn btn-sm btn-warning btn-block">Post Check Voucher</button> -->
          <?php //endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($isEditable): ?>
  <script id="existingCheckVoucherDetails" type="application/json">
    <?= json_encode(
      array_map(static function ($detail) {
        return [
          'account_id'   => (int) $detail->account_id,
          'account_code' => $detail->account_code,
          'account_name' => $detail->account_name,
          'debit'        => (float) $detail->debit,
          'credit'       => (float) $detail->credit,
          'remarks'      => $detail->remarks ?? ''
        ];
      }, $details),
      JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>
  </script>
<?php endif; ?>
