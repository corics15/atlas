<?php $this->load->view('partials/reports/header'); ?>

  <?php /*** date information */ ?>
  <table class="report-borderless" style="line-height:0.5">
    <tbody>
      <tr>
        <td class="font-weight-bold" width="60%"></td>
        <td class="font-weight-bold">Date:</td>
        <td><?= date('M d, Y', strtotime($date_to)) ?></td>
      </tr>
      <tr>
        <td class="font-weight-bold" style="font-size:15px"></td>
        <td class="font-weight-bold">Statement Period:</td>
        <td><?= date('M d, Y', strtotime($date_from)) ?> - <?= date('M d, Y', strtotime($date_to)) ?></td>
      </tr>
      <tr>
        <td></td>
        <td class="font-weight-bold">Customer ID:</td>
        <td><?= strtoupper($hashedCustomerId) ?></td>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </tbody>
  </table>

  <?php /*** customer and account summary */ ?>
  <table class="report-borderless" style="line-height:0.8;table-layout:auto;">
    <tbody>
      <tr>
        <td class="font-weight-bold" style="padding:0"><span class="soa-bill-to">Bill To:</span></td>
        <td class="font-weight-bold text-center soa-account-summary" colspan="2">Account Summary</td>
      </tr>
      <tr>
        <td class="font-weight-bold" style="font-size:15px"><?= htmlspecialchars($customer->customer_name ?? '') ?></td>
        <td style="border-left: 0.5px dashed #bdbdbd;">Previous Balance</td>
        <td class="text-right" style="border-right:0.5px dashed #bdbdbd;"><?= number_format((float)$openingBalance, 2) ?></td>
      </tr>
      <tr>
        <td><?= nl2br(htmlspecialchars($customer->address)) ?></td>
        <td style="border-left:0.5px dashed #bdbdbd;">New Charges</td>
        <td class="text-right" style="border-right:0.5px dashed #bdbdbd;"><?= number_format((float)$periodInvoiced, 2) ?></td>
      </tr>

      <tr>
        <td>
          <?php
            $mobile    = trim($customer->mobile_no ?? '');
            $telephone = trim($customer->telephone_no ?? '');
            $contactNo = null;
            if ($mobile !== '' && $telephone !== '') {
              /*** both numbers exist → show both */
              $contactNo = htmlspecialchars($mobile . ' / ' . $telephone);
            } elseif ($mobile !== '') {
              /*** only mobile exists */
              $contactNo = htmlspecialchars($mobile);
            } elseif ($telephone !== '') {
              /*** only telephone exists */
              $contactNo = htmlspecialchars($telephone);
            }
            if ($contactNo !== null) {
              echo $contactNo;
            }
          ?>
        </td>
        <td style="border-left:0.5px dashed #bdbdbd;">Payments / Credits</td>
        <td class="text-right" style="border-right:0.5px dashed #bdbdbd;"><?= number_format((float)$periodPaid, 2) ?></td>
      </tr>

      <tr>
        <td><?= htmlspecialchars($customer->email_address ?? '') ?></td>
        <td class="font-weight-bold" style="border-left:0.5px dashed #bdbdbd;border-bottom:0.5px dashed #bdbdbd;">Total Balance Due</td>
        <td class="font-weight-bold text-right" style="border-right:0.5px dashed #bdbdbd;border-bottom:0.5px dashed #bdbdbd;"><?= number_format((float)$amountDue, 2) ?></td>
      </tr>
    </tbody>
  </table>

  <br>

  <?php /*** details */ ?>
  <table class="report-bordered soa-details">
    <thead>
      <tr>
        <th class="text-center">Date</th>
        <th class="text-center">Reference</th>
        <th>Description</th>
        <th class="text-right">Charges</th>
        <th class="text-right">Credits</th>
        <th class="text-right">Balance</th>
      </tr>
    </thead>
    <tbody>
      <?php if ((float)$openingBalance != 0): ?>
        <tr>
          <td></td>
          <td></td>
          <td>Previous Balance (Forwarded)</td>
          <td></td>
          <td></td>
          <td class="text-right"><?= number_format((float)$openingBalance, 2) ?></td>
        </tr>
      <?php endif; ?>

      <?php if (empty($transactions)): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-3">
            No transactions found for the selected period.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($transactions as $row): ?>
          <?php
            $description = '';

            if ($row->transaction_type === 'SALES INVOICE') {
              $description = 'Sales Invoice';
            } elseif ($row->transaction_type === 'CUSTOMER PAYMENT') {
              $description = 'Payment Received';
            } else {
              $description = $row->transaction_type;
            }
          ?>

          <tr class="soa-detail-row">
            <td class="text-center"><?= date('m/d/Y', strtotime($row->transaction_date)) ?></td>
            <td class="text-center"><?= htmlspecialchars($row->reference_no) ?></td>
            <td><?= htmlspecialchars($description) ?></td>
            <td class="text-right"><?= (float)$row->debit > 0 ? number_format((float)$row->debit, 2) : '' ?></td>
            <td class="text-right"><?= (float)$row->credit > 0 ? number_format((float)$row->credit, 2) : '' ?></td>
            <td class="text-right"><?= number_format((float)$row->balance, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php /*** filler rows */ ?>
      <?php
        $usedRows = count($transactions);
        if ((float)$openingBalance != 0) {
          $usedRows++;
        }
        $minimumRows = 28;
        $blankRows = max(0, $minimumRows - $usedRows);
      ?>
      <?php for ($i = 0; $i < $blankRows; $i++): ?>
        <tr class="soa-detail-row soa-blank-row">
          <td>&nbsp;</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <br>

  <?php /*** footer */ ?>
  <table class="report-borderless">
    <tbody>
      <tr class="soa-footer">
        <td class="font-weight-bold text-right">Account Current Balance</td>
        <td class="font-weight-bold text-right"><?= number_format((float)$amountDue, 2) ?></td>
      </tr>
    </tbody>
  </table>

  <br>
  <div class="text-center"><em>Thank you for your business!</em></div>

<?php $this->load->view('partials/reports/scripts'); ?>