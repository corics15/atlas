<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      Goods Receipt Information
    </h3>
  </div>
  <div class="card-body">
    <table class="table table-sm table-borderless">
      <tr>
        <th width="180">GRN No.</th>
        <td><?= htmlspecialchars($goodsReceipt->grn_no) ?></td>
      </tr>
      <tr>
        <th>Date</th>
        <td><?= htmlspecialchars($goodsReceipt->grn_date) ?></td>
      </tr>
      <tr>
        <th>Purchase Order</th>
        <td><?= htmlspecialchars($goodsReceipt->po_no) ?></td>
      </tr>
      <tr>
        <th>Supplier</th>
        <td><?= htmlspecialchars($goodsReceipt->supplier_name) ?></td>
      </tr>
      <tr>
        <th>Remarks</th>
        <td><?= nl2br(htmlspecialchars($goodsReceipt->remarks)) ?></td>
      </tr>
    </table>
  </div>
</div>