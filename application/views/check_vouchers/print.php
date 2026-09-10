<?php foreach ($documents as $i => $document): ?>
  <?php
    $header = $document->header;
    $details = $document->details;

    $totalDebit = 0;
    $totalCredit = 0;

    foreach ($details as $detail) {
      $totalDebit += (float)$detail->debit;
      $totalCredit += (float)$detail->credit;
    }

    $maxDetailRows = 8;
    $rowsToRender = max(0, $maxDetailRows - count($details));
  ?>

    <?php foreach (['FIRST COPY', 'SECOND COPY'] as $copy): ?>
      <?php $this->load->view('partials/reports/header'); ?>
      <div style="text-align:right;font-size:10px;font-weight:bold;margin-bottom:4px;">
        <?= $copy ?>
      </div>

      <div class="report">

        <?php /*** document header */ ?>
        <table class="report-borderless" style="table-layout:auto;line-height:7.5px">
          <tr>
            <td><strong>CV No.</strong></td>
            <td><strong><?= htmlspecialchars($header->cv_no) ?></strong></td>
            <td><strong>Voucher Date</strong></td>
            <td><?= date('M d, Y', strtotime($header->voucher_date)) ?></td>
          </tr>
          <tr>
            <td><strong>Branch</strong></td>
            <td><?= htmlspecialchars($header->branch_name ?? '') ?></td>
            <td><strong>Status</strong></td>
            <td><?= htmlspecialchars($header->status) ?></td>
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
                    (!empty($header->bank_account_name) ? ' - '.$header->bank_account_name : '')
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
            <td><strong>Reference No.</strong></td>
            <td><?= htmlspecialchars($header->reference_no ?? '') ?></td>
            <td><strong>Amount</strong></td>
            <td><strong><?= number_format($totalDebit, 2) ?></strong></td>
          </tr>
          <tr>
            <td><strong>Particulars</strong></td>
            <td colspan="3"><?= nl2br(htmlspecialchars($header->particulars ?? '')) ?></td>
          </tr>
        </table>
        <?php /*** end document header */ ?>

        <br>

        <?php /*** accounting distribution */ ?>
        <table class="report-bordered" style="line-height:7.5px">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th class="text-center">Account Code</th>
              <th>Account Description</th>
              <th class="text-right">Debit</th>
              <th class="text-right">Credit</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($details as $index => $detail): ?>
              <tr>
                <td class="text-center"><?= $index + 1 ?>.</td>
                <td class="text-center"><?= htmlspecialchars($detail->account_code) ?></td>
                <td><?= htmlspecialchars($detail->account_name) ?></td>
                <td class="text-right"><?= (float)$detail->debit > 0 ? number_format((float)$detail->debit, 2) : '' ?></td>
                <td class="text-right"><?= (float)$detail->credit > 0 ? number_format((float)$detail->credit, 2) : '' ?></td>
                <td><?= htmlspecialchars($detail->remarks ?? '') ?></td>
              </tr>
            <?php endforeach; ?>

            <?php /*** dummy row fillers */ ?>
            <?php for ($row = 0; $row < $rowsToRender; $row++): ?>
              <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            <?php endfor; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
              <td class="text-right" style="font-size:11px"><strong><?= number_format($totalDebit, 2) ?></strong></td>
              <td class="text-right" style="font-size:11px"><strong><?= number_format($totalCredit, 2) ?></strong></td>
              <td></td>
            </tr>
            <tr>
              <td colspan="2"><strong>Amount In Words</strong></td>
              <td colspan="6">
                <strong><?= htmlspecialchars(amount_in_words($totalDebit)) ?></strong>
              </td>
            </tr>
          </tfoot>
        </table>
        <?php /*** end accounting distribution */ ?>



        <br><br><br>

        <?php /*** signatures */ ?>
        <table class="report-borderless" style="line-height:5px">
          <tr>
            <td class="text-center font-weight-bold">
              <?= $this->session->userdata('first_name').' '.$this->session->userdata('last_name') ?>
            </td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td>_______________________________</td>
            <td>_______________________________</td>
            <td>_______________________________</td>
            <td>_______________________________</td>
          </tr>
          <tr>
            <td class="text-center">Prepared By</td>
            <td class="text-center">Approved By</td>
            <td class="text-center">Checked By</td>
            <td class="text-center">Received By</td>
          </tr>
        </table>
        <?php /*** end signatures */ ?>

      </div>

      <div style="text-align:right;font-size:10px;margin-top:15px;">
        Printed By:
        <strong><?= strtoupper(htmlspecialchars($this->session->userdata('username'))); ?></strong>
        <?= date('m/d/Y h:i A'); ?>
      </div>

      <?php if ($copy === 'FIRST COPY'): ?>
        <div style="border-top:1px dashed #777;margin:10px 0;"></div>
      <?php endif; ?>

    <?php endforeach; ?>

  <?php if ($i < count($documents) - 1): ?>
    <div class="page-break"></div>
  <?php endif; ?>

<?php endforeach; ?>

<?php $this->load->view('partials/reports/scripts'); ?>