<thead class="thead-orange">
  <tr>
    <th>Customer</th>
    <th>Salesman</th>
    <th class="text-right">Invoices</th>
    <th class="text-right">Gross Sales</th>
    <th class="text-right">Discount</th>
    <th class="text-right">Net Sales</th>
  </tr>
</thead>

<tbody>
  <?php if (!empty($salesPerCustomerSalesman)): ?>
    <?php
      $grossTotal = 0;
      $discountTotal = 0;
      $netTotal = 0;
    ?>
    <?php foreach ($salesPerCustomerSalesman as $row): ?>

      <?php
        $grossSales = (float)$row->gross_sales;
        $discountAmount = (float)$row->discount_amount;
        $netSales = (float)$row->net_sales;
        $grossTotal += $grossSales;
        $discountTotal += $discountAmount;
        $netTotal += $netSales;
      ?>
      <tr>
        <td><?= htmlspecialchars($row->customer_name); ?></td>
        <td><?= htmlspecialchars($row->salesman_name); ?></td>
        <td class="text-right" data-t="n" data-num-fmt="#,##0"><?= number_format((int)$row->invoice_count); ?></td>
        <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format($grossSales, 2); ?></td>
        <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format($discountAmount, 2); ?></td>
        <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format($netSales, 2); ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="6" class="text-center text-muted py-3">
        No sales found.
      </td>
    </tr>
  <?php endif; ?>
</tbody>

<?php if (!empty($salesPerCustomerSalesman)): ?>
  <tfoot class="font-weight-500 text-info text-right">
    <tr>
      <td colspan="2" data-f-bold="true" data-a-h="right">Grand Total</td>
      <td></td>
      <td data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($grossTotal, 2); ?></td>
      <td data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($discountTotal, 2); ?></td>
      <td data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($netTotal, 2); ?></td>
    </tr>
  </tfoot>
<?php endif; ?>