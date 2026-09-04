<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      Receiving Information
    </h3>
  </div>
  <div class="card-body">

    <input type="hidden" id="hidPurchaseOrderId" name="po_id" value="<?= $purchaseOrder['header']->id ?>">
    <input type="hidden" id="hidSupplierId" name="supplier_id" value="<?= $purchaseOrder['header']->supplier_id ?>">

      <?php /*** header */ ?>
      <?php
        switch (htmlspecialchars($purchaseOrder['header']->status)) {
          case 'OPEN':
            $status = '<span class="badge badge-success">OPEN</span>';
            break;

          case 'PARTIAL':
            $status = '<span class="badge badge-warning">PARTIAL</span>';
            break;

          case 'COMPLETED':
            $status = '<span class="badge badge-primary">COMPLETED</span>';
            break;

          case 'CLOSED':
            $status = '<span class="badge badge-secondary">CLOSED</span>';
            break;

          case 'CANCELLED':
            $status = '<span class="badge badge-danger">CANCELLED</span>';
            break;

          default:
            $status = '<span class="badge badge-light">UNKNOWN</span>';
            break;
        }
      ?>
    <input type="hidden" id="txtGRNNo" value="">
    <table class="table table-sm table-borderless">
      <tr>
        <th width="180">GRN No.</th>
        <td class="font-weight-500 text-brown" id="tdRefNo">AUTO-GENERATED</td>
      </tr>
      <tr>
        <th>PO No.</th>
        <td>
          <a href="<?= base_url('purchase-orders?id=').$purchaseOrder['header']->id ?>" class="font-weight-500 text-olive" target="_blank">
            <i class="fa-external-link-alt fas fa-xs mr-1"></i>
            <?= htmlspecialchars($purchaseOrder['header']->po_no) ?>
          </a>
        </td>
      </tr>
      <tr>
        <th>Supplier</th>
        <td>
          <?= htmlspecialchars($purchaseOrder['header']->supplier_name) ?>
        </td>
      </tr>
      <tr>
        <th>PO Remarks</th>
        <td>
          <?= htmlspecialchars($purchaseOrder['header']->remarks) ?>
        </td>
      </tr>
      <tr>
        <th>PO Status</th>
        <td>
          <?= $status ?>
        </td>
      </tr>
      <tr>
        <th>GRN Date</th>
        <td>
          <input type="date" id="dtGRNDate" class="form-control form-control-sm w-auto" value="<?= date('Y-m-d'); ?>">
        </td>
      </tr>
      <tr>
        <th>Remarks</th>
        <td>
          <input type="text" id="txtRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks">
        </td>
      </tr>
    </table>

  </div>
</div>