<thead class="thead-orange">
  <tr>
    <th width="50" class="text-cener">#</th>
    <th width="120" class="text-center">Barcode</th>
    <th>Description</th>
    <th width="80" class="text-center">UOM</th>
    <th width="110" class="text-right">Conversion</th>
    <th width="90" class="text-right">Ordered</th>
    <th width="90" class="text-right">Received</th>
    <th width="100" class="text-right">Remaining</th>
    <th width="120" class="text-right">Receive Now</th>
  </tr>
</thead>

<tbody>
  <?php foreach ($purchaseOrder['details'] as $index => $item): ?>
  <tr
    data-po-detail-id="<?= $item->id ?>"
    data-product-id="<?= $item->product_id ?>"
    data-uom-id="<?= $item->uom_id ?>"
    data-base-uom-id="<?= $item->base_uom_id ?>"
    data-conversion-factor="<?= $item->conversion_factor !== NULL ? $item->conversion_factor : '' ?>"
    data-original-conversion="<?= $item->conversion_factor !== NULL ? $item->conversion_factor : '' ?>"
    data-ordered-qty="<?= $item->qty ?>"
    data-unit-cost="<?= $item->price ?>"
    data-remaining-qty="<?= $item->qty_remaining ?>">

    <td class="text-center">
      <?= $index + 1 ?>.
    </td>
    <td class="text-center"><?= htmlspecialchars($item->barcode) ?></td>
    <td><?= htmlspecialchars($item->description) ?></td>
    <td class="text-center"><?= htmlspecialchars($item->uom) ?></td>
    <td>
      <div class="input-group input-group-sm">
        <input type="number" class="form-control form-control-sm text-right grn-conversion" value="<?= $item->conversion_factor !== NULL ? $item->conversion_factor : '' ?>" min="0.0001" step="any"
          <?= $item->conversion_factor !== NULL ? 'readonly' : '' ?>>

        <?php if ($item->conversion_factor !== NULL && (int)$item->uom_id !== (int)$item->base_uom_id): ?>

          <div class="input-group-append">
            <button type="button" class="btn btn-outline-warning btn-link btn-change-conversion" title="Change Conversion">
              <i class="fas fa-edit"></i>
            </button>
          </div>

        <?php endif; ?>
      </div>
    </td>
    <td class="text-right"><?= number_format($item->qty) ?></td>
    <td class="text-right"><?= number_format($item->qty_received) ?></td>
    <td class="text-right"><?= number_format($item->qty_remaining) ?></td>
    <td>
      <input type="number" class="form-control form-control-sm text-right grn-receive-now" value="0" min="0" max="<?= $item->qty_remaining ?>" step="any">
    </td>
  </tr>
  <?php endforeach; ?>
</tbody>