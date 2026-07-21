<thead class="thead-orange">
  <tr>
    <th width="30" class="text-center">
      <div class="custom-checkbox custom-control ml-2 mt-1">
        <input
          type="checkbox"
          class="custom-control-input chkInventoryAdjustment"
          id="idHere">
        <label
          class="custom-control-label"
          for="idHere">
        </label>
      </div>
    </th>
    <th width="160" class="text-center">Adjustment No.</th>
    <th width="120" class="text-center">Date</th>
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
        <div class="custom-checkbox custom-control ml-2 mt-1">
          <input
            type="checkbox"
            class="custom-control-input chkInventoryAdjustment"
            id="ckia-<?= $row->id ?>">
          <label
            class="custom-control-label"
            for="ckia-<?= $row->id ?>">
          </label>
        </div>
      </td>
      <td class="text-center">
        <a href="<?= base_url('inventory_adjustments/view/'.$row->id) ?>" class="text-olive text-wrap">
          <?= htmlspecialchars($row->adjustment_no); ?>
        </a>
      </td>
      <td class="text-center"><?= date('m/d/Y', strtotime(htmlspecialchars($row->adjustment_date))); ?></td>
      <td><?= htmlspecialchars($row->remarks); ?></td>
      <td class="text-center">
        <?php
          switch (htmlspecialchars($row->status)) {
            case 'DRAFT':
              $status = '<span class="badge badge-secondary">DRAFT</span>';
              break;

            case 'POSTED':
              $status = '<span class="badge badge-success">POSTED</span>';
              break;

            default:
              $status = '<span class="badge badge-danger">CANCELLED</span>';
              break;
          }
          echo $status;
        ?>
      </td>
    </tr>
    <?php endforeach; ?>

  <?php endif; ?>
</tbody>