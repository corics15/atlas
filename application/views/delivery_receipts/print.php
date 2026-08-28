<?php foreach ($documents as $i => $document): ?>

  <?php $this->load->view('partials/reports/header'); ?>

  <?php
    $header  = $document->header;
    $details = $document->details;
    ?>

  <div class="report">
    <table class="report-borderless" style="line-height:8px;table-layout:auto">
      <tr>
        <td><strong>DR No.:</strong></td>
        <td><?= htmlspecialchars($header->dr_no) ?></td>
        <td><strong>SO No.:</strong></td>
        <td><?= htmlspecialchars($header->so_no) ?></td>
      </tr>
      <tr>
        <td><strong>Delivery Date:</strong></td>
        <td><?= date('m/d/Y', strtotime($header->delivery_date)) ?></td>
        <td><strong>Customer:</strong></td>
        <td><?= htmlspecialchars($header->customer_name) ?></td>
      </tr>
      <tr>
        <td><strong>Salesman:</strong></td>
        <td><?= htmlspecialchars($header->salesman_name) ?></td>
        <td><strong>Terms:</strong></td>
        <td><?= htmlspecialchars($header->terms_name) ?></td>
      </tr>
      <tr>
        <td><strong>Remarks</strong></td>
        <td><?= nl2br(htmlspecialchars($header->remarks)) ?></td>
      </tr>
    </table>

    <br><br>

    <table class="report-table">
      <thead>
        <tr>
          <th width="40">#</th>
          <th>Barcode</th>
          <th>Description</th>
          <th class="text-center">Qty</th>
          <th>UOM</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($details as $index => $detail): ?>
        <tr>
          <td class="text-center"><?= $index + 1 ?>.</td>
          <td class="text-center"><?= $detail->barcode ?></td>
          <td><?= $detail->description ?></td>
          <td class="text-center"><?= number_format($detail->qty_ordered) ?></td>
          <td class="text-center"><?= $detail->uom ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <br><br><br>

    <table class="table report-borderless">
      <tr>
        <td width="33%" class="text-center">
          _______________________________<br>
          Prepared By
        </td>

        <!-- <td width="34%" class="text-center">
          _______________________________<br>
          Released By
        </td> -->

        <td width="33%" class="text-center">
          _______________________________<br>
          Approved By
        </td>
      </tr>
    </table>
  </div>

      <div style="text-align:right;font-size:10px;margin-top:20px;">
        Printed By:
        <?= htmlspecialchars($this->session->userdata('username')).' '.date('m/d/Y h:i A'); ?>
      </div>

  <?php if ($i < count($documents) - 1): ?>
    <div class="page-break"></div>
  <?php endif; ?>

<?php endforeach; ?>

<?php $this->load->view('partials/reports/scripts'); ?>