<thead class="thead-orange">
  <tr>
    <th width="120" class="text-center">Barcode</th>
    <th>Description</th>
    <th width="80" class="text-center">UOM</th>
    <th width="90" class="text-right">Ordered</th>
    <th width="90" class="text-right">Received</th>
    <th width="100" class="text-right">Remaining</th>
    <th width="120" class="text-right">Receive Now</th>
  </tr>
</thead>

<tbody>
  <?php foreach ($purchaseOrder['details'] as $item): ?>
  <tr
    data-po-detail-id="<?= $item->id ?>"
    data-product-id="<?= $item->product_id ?>"
    data-ordered-qty="<?= $item->qty ?>"
    data-unit-cost="<?= $item->price ?>"
    data-remaining-qty="<?= $item->qty_remaining ?>">

    <td class="text-center"><?= htmlspecialchars($item->barcode) ?></td>
    <td><?= htmlspecialchars($item->description) ?></td>
    <td class="text-center"><?= htmlspecialchars($item->uom) ?></td>
    <td class="text-right"><?= number_format($item->qty, 2) ?></td>
    <td class="text-right"><?= number_format($item->qty_received, 2) ?></td>
    <td class="text-right"><?= number_format($item->qty_remaining, 2) ?></td>
    <td>
      <input
        type="number"
        class="form-control form-control-sm text-right grn-receive-now"
        value="0"
        min="0"
        max="<?= $item->qty_remaining ?>"
        step="0.01">
    </td>
  </tr>
  <?php endforeach; ?>
</tbody>