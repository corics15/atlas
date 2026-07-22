<thead class="thead-orange">
  <tr>
    <th class="text-center">Date</th>
    <th class="text-center">Type</th>
    <th class="text-center">Reference</th>
    <th class="text-right">In</th>
    <th class="text-right">Out</th>
    <th class="text-right">Balance</th>
  </tr>
</thead>
<tbody>
  <?php if (!empty($ledger)) : ?>
  <?php $previousBalance = null; ?>
  <?php foreach ($ledger as $row) : ?>
  <tr>
    <td class="text-center"><?= date('m/d/Y h:i A', strtotime($row->transaction_date)); ?></td>

    <td class="text-center">
      <?php
      $badgeClass = [
        'GRN'        => 'badge-success',
        'SALE'       => 'badge-danger',
        'ADJUSTMENT' => 'badge-warning',
        'TRANSFER'   => 'badge-info',
        'RETURN'     => 'badge-primary',
        'COUNT'      => 'badge-secondary',
      ];
      ?>

      <span class="badge <?= $badgeClass[$row->transaction_type] ?? 'badge-light'; ?>">
        <?= htmlspecialchars($row->transaction_type); ?>
      </span>
    </td>

    <td class="text-center">
      <a
        href="#"
        class="text-olive text-wrap"
        data-toggle="tooltip" title="Open Transaction">
        <i class="fas fa-external-link-alt fa-xs mr-1"></i>
        <?= htmlspecialchars($row->reference_no); ?>
      </a>
    </td>

    <td class="text-right">
      <?= $row->qty_in > 0 ? number_format($row->qty_in) : ''; ?>
    </td>

    <td class="text-right">
      <?= $row->qty_out > 0 ? number_format($row->qty_out) : ''; ?>
    </td>

    <?php
        $balanceClass = '';
        if ($row->balance_after < 0) {
          $balanceClass = 'text-danger font-weight-bold';
        }

        $trend = '';
        if ($previousBalance !== null) {
          if ($row->balance_after > $previousBalance) {
            $trend = '<i class="fas fa-arrow-up text-success mr-1"></i>';
          } elseif ($row->balance_after < $previousBalance) {
            $trend = '<i class="fas fa-arrow-down text-danger mr-1"></i>';
          }
        }
        $previousBalance = $row->balance_after;
      ?>
    <td class="text-right <?= $balanceClass; ?>">
      <?= $trend .' '.number_format($row->balance_after); ?>
    </td>

  </tr>
  <?php endforeach; ?>
  <?php else : ?>
  <tr>
    <td colspan="6" class="text-center">
      No inventory transactions found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>