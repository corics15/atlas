  <?php /*** header */ ?>
  <?php
    $company = atlas_company();

    $contactNo = null;
    $mobile    = trim($company->mobile_no ?? '');
    $telephone = trim($company->telephone_no ?? '');

    if ($mobile !== '' && $telephone !== '') {
        $contactNo = htmlspecialchars($mobile . ' / ' . $telephone);
    } elseif ($mobile !== '') {
        $contactNo = htmlspecialchars($mobile);
    } elseif ($telephone !== '') {
        $contactNo = htmlspecialchars($telephone);
    }
  ?>
  <?php foreach ($documents as $documentIndex => $document): ?>

    <?php $header = $document->header; ?>

    <?php
      /*** report header */
      $this->load->view(
        'partials/reports/pdf_header',
        [
          'title'  => 'Purchase Order',
        ]
      );
    ?>

    <table style="line-height:12px">
      <tr>
        <td width="10%"><strong>Supplier:</strong></td>
        <td width="65%"><?= htmlspecialchars($header->supplier_name) ?></td>
        <td width="10%"><strong>PO No:</strong></td>
        <td width="15%"><?= htmlspecialchars($header->po_no) ?></td>
      </tr>
      <tr>
        <td><strong>Address:</strong></td>
        <td><?= htmlspecialchars($header->address) ?></td>
        <td><strong>PO Date:</strong></td>
        <td><?= date('m/d/Y', strtotime($header->po_date)) ?></td>
      </tr>
      <tr>
        <td><strong>Contact:</strong></td>
        <td><?= htmlspecialchars($header->contact_person) ?></td>
        <td><strong>Terms:</strong></td>
        <td><?= htmlspecialchars($header->terms_name) ?></td>
      </tr>
      <tr>
        <td><strong>Remarks</strong></td>
        <td colspan="3"><?= htmlspecialchars($header->remarks) ?></td>
      </tr>
    </table>

    <br><br>

    <?php /*** details table */ ?>
    <table style="line-height:15px">
      <thead style="border:1px solid #000">
        <tr>
          <th class="text-center border-left-end" width="5%">#</th>
          <th width="41%" class="border-top-bottom">Description</th>
          <th class="text-center border-top-bottom" width="8%">UOM</th>
          <th width="8%" class="text-right border-top-bottom">Qty</th>
          <th width="12%" class="text-right border-top-bottom">Price</th>
          <th width="12%" class="text-right border-top-bottom">Discount %</th>
          <th width="14%" class="text-right border-right-end">Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $total = 0;
          $rowIndex = 1;
          foreach($document->details as $detail):
            $amount = ($detail->qty * $detail->price) * (1 - ($detail->discount / 100));
            $total += $amount;
          ?>
        <tr>
          <td class="text-center border-left-bottom-right" width="5%"><?= $rowIndex ?>.</td>
          <td class ="border-bottom-right" width="41%"><?= htmlspecialchars($detail->description) ?></td>
          <td class="text-center border-bottom-right" width="8%"><?= htmlspecialchars($detail->uom) ?></td>
          <td class="text-right border-bottom-right" width="8%"><?= number_format($detail->qty, 0) ?></td>
          <td class="text-right border-bottom-right" width="12%"><?= number_format($detail->price, 2) ?></td>
          <td class="text-right border-bottom-right" width="12%"><?= number_format($detail->discount, 2) ?></td>
          <td class="text-right border-bottom-right" width="14%"><?= number_format($amount, 2) ?></td>
        </tr>
        <?php $rowIndex++; endforeach; ?>
        <tr>
          <td colspan="6" class="text-right border-left-bottom-right"><strong>Grand Total</strong></td>
          <td class="text-right border-bottom-right"><strong><?= number_format($total,2) ?></strong></td>
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

    <?php if ($documentIndex < count($documents) - 1): ?>
      <br pagebreak="true" />
    <?php endif; ?>

  <?php endforeach; ?>