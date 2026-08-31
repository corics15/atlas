<thead class="thead-orange">
  <tr>
    <th class="text-center">Date</th>
    <th>Supplier</th>
    <th class="text-center" data-a-h="center">Barcode</th>
    <th>Product Description</th>
    <th class="text-center" data-a-h="center">DR No.</th>
    <th class="text-center" data-a-h="center">UOM</th>
    <th class="text-right" data-a-h="center">Qty</th>
    <th class="text-right">Selling Price</th>
    <th class="text-right">Gross Price</th>
    <th class="text-right">Disc. %</th>
    <th class="text-right">Discount</th>
    <th class="text-right">Net Amt.</th>
    <th class="text-center" data-a-h="center">Outlet Type</th>
    <th class="text-center" data-a-h="center">Salesman Code</th>
    <th>Salesman</th>
    <th>Customer</th>
    <th>Address</th>
  </tr>
</thead>

<tbody>
  <?php if (count($salesDetails) == 0): ?>
    <tr>
      <td colspan="17" class="text-center text-muted py-3">
        No Sales Detail found.
      </td>
    </tr>
  <?php endif; ?>

  <?php foreach ($salesDetails as $row): ?>
    <tr>
      <td class="text-center"><?= date('m/d/Y', strtotime($row->dr_date)) ?></td>
      <td><?= htmlspecialchars($row->supplier_name ?? '') ?></td>
      <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->barcode ?? '') ?>
      <td><?= htmlspecialchars($row->description ?? '') ?></td>
      <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->dr_no ?? '') ?></td>
      <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->uom ?? '') ?></td>
      <td class="text-right" data-a-h="center" data-t="n" data-num-fmt="#,##0"><?= number_format((float) $row->qty, 0) ?></td>
      <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format((float) $row->unit_price, 2) ?></td>
      <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format((float) $row->gross_amount, 2) ?></td>
      <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format((float) $row->discount_percent, 2) ?></td>
      <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format((float) $row->discount_amount, 2) ?></td>
      <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format((float) $row->net_amount, 2) ?></td>
      <td class="text-center"><?= htmlspecialchars($row->outlet_type_name ?? '') ?></td>
      <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->salesman_code ?? '') ?></td>
      <td><?= htmlspecialchars($row->salesman_name ?? '') ?></td>
      <td><?= htmlspecialchars($row->customer_name ?? '') ?></td>
      <td><?= htmlspecialchars($row->address ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
</tbody>