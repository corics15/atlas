<thead class="thead-orange">
  <tr>
    <th width="120" class="text-center">Date</th>
    <th width="180" class="text-center">Reference</th>
    <th>Description</th>
    <th width="140" class="text-right">Charges</th>
    <th width="140" class="text-right">Credits</th>
    <th width="140" class="text-right">Balance</th>
  </tr>
</thead>

<tbody>

  <?php if ((float)$openingBalance != 0): ?>

    <tr>
      <td></td>
      <td></td>

      <td class="font-weight-500">
        Previous Balance (Forwarded)
      </td>

      <td></td>
      <td></td>

      <td class="text-right font-weight-500">
        <?= number_format((float)$openingBalance, 2) ?>
      </td>
    </tr>

  <?php endif; ?>

  <?php if (empty($transactions)): ?>

    <tr>
      <td
        colspan="6"
        class="text-center text-muted py-3">
        No transactions found for the selected period.
      </td>
    </tr>

  <?php else: ?>

    <?php foreach ($transactions as $row): ?>

      <?php
        $description = '';

        if ($row->transaction_type === 'SALES INVOICE') {
          $description = 'Sales Invoice';
        } elseif ($row->transaction_type === 'CUSTOMER PAYMENT') {
          $description = 'Payment Received';
        } else {
          $description = $row->transaction_type;
        }
      ?>

      <tr>

        <td class="text-center">
          <?= date(
            'm/d/Y',
            strtotime($row->transaction_date)
          ) ?>
        </td>

        <td class="text-center">
          <?= htmlspecialchars($row->reference_no) ?>
        </td>

        <td>
          <?= htmlspecialchars($description) ?>
        </td>

        <td class="text-right">
          <?= (float)$row->debit > 0
            ? number_format((float)$row->debit, 2)
            : '' ?>
        </td>

        <td class="text-right">
          <?= (float)$row->credit > 0
            ? number_format((float)$row->credit, 2)
            : '' ?>
        </td>

        <td class="text-right font-weight-500">
          <?= number_format((float)$row->balance, 2) ?>
        </td>

      </tr>

    <?php endforeach; ?>

  <?php endif; ?>

</tbody>