<div class="card mt-3">
  <div class="card-body">
    <div class="form-row">
      <div class="align-items-end col-md-9 d-flex">

        <div class="alert alert-light font-sm mb-0" role="alert">
          <div class="font-weight-500 mb-1">
            <i class="fas fa-info-circle mr-1 text-info"></i>
            Purchase Order Guide
          </div>

          <div>
            <span class="font-weight-500">1.</span>
            Scan or enter the product barcode, then press <kbd class="text-lime">Enter</kbd>.
            You may also press <kbd class="text-lime">F2</kbd> or click the
            <i class="fas fa-search text-brown"></i>
            button to open the Product Finder.
          </div>

          <div>
            <span class="font-weight-500">2.</span>
            Select the appropriate <span class="font-weight-500 text-danger">UOM</span> and enter the
            <span class="font-weight-500 text-danger">Qty</span> to be ordered.
          </div>

          <div>
            <span class="font-weight-500">3.</span>
            Review or enter the <span class="font-weight-500 text-danger">Price</span> and
            <span class="font-weight-500 text-danger">Discount</span>, when applicable.
          </div>

          <div>
            <span class="font-weight-500">4.</span>
            Press <kbd class="text-lime">Enter</kbd> after <span class="font-weight-500 text-danger">Qty</span> to continue to the next item.
          </div>

          <div>
            <span class="font-weight-500">5.</span>
            Review the order total, then click
            <span class="font-weight-500 text-brown">Save Purchase Order</span>.
          </div>

          <div>
            <span class="font-weight-500">6.</span>
            Once the Purchase Order is ready for actual delivery,
            use <span class="font-weight-500 text-olive">Receive Goods</span> to record the items received
            and update inventory.
          </div>
        </div>


      </div>

      <div class="col-md-3">
        <table class="table table-sm mb-3">
          <tr>
            <td class="total-text">Total</td>
            <td id="lblTotal" class="text-right">0.00</td>
          </tr>
        </table>
        <button id="btnSavePurchaseOrder" class="btn btn-default btn-sm btn-block" <?= !$isEditable ? 'disabled' : '' ?>>Save Purchase Order</button>
      </div>
    </div>

  </div>
</div>