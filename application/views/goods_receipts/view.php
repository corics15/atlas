<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <?php /*** header */ ?>
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
            <td><?= date('m/d/Y', strtotime(htmlspecialchars($goodsReceipt->grn_date))) ?></td>
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
            <td>
              <input type="text" id="txtRemarks" class="form-control form-control-sm text-uppercase" placeholder="Enter remarks" value="<?= $goodsReceipt->remarks ?>">
            </td>
          </tr>
        </table>
      </div>
    </div>

    <?php /*** details */ ?>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          Items Received
        </h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive table-scroll">
          <table class="table table-sm table-bordered table-hover" id="tblGoodsReceiptDetails">
            <thead class="thead-orange">
              <tr>
                <th width="50" class="text-cener">#</th>
                <th width="150">Barcode</th>
                <th>Description</th>
                <th width="80" class="text-center">UOM</th>
                <th width="110" class="text-right">Ordered</th>
                <th width="110" class="text-right">Received</th>
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
                  $amount = $item->qty_received * $item->unit_cost;
                  $grandTotal += $amount;
                ?>
                <tr data-grn-detail-id="<?= $item->id ?>" data-ordered-qty="<?= (float)$item->qty_ordered ?>">
                  <td class="text-right">
                    <?= $index + 1 ?>.
                  </td>
                  <td>
                    <?= htmlspecialchars($item->barcode) ?>
                  </td>
                  <td>
                    <?= htmlspecialchars($item->description) ?>
                  </td>
                  <td class="text-center">
                    <?= htmlspecialchars($item->uom) ?>
                  </td>
                  <td class="text-right">
                    <?= number_format($item->qty_ordered) ?>
                  </td>
                  <td>
                    <input type="number"
                          class="form-control form-control-sm text-right grn-qty"
                          name="qty_received[]"
                          value="<?= (float)$item->qty_received ?>"
                          min="0"
                          max="<?= (float)$item->qty_ordered ?>"
                          step="any">
                  </td>
                  <td class="text-right">
                    <?= number_format($item->unit_cost, 2) ?>
                  </td>
                  <td class="text-right">
                    <?= number_format($amount, 2) ?>
                  </td>
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
          <div class="col-md-8">

            <a href="<?= site_url('goods_receipts'); ?>"
              class="btn btn-default btn-sm">
            <i class="bi bi-arrow-left"></i>
            Back
            </a>

            <button id="btnSaveChangesGoodsReceipt"
              class="btn btn-outline-success btn-sm">
            <i class="bi bi-floppy"></i>
            Save Changes
            </button>

            <button id="btnPrint"
              class="btn btn-default btn-sm">
            <i class="bi bi-printer"></i>
            Print
            </button>

          </div>
          <div class="col-md-4">

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
      </div>
    </div>
    <input type="hidden" id="hidGoodsReceiptId" value="<?= $goodsReceipt->id ?>">

  </div>
</section>