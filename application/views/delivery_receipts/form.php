<section class="content">
  <div class="container-fluid">

    <div class="card">
      <div class="card-header">

        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">
            Delivery Receipt Information
          </h3>

          <?php if ($isEdit) : ?>
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
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body">

        <div class="row">
          <div class="col-md-6">
            <?php
              switch (htmlspecialchars($header->status)) {
                case 'OPEN':
                  $status = '<span class="badge badge-secondary">OPEN</span>';
                  break;
                case 'POSTED':
                  $status = '<span class="badge badge-success">POSTED</span>';
                  break;
                case 'COMPLETED':
                  $status = '<span class="badge badge-primary">COMPLETED</span>';
                  break;
                default:
                  $status = '<span class="badge badge-danger">CANCELLED</span>';
                  break;
              }
            ?>

            <table class="table table-sm table-borderless">
              <tr>
                <th>DR No.</th>
                <td class="text-brown"><?= $isEdit ? $header->dr_no : 'AUTO-GENERATED' ?></td>
              </tr>
              <tr>
                <th width="180">SO No.</th>
                <td>
                  <a href="<?= base_url('sales-orders/edit/').($header->sales_order_id ?? $header->id) ?>" class="text-wrap text-olive" target="_blank"><i class="fa-external-link-alt fas font-smr mr-1"></i><?= htmlspecialchars($header->so_no) ?></a>
              </tr>
              <tr>
                <th>Order Date</th>
                <td><?= date('m/d/Y', strtotime($header->order_date)); ?></td>
              </tr>
              <tr>
                <th>Customer</th>
                <td>
                  <?= htmlspecialchars($header->customer_name); ?>
                </td>
              </tr>
              <tr>
                <th>SO Remarks</th>
                <td><?= htmlspecialchars($isEdit ? $header->so_remarks : $header->remarks) ?></td>
              </tr>
              <tr>
                <th>SO Status</th>
                <td><?= $status ?></td>
              </tr>
              <tr>
                <th>Delivery Date</th>
                <td>
                  <input type="date" id="dtDeliveryDate" class="form-control form-control-sm" value="<?= isset($deliveryReceiptId) ? $header->delivery_date : date('Y-m-d'); ?>">
                </td>
              </tr>
              <tr>
                <th>Remarks</th>
                <td>
                  <input type="text" id="txtDeliveryReceiptRemarks" class="form-control form-control-sm text-uppercase" placeholder="Delivery remarks..." value="<?= $isEdit ? htmlspecialchars($header->remarks) : '' ?>">
                </td>
              </tr>
            </table>
          </div>
        </div>


      </div>
    </div>

  </div>
</section>