<thead class="thead-orange">
  <tr>
    <th width="30" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input"
          id="idHere">
        <label
          class="custom-control-label"
          for="idHere">
        </label>
      </div>
    </th>
    <th width="160">Adjustment No.</th>
    <th width="120">Date</th>
    <th>Remarks</th>
    <th width="120" class="text-center">Status</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($inventoryAdjustments)): ?>
  <tr>
    <td colspan="5" class="text-center text-muted py-3">
      No inventory adjustments found.
    </td>
  </tr>
  <?php else: ?>
  <?php foreach ($inventoryAdjustments as $row): ?>
  <tr data-id="<?= $row->id; ?>">
    <td class="text-center">
      <input
        type="checkbox"
        class="row-check">
    </td>
    <td>
      <a href="<?= base_url('inventory_adjustments/view/'.$row->id) ?>">
        <?= htmlspecialchars($row->adjustment_no); ?>
      </a>
    </td>
    <td><?= htmlspecialchars($row->adjustment_date); ?></td>
    <td><?= htmlspecialchars($row->remarks); ?></td>
    <td class="text-center">
      <?= htmlspecialchars($row->status); ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php endif; ?>
</tbody>