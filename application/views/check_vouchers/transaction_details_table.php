<div class="table-responsive">
  <table class="table table-sm table-hover table-bordered mb-0">
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
            <td class="text-center"><?= date('m/d/Y', strtotime(htmlspecialchars($detail->voucher_date))) ?></td>
            <td class="text-center">
              <a href="<?= htmlspecialchars($detail->url) ?>" class="font-weight-500 text-olive">
                <?= htmlspecialchars($detail->cv_no) ?>
              </a>
            </td>
            <td class="text-center"><?= htmlspecialchars($detail->branch_code ?: $detail->branch_name) ?></td>
            <td><?= htmlspecialchars($detail->payee_name) ?></td>
            <td class="text-center"><?= htmlspecialchars(str_replace('_', ' ', $detail->payment_method)) ?></td>
            <td class="text-center"><?= htmlspecialchars($detail->account_code) ?></td>
            <td><?= htmlspecialchars($detail->account_name) ?></td>
            <td class="text-right"><?= (float) $detail->debit > 0 ? number_format((float) $detail->debit, 2) : '' ?></td>
            <td class="text-right"><?= (float) $detail->credit > 0 ? number_format((float) $detail->credit, 2) : '' ?></td>
            <td><?= htmlspecialchars($detail->remarks ?? '') ?></td>
            <td class="text-center"><?= htmlspecialchars($detail->status) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>

    <?php if (!empty($details)): ?>
      <tfoot>
        <tr class="font-weight-bold">
          <td colspan="7" class="text-right">TOTAL</td>
          <td class="text-right"><?= number_format($summary['total_debit'], 2) ?></td>
          <td class="text-right"><?= number_format($summary['total_credit'], 2) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    <?php endif; ?>
  </table>
</div>