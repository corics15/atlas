<thead class="thead-orange">
  <tr>
    <th width="50" class="text-cener">#</th>
    <th width="120" class="text-center">Barcode</th>
    <th>Description</th>
    <th width="80" class="text-center">UOM</th>
    <th width="90" class="text-right">Received</th>
    <th width="90" class="text-right">Returned</th>
    <th width="100" class="text-right">Available</th>
    <th width="120" class="text-right">Return Qty</th>
  </tr>
</thead>

<tbody>
  <?php foreach ($details as $index => $item): ?>
  <tr
    data-goods-receipt-detail-id="<?= $item->goods_receipt_detail_id ?>"
    data-product-id="<?= $item->product_id ?>"
    data-available-qty="<?= $item->qty ?>">

    <td class="text-right"><?= $index + 1 ?>.</td>
    <td class="text-center"><?= htmlspecialchars($item->barcode) ?></td>
    <td><?= htmlspecialchars($item->description) ?></td>
    <td class="text-center"><?= htmlspecialchars($item->uom) ?></td>
    <td class="text-right"><?= number_format($item->qty_received) ?></td>
    <td class="text-right"><?= number_format($item->qty_returned) ?></td>
    <td class="text-right"><?= number_format($item->qty) ?></td>
    <td>
      <input
        type="number"
        class="form-control form-control-sm text-right pr-return-qty"
        value="0"
        min="0"
        max="<?= $item->qty ?>"
        step="any"
        <?= !empty($purchaseReturn) ? 'readonly' : '' ?>>
    </td>
  </tr>
  <?php endforeach; ?>
</tbody>