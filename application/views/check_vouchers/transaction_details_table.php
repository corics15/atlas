<div class="table-responsive">
  <table class="table table-sm table-hover table-bordered mb-0" id="tblCheckVoucherTransactionList">
    <thead class="thead-orange">
      <tr>
        <th class="text-center">Date</th>
        <th class="text-center">CV No.</th>
        <th class="text-center">Branch</th>
        <th>Payee</th>
        <th class="text-center">Payment Method</th>
        <th class="text-center">Account Code</th>
        <th>Account Description</th>
        <th class="text-right">Debit</th>
        <th class="text-right">Credit</th>
        <th>Remarks</th>
        <th class="text-center">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($details)): ?>
        <tr>
          <td colspan="11" class="text-center text-muted py-3">No Check Voucher transaction details found.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($details as $detail): ?>
          <tr>
            <td class="text-center" data-a-h="center"><?= date('m/d/Y', strtotime(htmlspecialchars($detail->voucher_date))) ?></td>
            <td class="text-center" data-a-h="center">
              <a href="<?= htmlspecialchars($detail->url) ?>" class="font-weight-500 text-olive">
                <?= htmlspecialchars($detail->cv_no) ?>
              </a>
            </td>
            <td class="text-center" data-a-h="center"><?= htmlspecialchars($detail->branch_code ?: $detail->branch_name) ?></td>
            <td><?= htmlspecialchars($detail->payee_name) ?></td>
            <td class="text-center" data-a-h="center"><?= htmlspecialchars(str_replace('_', ' ', $detail->payment_method)) ?></td>
            <td class="text-center" data-a-h="center" data-t="n" data-num-fmt="###0"><?= htmlspecialchars($detail->account_code) ?></td>
            <td><?= htmlspecialchars($detail->account_name) ?></td>
            <td class="font-weight-500 text-right" data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= (float) $detail->debit > 0 ? number_format((float) $detail->debit, 2) : '' ?></td>
            <td class="font-weight-500 text-right" data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= (float) $detail->credit > 0 ? number_format((float) $detail->credit, 2) : '' ?></td>
            <td data-excel-value="<?= htmlspecialchars($detail->remarks) ?>">
              <?php
                $remarks = htmlspecialchars($detail->remarks);
                echo (mb_strlen($remarks) > 30)
                  ? mb_strimwidth($remarks, 0, 30, '...')
                  : $remarks;
              ?>
            </td>
            <td class="text-center" data-a-h="center">
              <?php
                switch (htmlspecialchars($detail->status)) {
                  case 'DRAFT':
                    $status = '<span class="badge badge-secondary">DRAFT</span>';
                    break;
                  case 'POSTED':
                    $status = '<span class="badge badge-success">POSTED</span>';
                    break;
                  default:
                    $status = '<span class="badge badge-danger">CANCELLED</span>';
                    break;
                }
                echo $status;
              ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>

    <?php if (!empty($details)): ?>
      <tfoot>
        <tr class="font-weight-500">
          <td colspan="7" class="text-right" data-a-h="right" data-f-bold="true">TOTAL</td>
          <td class="text-right" data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($summary['total_debit'], 2) ?></td>
          <td class="text-right" data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($summary['total_credit'], 2) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    <?php endif; ?>
  </table>
</div>