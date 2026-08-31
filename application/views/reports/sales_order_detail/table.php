<thead class="thead-orange">
  <tr>
    <th>SO Date</th>
    <th>SO No.</th>
    <th>Customer</th>
    <th>Supplier</th>
    <th>Product Description</th>
    <th>Packing</th>
    <th class="text-center" data-a-h="center">UOM</th>
    <th class="text-center" data-a-h="center">Qty</th>
    <th>Salesman</th>
    <th class="text-center" data-a-h="center">Terms</th>
    <th>Remarks</th>
    <th class="text-center" data-a-h="center">Item Count</th>
    <th class="text-right" data-a-h="right">Total Amt</th>
    <th class="text-center" data-a-h="center">Remaining</th>
    <th class="text-center" data-a-h="center">Status</th>
  </tr>
</thead>

<tbody>
  <?php if (!empty($salesOrderDetails)): ?>
    <?php foreach ($salesOrderDetails as $row): ?>
      <tr>
        <td><?= date('m/d/Y', strtotime(htmlspecialchars($row->order_date))); ?></td>
        <td><?= htmlspecialchars($row->so_no); ?></td>
        <td><?= htmlspecialchars($row->customer_name ?? ''); ?></td>
        <td><?= htmlspecialchars($row->supplier_name ?? ''); ?></td>
        <td><?= htmlspecialchars($row->description ?? ''); ?></td>
        <td><?= htmlspecialchars($row->packing ?? ''); ?></td>
        <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->uom ?? ''); ?></td>
        <td class="text-center" data-t="n" data-format="#,##0" data-a-h="center"><?= number_format((float)$row->qty, 0); ?></td>
        <td><?= htmlspecialchars($row->salesman_name ?? ''); ?></td>
        <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->terms ?? ''); ?></td>
        <td><?= htmlspecialchars($row->remarks ?? ''); ?></td>
        <td class="text-center" data-t="n" data-format="#,##0" data-a-h="center"><?= number_format($row->item_count, 0); ?></td>
        <td class="text-right" data-t="n" data-format="#,##0.00"><?= number_format($row->total_amount, 2); ?></td>
        <td class="text-center" data-t="n" data-format="#,##0" data-a-h="center"><?= number_format($row->remaining, 0); ?></td>
        <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->status ?? ''); ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="15" class="text-center text-muted py-3">
        No Sales Order details found.
      </td>
    </tr>
  <?php endif; ?>
</tbody>