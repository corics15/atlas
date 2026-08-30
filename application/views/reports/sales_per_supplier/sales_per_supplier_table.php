<thead class="thead-orange">
  <tr>
    <th>Supplier</th>
    <th class="text-right">Invoices</th>
    <th class="text-right">Gross Sales</th>
    <th class="text-right">Discount</th>
    <th class="text-right">Net Sales</th>
  </tr>
</thead>

<tbody>
  <?php if (!empty($salesPerSupplier)): ?>
    <?php
      $grossTotal = 0;
      $discountTotal = 0;
      $netTotal = 0;
    ?>

    <?php foreach ($salesPerSupplier as $row): ?>
      <?php
        $grossSales = (float)$row->gross_sales;
        $discountAmount = (float)$row->discount_amount;
        $netSales = (float)$row->net_sales;

        $grossTotal += $grossSales;
        $discountTotal += $discountAmount;
        $netTotal += $netSales;
      ?>
      <tr>
        <td><a href="#" class="js-supplier-drilldown text-olive" data-supplier-id="<?= (int)$row->supplier_id ?>"><?= htmlspecialchars($row->supplier_name); ?></a></td>
        <td class="text-right"><?= number_format((int)$row->invoice_count); ?></td>
        <td class="text-right"><?= number_format($grossSales, 2); ?></td>
        <td class="text-right"><?= number_format($discountAmount, 2); ?></td>
        <td class="text-right font-weight-500"><?= number_format($netSales, 2); ?></td>
      </tr>
    <?php endforeach; ?>

  <?php else: ?>
    <tr>
      <td colspan="5" class="text-center text-muted py-3">
        <?php if (!empty($date_from) && !empty($date_to)): ?>
          No posted Sales Invoice sales found for the selected period.
        <?php else: ?>
          Select a date range and click Generate.
        <?php endif; ?>
      </td>
    </tr>
  <?php endif; ?>

</tbody>

<?php if (!empty($salesPerSupplier)): ?>
  <tfoot class="font-weight-500 text-info text-right">
    <tr>
      <td>Grand Total</td>
      <td class="text-right">-</td>
      <td class="text-right"><?= number_format($grossTotal, 2); ?></td>
      <td class="text-right"><?= number_format($discountTotal, 2); ?></td>
      <td class="text-right"><?= number_format($netTotal, 2); ?></td>
    </tr>
  </tfoot>
<?php endif; ?>