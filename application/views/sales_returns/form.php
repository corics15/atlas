<?php
  $isFromSalesInvoice = isset($salesInvoice);
  $status = $isEdit ? $header->status : null;
?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">

      <?php if (isset($header)) : ?>

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Sales Return Information
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
          Sales Return Information
        </h3>
      <?php endif; ?>

      </div>

      <?php /*** header */ ?>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">

            <?php
              switch (htmlspecialchars($salesInvoice->status)) {
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
                <th>SR No.</th>
                <td class="text-brown"><?= $isEdit ? $header->sr_no : 'AUTO-GENERATED' ?></td>
              </tr>
              <tr>
                <th width="180">SI No.</th>
                <td>
                  <a href="<?= $salesInvoice->url//base_url('sales-invoices/edit/').$salesInvoice->id ?>" class="text-wrap text-olive" target="_blank"><i class="fa-external-link-alt fas font-smr mr-1"></i><?= htmlspecialchars($salesInvoice->si_no) ?></a>
              </tr>
              <tr>
                <th>Customer</th>
                <td>
                  <?= htmlspecialchars($salesInvoice->customer_name); ?>
                </td>
              </tr>
              <tr>
                <th>Salesman</th>
                <td>
                  <?= htmlspecialchars($salesInvoice->salesman_name); ?>
                </td>
              </tr>
              <tr>
                <th>Terms</th>
                <td>
                  <?= htmlspecialchars($salesInvoice->terms_name); ?>
                </td>
              </tr>
              <tr>
                <th>Credit Limit</th>
                <td>
                  <?= number_format($salesInvoice->credit_limit, 2); ?>
                </td>
              </tr>
              <tr>
                <th>SI Remarks</th>
                <td><?= htmlspecialchars($salesInvoice->remarks) ?>
                </td>
              </tr>
              <tr>
                <th>SI Status</th>
                <td><?= $status ?></td>
              </tr>
              <tr>
                <th>Return Date</th>
                <td>
                  <input type="date" id="dtSalesReturnDate" class="form-control form-control-sm w-auto" value="<?= $isEdit ? date('Y-m-d', strtotime($header->return_date)) : date('Y-m-d'); ?>">
                </td>
              </tr>
              <tr>
                <th>Remarks</th>
                <td>
                  <input type="text" id="txtSalesReturnRemarks" class="form-control form-control-sm text-uppercase" placeholder="Remarks..." value="<?= $isEdit ? htmlspecialchars($header->remarks) : '' ?>">
                </td>
              </tr>
            </table>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<input type="hidden" id="hidSalesInvoiceId" value="<?= $salesInvoice->id ?>">
<input type="hidden" id="hidSalesReturnId" value="<?= $isEdit ? $header->id : '' ?>">