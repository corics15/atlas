<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllSalesOrder">
        <label class="custom-control-label" for="chkSelectAllSalesOrder"></label>
      </div>
    </th>
    <th class="text-center">Date</th>
    <th class="text-center">SO No.</th>
    <th>Customer</th>
    <th>Salesman</th>
    <th class="text-center">Terms</th>
    <th>Remarks</th>
    <th class="text-center">Remaining</th>
    <th width="110" class="text-center">Status</th>
  </tr>
</thead>
<tbody>
  <?php if (count($salesOrders) == 0): ?>
    <tr>
      <td colspan="9" class="text-center text-muted py-3">
        No Sales Order found.
      </td>
    </tr>
  <?php endif; ?>

  <?php foreach ($salesOrders as $row): ?>
    <tr
      class="stock-transfer-row"
      data-id="<?= $row->id ?>"
      data-status="<?= $row->status ?>"
      data-remaining-items="<?= $row->remaining_items ?>">
      <td class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkSalesOrder" id="chkSalesOrder-<?= $row->id ?>" value="<?= $row->id ?>">
          <label class="custom-control-label" for="chkSalesOrder-<?= $row->id ?>"></label>
        </div>
      </td>
      <td class="text-center">
        <?= date('m/d/Y', strtotime($row->order_date)) ?>
      </td>
      <td class="text-center">
        <a href="<?= base_url('sales-orders/edit/').$row->id ?>" class="text-wrap text-olive"><?= $row->so_no ?></a>
      </td>
      <td>
        <?= htmlspecialchars($row->customer_name) ?>
      </td>
      <td>
        <?= htmlspecialchars($row->salesman_name) ?>
      </td>
      <td class="text-center">
        <?= htmlspecialchars($row->terms_name) ?>
      </td>
      <td>
        <?php
          $remarks = htmlspecialchars($row->remarks);
          echo (mb_strlen($remarks) > 30)
            ? mb_strimwidth($remarks, 0, 30, '...')
            : $remarks;
        ?>
      </td>

      <td class="text-center">
        <?= number_format($row->remaining_items, 0) ?>
      </td>

      <td class="text-center">
        <?php
          $badge = 'secondary';

          switch ($row->status) {
            case 'OPEN':
              $badge = 'secondary';
              break;
            case 'POSTED':
              $badge = 'success';
              break;
            case 'COMPLETED':
              $badge = 'primary';
              break;
            case 'CANCELLED':
              $badge = 'danger';
              break;
          }
          ?>
        <span class="badge badge-<?= $badge ?>">
        <?= $row->status ?>
        </span>
      </td>
    </tr>
    <?php endforeach; ?>
</tbody>