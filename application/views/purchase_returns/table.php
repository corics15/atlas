<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllPurchaseReturn">
        <label class="custom-control-label" for="chkSelectAllPurchaseReturn"></label>
      </div>
    </th>
    <th class="text-center">PR No.</th>
    <th class="text-center">Return Date</th>
    <th class="text-center">GRN No.</th>
    <th class="text-center">GRN Date</th>
    <th>Supplier</th>
    <th>Remarks</th>
    <th width="110" class="text-center">Status</th>
  </tr>
</thead>
<tbody>
  <?php if (count($purchaseReturns) == 0): ?>
    <tr>
      <td colspan="8" class="text-center text-muted py-3">
        No Purchase Return found.
      </td>
    </tr>
  <?php endif; ?>

  <?php foreach ($purchaseReturns as $row): ?>
    <tr
      class="sales-transfer-row"
      data-id="<?= $row->id ?>">
      <td class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkPurchaseReturn" id="chkPurchaseReturn-<?= $row->id ?>" value="<?= $row->id ?>">
          <label class="custom-control-label" for="chkPurchaseReturn-<?= $row->id ?>"></label>
        </div>
      </td>
      <td class="text-center">
        <a href="<?= $row->url ?>" class="text-wrap text-olive"><?= $row->pr_no ?></a>
      </td>
      <td class="text-center">
        <?= date('m/d/Y', strtotime($row->return_date)) ?>
      </td>
      <td class="text-center">
        <a href="<?= $row->gr_url  ?>" class="text-wrap text-olive" target="_blank"><i class="fa-external-link-alt fas fa-xs mr-1"></i><?= $row->grn_no ?></a>
      </td>
      <td class="text-center">
        <?= date('m/d/Y', strtotime($row->grn_date)) ?>
      </td>
      <td>
        <?= htmlspecialchars($row->supplier_name) ?>
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