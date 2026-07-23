<div class="align-items-md-end align-items-start d-flex flex-column flex-md-row justify-content-between">
  <?php $this->load->view('inventory/search_toolbar'); ?>
</div>

<div class="row">
  <div class="col-md-6">
    <table class="table table-sm table-bordered table-hover mb-3">
      <tr>
        <th width="120">Case Barcode</th>
        <td><?= htmlspecialchars($product->case_barcode); ?></td>
      </tr>
      <tr>
        <th width="120">Barcode</th>
        <td><?= htmlspecialchars($product->barcode); ?></td>
      </tr>
      <tr>
        <th>Description</th>
        <td><?= htmlspecialchars($product->description); ?></td>
      </tr>
      <tr>
        <th>Supplier</th>
        <td><?= htmlspecialchars($product->supplier_name); ?></td>
      </tr>
    </table>
  </div>
  <div class="col-md-6">
    <table class="table table-sm table-bordered table-hover mb-3">
      <tr>
        <th width="120">UOM</th>
        <td><?= htmlspecialchars($product->uom); ?></td>
      </tr>
      <tr>
        <th>On Hand</th>
        <td>
          <?= number_format($product->qty_on_hand); ?>
        </td>
      </tr>
      <tr>
        <th>Cost</th>
        <td><?= number_format($product->cost, 2); ?></td>
      </tr>
      <tr>
        <th>SRP</th>
        <td><?= number_format($product->srp, 2); ?></td>
      </tr>
      <tr>
        <th>SP</th>
        <td><?= number_format($product->selling_price, 2); ?></td>
      </tr>
    </table>
  </div>
</div>