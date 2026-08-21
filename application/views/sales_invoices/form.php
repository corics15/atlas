<?php
  $status = null;
?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if ($isEdit) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Sales Invoice Header
          </h3>

          <?php
            $statusClass = NULL;
            switch ($header->status) {
              case 'POSTED':
                $statusClass = 'text-success';
                break;
              case 'OPEN':
                $statusClass = 'text-secondary';
                break;
              default:
                $statusClass = 'text-danger';
                break;
            }
          ?>

          <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= $header->status ?>]</div>
        </div>

      <?php else : ?>
        <h3 class="card-title">
          Sales Invoice Header
        </h3>
      <?php endif; ?>

      </div>

      <?php /*** header */ ?>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">

            <?php
              switch (htmlspecialchars($isEdit ? $header->dr_status : $header->status)) {
                case 'OPEN':
                  $status = '<span class="badge badge-secondary">OPEN</span>';
                  break;
                case 'POSTED':
                  $status = '<span class="badge badge-success">POSTED</span>';
                  break;
                default:
                  $status = '<span class="badge badge-danger">CANCELLED</span>';
                  break;
              }
            ?>

            <table class="table table-sm table-borderless">
              <tr>
                <th>SI No.</th>
                <td class="text-brown"><?= $isEdit ? $header->si_no : 'AUTO-GENERATED' ?></td>
              </tr>
              <tr>
                <th width="180">DR No.</th>
                <td>
                  <a href="<?= $header->url ?>" class="text-olive" target="_blank"><i class="fa-external-link-alt fas font-smr mr-1"></i><?= htmlspecialchars($header->dr_no) ?></a>
              </tr>
              <tr>
                <th>Delivery Date</th>
                <td><?= date('m/d/Y', strtotime($header->delivery_date)); ?></td>
              </tr>
              <tr>
                <th>Customer</th>
                <td>
                  <?= htmlspecialchars($header->customer_name); ?>
                </td>
              </tr>
              <tr>
                <th>Salesman</th>
                <td>
                  <?= htmlspecialchars($header->salesman_name); ?>
                </td>
              </tr>
              <tr>
                <th>Terms</th>
                <td>
                  <?= htmlspecialchars($header->terms_name); ?>
                </td>
              </tr>
              <tr>
                <th>Credit Limit</th>
                <td>
                  <?= number_format($header->credit_limit, 2); ?>
                </td>
              </tr>
              <tr>
                <th>DR Remarks</th>
                <td><?= htmlspecialchars($isEdit ? $header->dr_remarks : $header->remarks) ?></td>
              </tr>
              <tr>
                <th>DR Status</th>
                <td><?= $status ?></td>
              </tr>
              <tr>
                <th>Invoice Date</th>
                <td>
                  <input type="date" id="dtInvoiceDate" class="form-control form-control-sm" value="<?= $isEdit ? $header->invoice_date : date('Y-m-d'); ?>">
                </td>
              </tr>
              <tr>
                <th>Remarks</th>
                <td>
                  <input type="text" id="txtSalesInvoiceRemarks" class="form-control form-control-sm text-uppercase" placeholder="Remarks..." value="<?= $isEdit ? htmlspecialchars($header->remarks) : '' ?>">
                </td>
              </tr>
            </table>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<input
    type="hidden"
    id="hidSalesOrderId"
    value="<?= $header->sales_order_id ?>">
<input
    type="hidden"
    id="hidSalesInvoiceId"
    value="<?= $isEdit ? $header->id : '' ?>">
<input
    type="hidden"
    id="hidDeliveryReceiptId"
    value="<?= $header->id ?>">
<input
    type="hidden"
    id="hidCustomerId"
    value="<?= $header->customer_id ?>">
<input
    type="hidden"
    id="hidSalesmanId"
    value="<?= $header->salesman_id ?>">
<input
    type="hidden"
    id="hidTermsId"
    value="<?= $header->terms_id ?>">
<input
    type="hidden"
    id="hidCreditLimit"
    value="<?= $header->credit_limit ?>">