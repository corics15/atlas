<thead class="thead-orange">
  <tr>
    <th>Customer</th>
    <th width="140" class="text-right">Current</th>
    <th width="140" class="text-right">1 - 30 Days</th>
    <th width="140" class="text-right">31 - 60 Days</th>
    <th width="140" class="text-right">61 - 90 Days</th>
    <th width="140" class="text-right">Over 90 Days</th>
    <th width="150" class="text-right">Total</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($aging)): ?>

    <tr>
      <td colspan="7" class="text-center text-muted py-3">
        No outstanding receivables found as of
        <?= date('m/d/Y', strtotime($as_of_date)) ?>.
      </td>
    </tr>

  <?php else: ?>

    <?php foreach ($aging as $row): ?>

      <tr>
        <td><?= htmlspecialchars($row->customer_name) ?></td>
        <td class="text-right"><?= number_format((float)$row->current_amount, 2) ?></td>
        <td class="text-right"><?= (float)$row->days_1_30 > 0 ? number_format((float)$row->days_1_30, 2) : '' ?></td>
        <td class="text-right"><?= (float)$row->days_31_60 > 0 ? number_format((float)$row->days_31_60, 2) : '' ?></td>
        <td class="text-right"><?= (float)$row->days_61_90 > 0 ? number_format((float)$row->days_61_90, 2) : '' ?></td>
        <td class="text-right"><?= (float)$row->over_90 ? number_format((float)$row->over_90, 2) : '' ?></td>
        <td class="text-right font-weight-500"><?= number_format((float)$row->total_balance, 2) ?></td>
      </tr>

    <?php endforeach; ?>

    <?php /*** totals */ ?>
    <tr class="font-weight-500">
      <td class="text-right">TOTAL</td>
      <td class="text-right"><?= number_format((float)$currentTotal, 2) ?></td>
      <td class="text-right"><?= number_format((float)$days1To30Total, 2) ?></td>
      <td class="text-right"><?= number_format((float)$days31To60Total, 2) ?></td>
      <td class="text-right"><?= number_format((float)$days61To90Total, 2) ?></td>
      <td class="text-right"><?= number_format((float)$over90Total, 2) ?></td>
      <td class="text-right"><?= number_format((float)$grandTotal, 2) ?></td>
    </tr>

  <?php endif; ?>

</tbody>