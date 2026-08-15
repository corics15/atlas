<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <?php /*** header */ ?>
    <div class="card">
      <div class="card-header">

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Goods Receipt Information
          </h3>

          <?php
            $statusClass = NULL;
            switch ($goodsReceipt->status) {
              case 'POSTED':
                $statusClass = 'text-success';
                break;
              case 'DRAFT':
                $statusClass = 'text-secondary';
                break;
              default:
                $statusClass = 'text-danger';
                break;
            }
          ?>

          <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= $goodsReceipt->status ?>]</div>
        </div>

      </div>

      <?php
        switch (htmlspecialchars($goodsReceipt->po_status)) {
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

      <div class="card-body">
        <table class="table table-sm table-borderless">
          <tr>
            <th width="180">GRN No.</th>
            <td class="font-weight-500 text-brown"><?= htmlspecialchars($goodsReceipt->grn_no) ?></td>
          </tr>
          <tr>
            <th>PO NO.</th>
            <td>
              <a href="<?= $goodsReceipt->url ?>" class="text-wrap text-olive" target="_blank"><i class="fa-external-link-alt fas font-smr mr-1"></i><?= htmlspecialchars($goodsReceipt->po_no) ?></a>
            </td>
          </tr>
          <tr>
            <th>GRN Date</th>
            <td><?= date('m/d/Y', strtotime(htmlspecialchars($goodsReceipt->grn_date))) ?></td>
          </tr>
          <tr>
            <th>PO Remarks</th>
            <td><?= htmlspecialchars($goodsReceipt->po_remarks) ?></td>
          </tr>
          <tr>
            <th>PO Status</th>
            <td><?= $status ?></td>
          </tr>
          <tr>
            <th>Supplier</th>
            <td><?= htmlspecialchars($goodsReceipt->supplier_name) ?></td>
          </tr>
          <tr>
            <th>Remarks</th>
            <td>
              <input type="text" id="txtRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks" value="<?= $goodsReceipt->remarks ?>">
            </td>
          </tr>
        </table>
      </div>
    </div>

    <?php /*** details */ ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Items Received
        </h3>
        <div class="ml-auto">
          <a href="<?= base_url('goods-receipts') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
          <button type="button" class="btn btn-sm btn-link" id="btnPostGoodsReceipt" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-check mr-2"></i>Post</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCreatePurchaseReturn" <?= !$isEditable ? 'disabled' : '' ?>><i class="fas fa-exchange-alt mr-2"></i>Create Purchase Return</button>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintGoodsReceipt"><i class="fa fa-print mr-2"></i>Print</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCancelGoodsReceipt" <?= !$isEditable ? 'disabled' : '' ?>><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover" id="tblGoodsReceiptDetails">
            <thead class="thead-orange">
              <tr>
                <th width="50" class="text-cener">#</th>
                <th width="150" class="text-center">Barcode</th>
                <th>Description</th>
                <th width="80" class="text-center">UOM</th>
                <th width="110" class="text-right">Qty Ordered</th>
                <th width="110" class="text-right">Qty Rcvd</th>
                <th width="120" class="text-right">Unit Cost</th>
                <th width="120" class="text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php $grandTotal = 0; ?>
              <?php if (empty($details)): ?>
                <tr>
                  <td colspan="8" class="text-center">
                    No items found.
                  </td>
                </tr>
              <?php else: ?>

                <?php foreach ($details as $index => $item): ?>
                  <?php
                    $amount = $item->qty_ordered * $item->unit_cost;
                    $grandTotal += $amount;
                  ?>
                  <tr data-grn-detail-id="<?= $item->id ?>" data-ordered-qty="<?= (float)$item->qty_ordered ?>">
                    <td class="text-right"><?= $index + 1 ?>.</td>
                    <td class="text-center"><?= htmlspecialchars($item->barcode) ?></td>
                    <td><?= htmlspecialchars($item->description) ?></td>
                    <td class="text-center"><?= htmlspecialchars($item->uom) ?></td>
                    <td class="text-right"><?= number_format($item->qty_ordered) ?></td>
                    <td>
                      <input type="number"
                            class="form-control form-control-sm text-right grn-qty"
                            name="qty_received[]"
                            value="<?= (float)$item->qty_received ?>"
                            min="0"
                            max="<?= (float)$item->qty_ordered ?>"
                            step="any">
                    </td>
                    <td class="text-right"><?= number_format($item->unit_cost, 2) ?></td>
                    <td class="text-right"><?= number_format($amount, 2) ?></td>
                  </tr>
                  <?php endforeach; ?>

              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php /*** footer */ ?>
    <div class="card mt-3">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-9"></div>
          <div class="col-md-3">
            <table class="table table-sm mb-0">
              <tr>
                <td class="total-text text-right">
                  Total
                </td>
                <td id="lblTotal"
                  class="text-right"
                  width="180">
                  <?= number_format($grandTotal, 2) ?>
                </td>
              </tr>
            </table>
          </div>
        </div>

        <div class="row">
          <div class="col-md-9"></div>
          <div class="col-md-3">
            <button id="btnSaveChangesGoodsReceipt" class="btn btn-default btn-sm btn-block" <?= !$isEditable ? 'disabled' : '' ?>></i>Save Goods Receipt</button>
          </div>
        </div>

      </div>
    </div>
    <input type="hidden" id="hidGoodsReceiptId" value="<?= $goodsReceipt->id ?>">

  </div>
</section>

<script>
  window.goodsReceiptId = <?= (int) ($goodsReceipt->id ?? 0); ?>;
  window.status = '<?= $goodsReceipt->status; ?>';
</script>