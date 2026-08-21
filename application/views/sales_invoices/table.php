<thead class="thead-orange">
  <tr>
    <th width="40" class="text-center">
      <div class="custom-control custom-checkbox ml-2 mt-1">
        <input type="checkbox" class="custom-control-input" id="chkSelectAllSalesInvoice">
        <label class="custom-control-label" for="chkSelectAllSalesInvoice"></label>
      </div>
    </th>
    <th class="text-center">Date</th>
    <th class="text-center" width="140">SI No.</th>
    <th class="text-center" width="150">DR No.</th>
    <th>Customer</th>
    <th>Salesman</th>
    <th class="text-center">Terms</th>
    <th>Remarks</th>
    <th width="110" class="text-center">Status</th>
  </tr>
</thead>
<tbody>
  <?php if (count($salesInvoices) == 0): ?>
    <tr>
      <td colspan="9" class="text-center text-muted py-3">
        No Sales Invoice found.
      </td>
    </tr>
  <?php endif; ?>

  <?php foreach ($salesInvoices as $row): ?>
    <tr
      class="stock-transfer-row"
      data-id="<?= $row->id ?>">
      <td class="text-center">
        <div class="custom-control custom-checkbox ml-2 mt-1">
          <input type="checkbox" class="custom-control-input chkSalesInvoice" id="chkSalesInvoice-<?= $row->id ?>" value="<?= $row->id ?>">
          <label class="custom-control-label" for="chkSalesInvoice-<?= $row->id ?>"></label>
        </div>
      </td>
      <td class="text-center">
        <?= date('m/d/Y', strtotime($row->invoice_date)) ?>
      </td>
      <td class="text-center">
        <a href="<?= $row->url ?>" class="text-olive"><?= $row->si_no ?></a>
      </td>
      <td class="text-center">
        <a href="<?= $row->dr_url ?>" class="text-olive" target="_blank">
          <i class="fa-external-link-alt fas fa-xs mr-1"></i><?= $row->dr_no ?>
        </a>
      </td>
      <td>
        <?php
          $customerName = htmlspecialchars($row->customer_name);
          echo (mb_strlen($customerName) > 30)
            ? mb_strimwidth($customerName, 0, 30, '...')
            : $customerName;
        ?>
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