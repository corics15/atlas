<div class="modal fade" id="agingDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">A/R Details</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <span id="agingCustomerName" class="font-weight-500 text-brown"></span>
          <span class="text-muted float-right font-sm" id="agingAsOfDate"></span>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="thead-orange">
              <tr>
                <th class="text-center">SI No.</th>
                <th class="text-center">Invoice Date</th>
                <th class="text-right">SI Amount</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Credit Memo</th>
                <th class="text-right">Balance</th>
              </tr>
            </thead>
            <tbody id="agingDetailsBody"></tbody>
            <tfoot>
              <tr class="font-weight-500">
                <td colspan="5" class="text-right">Outstanding A/R</td>
                <td class="text-right" id="agingDetailsTotal">0.00</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>