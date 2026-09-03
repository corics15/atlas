<thead class="thead-orange">
  <tr>
    <th class="text-center">CV No.</th>
    <th class="text-center">Voucher Date</th>
    <th>Payee</th>
    <th class="text-center">Payment Method</th>
    <th>Bank Account</th>
    <th class="text-center">Check No.</th>
    <th class="text-center">Reference No.</th>
    <th class="text-right">Amount</th>
    <th class="text-center">Status</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($checkVouchers)): ?>
    <tr>
      <td colspan="9" class="text-center text-muted py-3">No Check Vouchers found.</td>
    </tr>
  <?php else: ?>
    <?php foreach ($checkVouchers as $cv): ?>
      <tr>
        <td class="font-weight-500 text-center"><a href="<?= $cv->url ?>" class="font-weight-500 text-olive"><?= htmlspecialchars($cv->cv_no) ?></a></td>
        <td class="text-center"><?= date('m/d/Y', strtotime($cv->voucher_date)) ?></td>
        <td><?= htmlspecialchars($cv->payee_name) ?></td>
        <td><?= htmlspecialchars(str_replace('_', ' ', $cv->payment_method)) ?></td>
        <td>
          <?php if (!empty($cv->bank_name)): ?>
            <?= htmlspecialchars($cv->bank_name . ' - ' . $cv->bank_account_name . ' (' . $cv->account_no . ')') ?>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($cv->check_no ?? '') ?></td>
        <td class="text-center"><?= htmlspecialchars($cv->reference_no ?? '') ?></td>
        <td class="text-right"><?= number_format((float) $cv->amount, 2) ?></td>
        <td class="text-center">
          <?php
            switch (htmlspecialchars($cv->status)) {
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