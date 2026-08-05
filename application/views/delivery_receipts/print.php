<?php foreach ($documents as $i => $document): ?>

  <?php $this->load->view('reports/header'); ?>

  <?php
    $header  = $document->header;
    $details = $document->details;
    ?>

  <div class="report">
    <table class="table report-borderless table-sm mb-3">
      <tr>
        <td width="100"><strong>DR No.</strong></td>
        <td><?= $header->dr_no ?></td>
      </tr>
      <tr>
          <td><strong>SO No.</strong></td>
          <td><?= $header->so_no ?></td>
      </tr>
      <tr>
        <td><strong>Delivery Date</strong></td>
        <td><?= date('m/d/Y', strtotime($header->delivery_date)) ?></td>
      </tr>
      <tr>
        <td><strong>Customer</strong></td>
        <td><?= $header->customer_name ?></td>
      </tr>
      <tr>
        <td><strong>Salesman</strong></td>
        <td><?= $header->salesman_name ?></td>
      </tr>
      <tr>
        <td><strong>Terms</strong></td>
        <td><?= $header->terms_name ?></td>
      </tr>
      <tr>
        <td><strong>Remarks</strong></td>
        <td><?= nl2br(htmlspecialchars($header->remarks)) ?></td>
      </tr>
    </table>

    <br><br>

    <table class="table table-bordered table-sm">
      <thead>
        <tr>
          <th width="40">#</th>
          <th>Barcode</th>
          <th>Description</th>
          <th width="120" class="text-right">
            Qty
          </th>
          <th width="90">
            UOM
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($details as $index => $detail): ?>
        <tr>
          <td class="text-center">
            <?= $index + 1 ?>.
          </td>
          <td class="text-center">
            <?= $detail->barcode ?>
          </td>
          <td>
            <?= $detail->description ?>
          </td>
          <td class="text-right">
            <?= number_format($detail->qty_ordered) ?>
          </td>
          <td class="text-center">
            <?= $detail->uom ?>
          </td>
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

  <?php if ($i < count($documents) - 1): ?>
    <div class="page-break"></div>
  <?php endif; ?>

<?php endforeach; ?>

<?php $this->load->view('reports/scripts'); ?>