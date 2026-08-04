<thead class="thead-orange">
  <tr>
      <th width="40" class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input
              type="checkbox"
              class="custom-control-input"
              id="chkSelectAllDeliveryReceipt"/>
          <label class="custom-control-label" for="chkSelectAllDeliveryReceipt"></label>
        </div>
      </th>

      <th width="140" class="text-center">DR No.</th>
      <th width="150" class="text-center">SO No.</th>
      <th width="120" class="text-center">Delivery Date</th>
      <th>Customer</th>
      <th width="120" class="text-center">Status</th>
  </tr>
</thead>

<tbody>
  <?php if (empty($deliveryReceipts)): ?>

  <tr>
      <td colspan="6" class="text-center text-muted py-3">
        No Delivery Receipts found.
      </td>
  </tr>

  <?php else: ?>
    <?php foreach ($deliveryReceipts as $row): ?>
      <?php
        switch ($row->status) {
          case 'OPEN':
            $status = '<span class="badge badge-secondary">OPEN</span>';
          break;
          case 'POSTED':
            $status = '<span class="badge badge-success">POSTED</span>';
          break;
          default:
            $status = '<span class="badge badge-danger">CANCELLED</span>';
          break;
        }
      ?>

      <tr data-id="<?= $row->id; ?>" data-status="<?= $row->status; ?>">
        <td class="text-center">
          <div class="custom-control custom-checkbox ml-2 mt-1">
            <input
                type="checkbox"
                class="custom-control-input chkDeliveryReceipt"
                id="chkDeliveryReceipt-<?= $row->id; ?>"
                value="<?= $row->id; ?>"/>
            <label class="custom-control-label" for="chkDeliveryReceipt-<?= $row->id; ?>"></label>
          </div>
        </td>

        <td class="text-center">
          <a href="<?= base_url('delivery-receipts/edit/' . $row->id); ?>" class="text-wrap text-olive">
            <?= htmlspecialchars($row->dr_no); ?>
          </a>
        </td>

        <td class="text-center">
          <a href="<?= base_url('sales-orders/edit/' . $row->so_id); ?>" class="text-wrap text-olive" target="_blank">
            <i class="fa-external-link-alt fas fa-xs mr-1"></i><?= htmlspecialchars($row->so_no); ?>
          </a>
        </td>

        <td class="text-center">
          <?= date('m/d/Y', strtotime($row->delivery_date)); ?>
        </td>

        <td><?= htmlspecialchars($row->customer_name); ?></td>
        <td class="text-center"><?= $status; ?></td>
      </tr>

    <?php endforeach; ?>
  <?php endif; ?>
</tbody>