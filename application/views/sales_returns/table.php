<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllSalesReturn">
        <label class="custom-control-label" for="chkSelectAllSalesReturn"></label>
      </div>
    </th>
    <th class="text-center">Date</th>
    <th class="text-center">SR No.</th>
    <th class="text-center">SI No.</th>
    <th>Customer</th>
    <th>Salesman</th>
    <th class="text-center">Terms</th>
    <th>Remarks</th>
    <th width="110" class="text-center">Status</th>
  </tr>
</thead>
<tbody>
  <?php if (count($salesReturns) == 0): ?>
    <tr>
      <td colspan="9" class="text-center text-muted py-3">
        No Sales Return found.
      </td>
    </tr>
  <?php endif; ?>

  <?php foreach ($salesReturns as $row): ?>
    <tr
      class="sales-transfer-row"
      data-id="<?= $row->id ?>">
      <td class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkSalesReturn" id="chkSalesReturn-<?= $row->id ?>" value="<?= $row->id ?>">
          <label class="custom-control-label" for="chkSalesReturn-<?= $row->id ?>"></label>
        </div>
      </td>
      <td class="text-center">
        <?= date('m/d/Y', strtotime($row->return_date)) ?>
      </td>
      <td class="text-center">
        <a href="<?= base_url('sales-returns/edit/').$row->id ?>" class="text-wrap text-olive"><?= $row->sr_no ?></a>
      </td>
      <td class="text-center">
        <a href="<?= base_url('sales-invoices/edit/').$row->sales_invoice_id  ?>" class="text-wrap text-olive" target="_blank">
          <i class="fas fa-external-link-alt fa-xs mr-1"></i>
          <?= $row->si_no ?>
        </a>
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
        <?= htmlspecialchars($row->remarks) ?>
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