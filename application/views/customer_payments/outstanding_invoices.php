<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Outstanding Invoices
        </h3>
        <div class="ml-auto">
          <a href="<?= base_url('customer-payments') ?>" type="button" class="btn btn-sm btn-link"><i class="fa fa-arrow-alt-circle-left mr-2"></i>Back To List</a>
          <button type="button" class="btn btn-sm btn-link" id="btnPostCustomerPayment" <?= !$isEditable ? 'disabled' : '' ?>><i class="fa fa-check mr-2"></i>Post</button>
          <button type="button" class="btn btn-sm btn-link" id="btnPrintCustomerPayment"><i class="fa fa-print mr-2"></i>Print</button>
          <button type="button" class="btn btn-sm btn-link" id="btnCancelCustomerPayment"><i class="fas fa-ban mr-2"></i>Cancel</button>
        </div>
      </div>

      <?php /*** outstanding invoice list */ ?>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover mb-0 atlas-entry-table" id="tblOutstandingInvoices">

            <thead class="thead-orange">
              <tr>
                <th class="text-center">SI No.</th>
                <th class="text-center">Invoice Date</th>
                <th class="text-right">SI Amount</th>
                <th class="text-right">Paid</th>
                <th class="text-right"><i class="fas fa-info-circle text-brown mr-1" data-toggle="tooltip" title="" data-original-title="Non-cash adjustment/credit"></i>Credit Memo</th>
                <th class="text-right">Balance</th>
                <th class="text-right">Apply Amount</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td colspan="7" class="text-center text-muted py-3">Select a customer to view outstanding invoices.</td>
              </tr>
            </tbody>

          </table>
        </div>
      </div>
    </div>

    <?php /*** other deductions */ ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Other Deductions</h3>
        <div class="ml-auto">
          <button type="button" class="btn btn-sm btn-link" id="btnAddCustomerPaymentDeduction" <?= !$isEditable ? 'disabled' : '' ?>>
            <i class="fas fa-plus-circle mr-2"></i>Add Deduction
          </button>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover mb-0 atlas-entry-table" id="tblCustomerPaymentDeductions">
            <thead class="thead-orange">
              <tr>
                <th width="220" class="text-center">Sales Invoice</th>
                <th>Particulars / Type</th>
                <th width="180" class="text-right">Amount</th>
                <th width="80" class="text-center"></th>
              </tr>
            </thead>
            <tbody>
              <tr class="deduction-empty-row">
                <td colspan="4" class="text-center text-muted py-3">No other deductions.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php /*** customer credits, if applicable */ ?>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Available Customer Credit</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover mb-0 atlas-entry-table" id="tblAvailableCredits">
            <thead class="thead-orange">
              <tr>
                <th class="text-center">Source No.</th>
                <th class="text-center">Date</th>
                <th class="text-center">Origin</th>
                <th class="text-right">Available Credit</th>
                <th class="text-center">Apply To SI No.</th>
                <th class="text-right">Apply Credit</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="7" class="text-center text-muted py-3">Select a customer to view available credit.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php /*** customer payment credit application history */ ?>
    <?php
      $creditApplications = array_filter($allocations ?? [], function($allocation) {
        return $allocation->allocation_type === 'CREDIT';
      });
    ?>

    <?php if (!empty($creditApplications)): ?>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Credit Applications</h3>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0 atlas-entry-table">
              <thead class="thead-orange">
                <tr>
                  <th class="text-center">Applied On</th>
                  <th class="text-center">Applied To</th>
                  <th class="text-right">Amount</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($creditApplications as $application): ?>
                  <tr>
                    <td class="text-center">
                      <?= $application->applied_on
                        ? date('m/d/Y h:i A', strtotime($application->applied_on))
                        : '-' ?>
                    </td>
                    <td class="text-center">
                      <?= html_escape($application->si_no) ?>
                    </td>
                    <td class="text-right">
                      <?= number_format((float)$application->amount_applied, 2) ?>
                    </td>
                    <td>
                      <?= html_escape($application->remarks ?? '') ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php /*** customer credit refund history */ ?>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Customer Credit Refunds</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover mb-0 atlas-entry-table" id="tblCustomerCreditRefunds">
            <thead class="thead-orange">
              <tr>
                <th class="text-center">Refund Date</th>
                <th class="text-center">Source No.</th>
                <th class="text-center">Reference No.</th>
                <th class="text-right">Amount</th>
                <th>Remarks</th>
                <th class="text-center">Status</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="7" class="text-center text-muted py-3">Select a customer to view credit refunds.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php /*** footer */ ?>
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">

            <div class="alert alert-light font-sm mb-0" role="alert">
              <div class="font-weight-500 mb-1">
                <i class="fas fa-info-circle mr-1 text-info"></i>
                Customer Payment Guide
              </div>

              <div>
                <span class="font-weight-500">1.</span>
                Select the <span class="font-weight-500 text-danger">Customer</span> who made the payment.
                Make sure you select the correct customer because the
                <span class="font-weight-500 text-indigo-2">Outstanding Invoices</span> and
                <span class="font-weight-500 text-indigo-2">Available Customer Credit</span>
                shown below belong to the selected customer.
              </div>

              <div>
                <span class="font-weight-500">2.</span>
                Enter the <span class="font-weight-500 text-danger">Amount Received</span>.
                This is the actual amount of money received from the customer.
                Select the appropriate <span class="font-weight-500 text-danger">Payment Method</span>
                and enter the reference or other payment information when applicable.
              </div>

              <div>
                <span class="font-weight-500">3.</span>
                Under <span class="font-weight-500 text-indigo-2">Outstanding Invoices</span>,
                locate the invoice or invoices being paid.
                Check the <span class="font-weight-500">SI Amount</span>,
                <span class="font-weight-500">Paid</span>,
                <span class="font-weight-500">Credit Memo</span>, and
                <span class="font-weight-500">Balance</span> before entering the payment.
              </div>

              <div>
                <span class="font-weight-500">4.</span>
                Enter the amount to be paid for each invoice in the
                <span class="font-weight-500 text-danger">Apply Amount</span> column.
                You may apply the payment to one invoice or divide it among several outstanding invoices.
                Do not apply more than the remaining balance of an invoice.
              </div>

              <div>
                <span class="font-weight-500">5.</span>
                If the invoice has a non-cash adjustment such as withholding tax, shortage, damaged items,
                rebate, allowance, bank charge, discount, or similar deduction, click
                <span class="font-weight-500 text-brown">Add Deduction</span> under
                <span class="font-weight-500 text-indigo-2">Other Deductions</span>.
                Select the affected <span class="font-weight-500">Sales Invoice</span>,
                enter the <span class="font-weight-500">Particulars / Type</span> and
                <span class="font-weight-500">Amount</span>.
                Other Deductions reduce the invoice balance but are
                <span class="font-weight-500 text-danger">not part of the actual Amount Received</span>.
              </div>

              <div>
                <span class="font-weight-500">6.</span>
                The total <span class="font-weight-500">Apply Amount</span> does not have to use the entire
                <span class="font-weight-500">Amount Received</span>.
                If part of the customer's payment is not applied to an invoice, the unused amount becomes
                <span class="font-weight-500 text-success">Available Customer Credit</span>
                after the payment is posted.
              </div>

              <div>
                <span class="font-weight-500">7.</span>
                Review all payment information carefully, then click
                <span class="font-weight-500 text-brown">Save Customer Payment</span>.
                Saving keeps the transaction as a
                <span class="font-weight-500 text-secondary">DRAFT</span>
                and still allows you to review or correct it.
              </div>

              <div>
                <span class="font-weight-500">8.</span>
                When the payment details and invoice applications are correct, click
                <span class="font-weight-500 text-orange">Post</span>.
                Posting finalizes the payment and updates the customer's outstanding balances.
              </div>

              <div>
                <span class="font-weight-500">9.</span>
                If the customer has an amount listed under
                <span class="font-weight-500 text-indigo-2">Available Customer Credit</span>,
                the credit may be applied to an outstanding invoice using
                <span class="font-weight-500 text-olive">Apply Credit</span>.
                Enter only the amount that should be applied to the selected invoice.
              </div>

              <div>
                <span class="font-weight-500">10.</span>
                After Customer Payment credit is applied to an invoice, the transaction will appear under
                <span class="font-weight-500 text-indigo-2">Credit Applications</span>.
                This section shows when the credit was applied, which Sales Invoice received it, the amount applied,
                and any remarks entered.
              </div>

              <div>
                <span class="font-weight-500">11.</span>
                Customer credit with source
                <span class="font-weight-500 text-success">CP</span>
                came from an actual Customer Payment that was previously received but not fully applied.
                This type of credit may also be
                <span class="font-weight-500 text-danger">Refunded</span>
                to the customer when necessary.
              </div>

              <div>
                <span class="font-weight-500">12.</span>
                Customer credit with source
                <span class="font-weight-500 text-info">CM</span>
                came from a <span class="font-weight-500">Credit Memo</span>, usually created from a Sales Return.
                It may be applied to an outstanding invoice, but it
                <span class="font-weight-500 text-danger">cannot be refunded as cash</span>.
              </div>

              <div>
                <span class="font-weight-500">13.</span>
                If a Customer Payment credit is refunded, the transaction will appear under
                <span class="font-weight-500 text-indigo-2">Customer Credit Refunds</span>.
                A refund should only be made when money is actually being returned to the customer.
              </div>

              <div>
                <span class="font-weight-500">14.</span>
                Before leaving the transaction, always verify the
                <span class="font-weight-500">Amount Received</span>,
                <span class="font-weight-500">Apply Amount</span>,
                remaining <span class="font-weight-500">Balance</span>, and any
                <span class="font-weight-500">Available Customer Credit</span>
                to make sure the customer's account is correct.
              </div>
            </div>

          </div>

          <?php /*** payment summary */ ?>
          <div class="col-md-4">
            <table class="table table-sm mb-3">
              <tbody>
                <tr>
                  <td>Amount Received</td>
                  <td id="cpAmountReceived" class="text-right">0.00</td>
                </tr>
                <tr>
                  <td>Payment Applied</td>
                  <td id="cpAmountApplied" class="text-right">0.00</td>
                </tr>
                <tr>
                  <td>Other Deductions</td>
                  <td id="cpOtherDeductions" class="text-right">0.00</td>
                </tr>
                <tr>
                  <td>Total AR Settlement</td>
                  <td id="cpTotalSettlement" class="text-right">0.00</td>
                </tr>
                <tr class="font-weight-500">
                  <td>Unapplied Amount</td>
                  <td id="cpAmountUnapplied" class="text-right">0.00</td>
                </tr>
              </tbody>
            </table>

            <button id="btnSaveCustomerPayment" class="btn btn-default btn-sm btn-block" <?= !$isEditable ? 'disabled' : '' ?>>Save Customer Payment</button>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>