<?php foreach ($documents as $documentIndex => $document): ?>

  <?php
    $header = $document->header;
    $details = $document->details;

    $totalDebit = 0;
    $totalCredit = 0;

    foreach ($details as $detail) {
      $totalDebit += (float)$detail->debit;
      $totalCredit += (float)$detail->credit;
    }

    $maxDetail = 8;
    $rowsToRender = max(0, $maxDetail - count($details));
  ?>

  <?php foreach (['FIRST COPY', 'SECOND COPY'] as $copyIndex => $copy): ?>

    <?php
      $this->load->view(
        'partials/reports/pdf_header',
        [
          'title' => 'Check Voucher'
        ]
      );
    ?>

    <div style="text-align:right;font-size:7px;"><strong><?= $copy ?></strong></div>

    <table style="line-height:11px;">
      <tr>
        <td width="12%"><strong>CV No.</strong></td>
        <td width="38%"><strong><?= htmlspecialchars($header->cv_no) ?></strong></td>
        <td width="15%"><strong>Voucher Date</strong></td>
        <td width="35%"><?= date('M d, Y', strtotime($header->voucher_date)) ?></td>
      </tr>

      <tr>
        <td><strong>Branch</strong></td>
        <td><?= htmlspecialchars($header->branch_name ?? '') ?></td>
        <td><strong>Status</strong></td>
        <td><?= htmlspecialchars($header->status ?? '') ?></td>
      </tr>

      <tr>
        <td><strong>Payee</strong></td>
        <td><?= htmlspecialchars($header->payee_name ?? '') ?></td>
        <td><strong>Payment Method</strong></td>
        <td><?= htmlspecialchars(str_replace('_', ' ', $header->payment_method ?? '')) ?></td>
      </tr>

      <?php if (in_array($header->payment_method, ['CHECK', 'BANK_TRANSFER'], true)): ?>
        <tr>
          <td><strong>Bank</strong></td>
          <td>
            <?= htmlspecialchars(
              trim(
                ($header->bank_name ?? '') .
                (!empty($header->bank_account_name)
                  ? ' - '.$header->bank_account_name
                  : '')
              )
            ) ?>
          </td>
          <td><strong>Account No.</strong></td>
          <td><?= htmlspecialchars($header->account_no ?? '') ?></td>
        </tr>
      <?php endif; ?>

      <?php if ($header->payment_method === 'CHECK'): ?>
        <tr>
          <td><strong>Check No.</strong></td>
          <td><?= htmlspecialchars($header->check_no ?? '') ?></td>
          <td><strong>Check Date</strong></td>
          <td>
            <?= !empty($header->check_date)
              ? date('M d, Y', strtotime($header->check_date))
              : '' ?>
          </td>
        </tr>
      <?php endif; ?>

      <tr>
        <td><strong>Ref. No.</strong></td>
        <td><?= htmlspecialchars($header->reference_no ?? '') ?></td>
        <td><strong>Amount</strong></td>
        <td><strong><?= number_format($totalDebit, 2) ?></strong></td>
      </tr>

      <tr>
        <td><strong>Particulars</strong></td>
        <td colspan="3">
          <?= nl2br(htmlspecialchars($header->particulars ?? '')) ?>
        </td>
      </tr>
    </table>

    <br><br>

    <table style="line-height:11.5px">
      <thead>
        <tr>
          <th class="text-center border-left-end" width="5%" style="border:0.5px solid #000;">#</th>
          <th class="text-center border-top-bottom" width="15%" style="border:0.5px solid #000;">Account Code</th>
          <th class="border-top-bottom" width="28%" style="border:0.5px solid #000;">Account Description</th>
          <th class="text-right border-top-bottom" width="14%" style="border:0.5px solid #000;">Debit</th>
          <th class="text-right border-top-bottom" width="14%" style="border:0.5px solid #000;">Credit</th>
          <th class="border-right-end" width="24%" style="border:0.5px solid #000;">Remarks</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($details as $index => $detail): ?>
          <tr class="font-8">
            <td class="text-center border-left-bottom-right" width="5%"><?= $index + 1 ?>.</td>
            <td class="text-center border-bottom-right" width="15%"><?= htmlspecialchars($detail->account_code) ?></td>
            <td class="border-bottom-right" width="28%"><?= htmlspecialchars($detail->account_name) ?></td>
            <td class="text-right border-bottom-right" width="14%">
              <?= (float)$detail->debit > 0
                ? number_format((float)$detail->debit, 2)
                : '' ?>
            </td>
            <td class="text-right border-bottom-right" width="14%">
              <?= (float)$detail->credit > 0
                ? number_format((float)$detail->credit, 2)
                : '' ?>
            </td>
            <td class="border-bottom-right" width="24%"><?= htmlspecialchars($detail->remarks ?? '') ?></td>
          </tr>
        <?php endforeach; ?>

        <?php for ($row = 0; $row < $rowsToRender; $row++): ?>
          <tr>
            <td class="border-left-bottom-right" width="5%">&nbsp;</td>
            <td class="border-bottom-right" width="15%"></td>
            <td class="border-bottom-right" width="28%"></td>
            <td class="border-bottom-right" width="14%"></td>
            <td class="border-bottom-right" width="14%"></td>
            <td class="border-bottom-right" width="24%"></td>
          </tr>
        <?php endfor; ?>

        <tr>
          <td colspan="3" class="text-right border-left-bottom-right"><strong>TOTAL</strong></td>
          <td class="text-right border-bottom-right"><strong><?= number_format($totalDebit, 2) ?></strong></td>
          <td class="text-right border-bottom-right"><strong><?= number_format($totalCredit, 2) ?></strong></td>
          <td class="border-bottom-right"></td>
        </tr>

        <tr class="font-8" style="line-height:14px">
          <td colspan="2" class="border-left-bottom-right"><strong>Amount in Words</strong></td>
          <td colspan="4" class="border-bottom-right"><strong><?= htmlspecialchars(amount_in_words($totalDebit)) ?></strong></td>
        </tr>
      </tbody>
    </table>

    <br><br><br>

    <?php /*** signatories */ ?>
    <table>
      <tr>
        <td class="font-7 text-center border-top-left-bottom"><strong><?= $this->session->userdata('first_name').' '.$this->session->userdata('last_name') ?></strong></td>
        <td class="border-top-left-bottom"></td>
        <td class="border-top-left-bottom"></td>
        <td class="border-top-left-bottom-right"></td>
      </tr>
      <tr>
        <td class="font-7 text-center border-left-bottom-right">Prepared By</td>
        <td class="font-7 text-center border-bottom-right">Checked By</td>
        <td class="font-7 text-center border-bottom-right">Approved By</td>
        <td class="font-7 text-center border-bottom-right">Received By</td>
      </tr>
    </table>

    <?php /*** printed by */ ?>
    <table cellspacing="5">
      <tr>
        <td class="font-7 text-right" style="font-style:italic">Printed By: <?= htmlspecialchars($this->session->userdata('username')).' '.date('m/d/Y h:i A'); ?></td>
      </tr>
    </table>

    <?php if ($copyIndex === 0): ?>
      <br>
      <hr style="border-top:1px dashed #777;">
      <br>
    <?php endif; ?>

  <?php endforeach; ?>

  <?php if ($documentIndex < count($documents) - 1): ?>
    <br pagebreak="true" />
  <?php endif; ?>

<?php endforeach; ?>