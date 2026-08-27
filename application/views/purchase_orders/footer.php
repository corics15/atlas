<div class="card mt-3">
  <div class="card-body">
    <div class="form-row">
      <div class="col-md-9"></div>

      <div class="col-md-3">
        <table class="table table-sm mb-3">
          <tr>
            <td class="total-text">Total</td>
            <td id="lblTotal" class="text-right">0.00</td>
          </tr>
        </table>
      </div>
    </div>

    <div class="form-row">
      <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
          <div class="col-md-9"></div>
          <div class="col-md-3">
            <button id="btnSavePurchaseOrder" class="btn btn-default btn-sm btn-block" <?= !$isEditable ? 'disabled' : '' ?>>Save Purchase Order</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>