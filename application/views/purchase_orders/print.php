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
        'partials/reports/header',
        [
          'title'  => 'Purchase Order',
          'period' => null
        ]
      );
    ?>

    <table class="report-borderless" style="line-height:12px;table-layout:auto">
      <tr>
        <td><strong>Supplier:</strong></td>
        <td><?= htmlspecialchars($header->supplier_name) ?></td>
        <td><strong>PO No:</strong></td>
        <td><?= htmlspecialchars($header->po_no) ?></td>
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

    <br>

    <?php /*** details table */ ?>
    <table class="report-table">
      <thead>
        <tr>
          <th class="text-center" width="5%">#</th>
          <th>Description</th>
          <th width="8%">UOM</th>
          <th width="8%" class="text-right">Qty</th>
          <th width="12%" class="text-right">Price</th>
          <th width="12%" class="text-right">Discount %</th>
          <th width="14%" class="text-right">Amount</th>
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
          <td class="text-center"><?= $rowIndex ?>.</td>
          <td><?= htmlspecialchars($detail->description) ?></td>
          <td class="text-center"><?= htmlspecialchars($detail->uom) ?></td>
          <td class="text-right"><?= number_format($detail->qty, 0) ?></td>
          <td class="text-right"><?= number_format($detail->price, 2) ?></td>
          <td class="text-right"><?= number_format($detail->discount, 2) ?></td>
          <td class="text-right"><?= number_format($amount, 2) ?></td>
        </tr>
        <?php $rowIndex++; endforeach; ?>
        <tr>
          <td colspan="6" class="text-right"><strong>Grand Total</strong></td>
          <td class="text-right"><strong><?= number_format($total, 2) ?></strong></td>
        </tr>
      </tbody>
    </table>

    <?php /*** signatories */ ?>
    <table class="report-borderless" style="line-height:8px;">
      <tr>
        <td class="font-10 text-center" width="25%">

          <?php if (!empty($preparedBy->signature)): ?>
            <img src="<?= base_url($preparedBy->signature) ?>" alt="Signature" style="height:40px;max-width:120px;object-fit:contain;"><br>
          <?php else: ?>
            <br><br><br><br><?php /*** filler */ ?>
          <?php endif; ?>
          <strong><?= htmlspecialchars($preparedBy->first_name.' '.$preparedBy->last_name) ?></strong>

        </td>
        <td width="25%"></td>
        <td width="25%"></td>
        <td width="25%"></td>
      </tr>
      <tr>
        <td class="font-10 text-center">Prepared By</td>
        <td class="font-10 font-10 text-center">Checked By</td>
        <td class="font-10 font-10 text-center">Approved By</td>
        <td class="font-10 font-10 text-center">Received By</td>
      </tr>
    </table>

    <div style="text-align:right;font-size:10px;margin-top:20px;font-style:italic">
      Printed By:
      <strong><?= htmlspecialchars(strtoupper($this->session->userdata('username'))) ?></strong> <?= date('m/d/Y h:i A'); ?>
    </div>

    <?php if ($documentIndex < count($documents) - 1): ?>
      <div class="page-break"></div>
    <?php endif; ?>

  <?php endforeach; ?>

  <?php $this->load->view('partials/reports/scripts'); ?>