<thead class="thead-orange">
  <tr>
    <th>Customer</th>
    <th class="text-right">Invoices</th>
    <th class="text-right">Gross Sales</th>
    <th class="text-right">Discount</th>
    <th class="text-right">Net Sales</th>
  </tr>
</thead>

<tbody>
  <?php if (!empty($salesPerCustomer)): ?>
    <?php
      $grossTotal = 0;
      $discountTotal = 0;
      $netTotal = 0;
    ?>

    <?php foreach ($salesPerCustomer as $row): ?>
      <?php
        $grossSales = (float)$row->gross_sales;
        $discountAmount = (float)$row->discount_amount;
        $netSales = (float)$row->net_sales;

        $grossTotal += $grossSales;
        $discountTotal += $discountAmount;
        $netTotal += $netSales;
      ?>
      <tr>
        <td><a href="#" class="js-customer-drilldown text-olive" data-customer-id="<?= (int)$row->customer_id; ?>"><?= htmlspecialchars($row->customer_name); ?></a></td>
        <td class="text-right"><?= number_format((int)$row->invoice_count); ?></td>
        <td class="text-right"><?= number_format($grossSales, 2); ?></td>
        <td class="text-right"><?= number_format($discountAmount, 2); ?></td>
        <td class="text-right font-weight-500"><?= number_format($netSales, 2); ?></td>
      </tr>
    <?php endforeach; ?>

  <?php else: ?>
    <tr>
      <td colspan="5" class="text-center text-muted py-3">
        No sales found.
      </td>
    </tr>
  <?php endif; ?>
</tbody>

<?php if (!empty($salesPerCustomer)): ?>
  <tfoot class="font-weight-500 text-info text-right">
    <tr>
      <td>Grand Total</td>
      <td>-</td>
      <td><?= number_format($grossTotal, 2); ?></td>
      <td><?= number_format($discountTotal, 2); ?></td>
      <td><?= number_format($netTotal, 2); ?></td>
    </tr>
  </tfoot>
<?php endif; ?>