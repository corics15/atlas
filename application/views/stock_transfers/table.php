<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllStockTransfer">
        <label class="custom-control-label" for="chkSelectAllStockTransfer"></label>
      </div>
    </th>
    <th class="text-center">Date</th>
    <th class="text-center">Transfer No.</th>
    <th>From Branch</th>
    <th>To Branch</th>
    <th>Remarks</th>
    <th width="110" class="text-center">Status</th>
  </tr>
</thead>
<tbody>
  <?php if (count($stockTransfers) == 0): ?>
    <tr>
      <td colspan="7" class="text-center text-muted py-3">
        No Stock Transfers found.
      </td>
    </tr>
  <?php endif; ?>

  <?php foreach ($stockTransfers as $row): ?>
    <tr
      class="stock-transfer-row"
      data-id="<?= $row->id ?>">
      <td class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkStockTransfer" id="chkStockTransfer-<?= $row->id ?>" value="<?= $row->id ?>">
          <label class="custom-control-label" for="chkStockTransfer-<?= $row->id ?>"></label>
        </div>
      </td>
      <td class="text-center">
        <?= date('m/d/Y', strtotime($row->transfer_date)) ?>
      </td>
      <td class="text-center">
        <a href="<?= base_url('stock_transfers/edit/').$row->id ?>" class="text-wrap text-olive"><?= $row->transfer_no ?></a>
      </td>
      <td>
        <?= $row->from_branch ?>
      </td>
      <td>
        <?= $row->to_branch ?>
      </td>
      <td>
        <?= $row->remarks ?>
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