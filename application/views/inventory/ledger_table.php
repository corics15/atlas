<thead class="thead-orange">
  <tr>
    <th width="60" class="text-center">#</th>
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

  <?php
    $previousBalance = null;
    $index = 1;
    $totalIn = 0;
    $totalOut = 0;
  ?>

  <?php foreach ($ledger as $row) : ?>
  <tr>
    <td class="text-center"><?= $index.'.' ?></td>
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
      <?php
        switch ($row->transaction_type) {
          case 'GRN':
            $url = 'goods_receipts/';
            break;
          
          default: /*** ADJUSTMENT */
            $url = 'inventory_adjustments/';
            break;
        }
      ?>
      <a
        href="<?= base_url($url.'view/'.$row->reference_id) ?>"
        class="text-olive text-wrap"
        data-toggle="tooltip" title="Open Transaction" target="_blank">
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
  <?php
      $index++;
      $totalIn += (float)$row->qty_in;
      $totalOut += (float)$row->qty_out;
    endforeach;
  ?>
  <?php else : ?>
  <tr>
    <td colspan="6" class="text-center">
      No inventory transactions found.
    </td>
  </tr>
  <?php endif; ?>
</tbody>

<tfoot>
  <tr class="bg-light">
    <td colspan="4" class="font-weight-500 text-right">
        TOTAL
    </td>
    <td class="font-weight-500 text-right text-success">
        <?= number_format($totalIn); ?>
    </td>
    <td class="font-weight-500 text-right text-danger">
        <?= number_format($totalOut); ?>
    </td>
    <td></td>
  </tr>
</tfoot>
<input type="hidden" id="hidProductId" value="<?= $product->id ?>">