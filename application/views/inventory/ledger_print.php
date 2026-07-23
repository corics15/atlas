<?php
  $this->load->view('reports/header');

  $totalIn = 0;
  $totalOut = 0;
  $rowNo = 1;
?>

<h3 style="margin-bottom:8px;">
  PRODUCT INFORMATION
</h3>

<table class="summary-table report-borderless">
  <tr>
    <td width="18%"><strong>Barcode</strong></td>
    <td width="32%"><?= htmlspecialchars($product->barcode) ?></td>
    <td width="18%"><strong>Supplier</strong></td>
    <td><?= htmlspecialchars($product->supplier_name) ?></td>
  </tr>

  <tr>
    <td><strong>Description</strong></td>
    <td><?= htmlspecialchars($product->description) ?></td>
    <td><strong>Package</strong></td>
    <td><?= htmlspecialchars($product->pkg) ?></td>
  </tr>

  <tr>
    <td><strong>UOM</strong></td>
    <td><?= htmlspecialchars($product->uom) ?></td>
    <td><strong>On Hand</strong></td>
    <td>
      <?= number_format($product->qty_on_hand) ?>
    </td>
  </tr>

  <tr>
    <td><strong>Cost</strong></td>
    <td>
      <?= number_format($product->cost, 2) ?>
    </td>
    <td><strong>Selling Price</strong></td>
    <td>
      <?= number_format($product->selling_price, 2) ?>
    </td>
  </tr>

</table>

<hr style="margin-top:8px;">

<h4 style="margin-bottom:2px;">
  Inventory Value: <?= number_format($product->qty_on_hand * $product->cost); ?>
</h4>
<br>

<table class="report-table">
  <thead>
    <tr>
      <th width="5%">#</th>
      <th width="15%">Date</th>
      <th width="15%">Transaction</th>
      <th>Reference No.</th>
      <th width="10%">In</th>
      <th width="10%">Out</th>
      <th width="10%">Balance</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($ledger)): ?>

      <tr>
        <td colspan="7" class="text-center">
          No transactions found.
        </td>
      </tr>

    <?php else: ?>

      <?php foreach ($ledger as $row): ?>
        <?php
          $totalIn += $row->qty_in;
          $totalOut += $row->qty_out;
        ?>
        <tr>
          <td class="text-center">
            <?= $rowNo++.'.'; ?>
          </td>
          <td class="text-center">
            <?= date('m/d/Y', strtotime($row->transaction_date)); ?>
          </td>
          <td class="text-center">
            <?= htmlspecialchars($row->transaction_type); ?>
          </td>
          <td class="text-center">
            <?= htmlspecialchars($row->reference_no); ?>
          </td>
          <td class="text-right">
            <?= $row->qty_in > 0 ? number_format($row->qty_in) : ''; ?>
          </td>
          <td class="text-right">
            <?= $row->qty_out > 0 ? number_format($row->qty_out) : ''; ?>
          </td>
          <td class="text-right">
            <?= number_format($row->balance_after); ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>

  </tbody>

  <tfoot>
    <tr>
      <th colspan="4" class="text-right">
        TOTAL MOVEMENTS
      </th>
      <th class="text-right">
        <?= number_format($totalIn); ?>
      </th>
      <th class="text-right">
        <?= number_format($totalOut); ?>
      </th>
      <th class="text-right">
        <?= number_format($product->qty_on_hand); ?>
      </th>
    </tr>
  </tfoot>
</table>

<?php
  $this->load->view('reports/footer');
  $this->load->view('reports/scripts');
?>