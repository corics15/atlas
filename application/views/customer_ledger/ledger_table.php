<thead class="thead-orange">
  <tr>
    <th width="120" class="text-center">Date</th>
    <th width="180" class="text-center">Reference</th>
    <th class="text-center">Transaction</th>
    <th width="140" class="text-right">Debit</th>
    <th width="140" class="text-right">Credit</th>
    <th width="140" class="text-right">Balance</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($ledger)): ?>
    <tr>
      <td colspan="6" class="text-center text-muted py-3">
        No ledger transactions found.
      </td>
    </tr>
  <?php else: ?>
    <?php foreach ($ledger as $row): ?>
      <?php
        $url = NULL;
        switch ($row->transaction_type) {
          case 'SALES INVOICE':
            $url = $row->si_url;
            break;
          case 'CUSTOMER PAYMENT':
            $url = $row->cp_url;
            break;
          default:
            $url = 'javascript:void(0)';
            break;
        }
      ?>
      <tr>
        <td class="text-center"><?= date('m/d/Y', strtotime($row->transaction_date)) ?></td>
        <td class="text-center">
          <?php if ($url): ?>
            <a href="<?= $url ?>" class="text-olive" target="_blank">
              <i class="fas fa-external-link-alt fa-xs mr-1"></i>
              <?= htmlspecialchars($row->reference_no) ?>
            </a>
          <?php else: ?>
            <?= htmlspecialchars($row->reference_no) ?>
          <?php endif; ?>
        </td>
        <td class="text-center"><?= htmlspecialchars($row->transaction_type) ?></td>
        <td class="text-right"><?= (float)$row->debit > 0 ? number_format((float)$row->debit, 2) : '' ?></td>
        <td class="text-right"><?= (float)$row->credit > 0 ? number_format((float)$row->credit, 2) : '' ?></td>
        <td class="text-right font-weight-500"><?= number_format((float)$row->balance, 2) ?></td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
</tbody>