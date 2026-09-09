<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <?php /*** summary */ ?>
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title">Credit Memo Information</h3>
          <?php
            $statusClass = NULL;
            switch ($creditMemo->status) {
              case 'POSTED':
                $statusClass = 'text-success';
                break;
              default:
                $statusClass = 'text-danger';
                break;
            }
          ?>
          <div class="ls-wider <?= $statusClass ?>" style="font-weight:500">[<?= htmlspecialchars($creditMemo->status) ?>]</div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <th width="180">CM No.</th>
                <td class="text-brown font-weight-500"><?= htmlspecialchars($creditMemo->cm_no) ?></td>
              </tr>
              <tr>
                <th>Credit Memo Date</th>
                <td><?= date('m/d/Y', strtotime($creditMemo->credit_memo_date)) ?></td>
              </tr>
              <tr>
                <th>Customer</th>
                <td><?= htmlspecialchars($creditMemo->customer_name) ?></td>
              </tr>
              <tr>
                <th>Source SI No.</th>
                <td>
                  <a href="<?= $creditMemo->si_url ?>" class="font-weight-500 text-olive" target="_blank">
                    <i class="fa-external-link-alt fas font-smr mr-1"></i><?= htmlspecialchars($creditMemo->si_no) ?>
                  </a>
                </td>
              </tr>
              <tr>
                <th>Source SR No.</th>
                <td>
                  <a href="<?= $creditMemo->sr_url ?>" class="font-weight-500 text-olive" target="_blank">
                    <i class="fa-external-link-alt fas font-smr mr-1"></i><?= htmlspecialchars($creditMemo->sr_no) ?>
                  </a>
                </td>
              </tr>
              <tr>
                <th>Remarks</th>
                <td><?= htmlspecialchars($creditMemo->remarks ?? '') ?></td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr>
                <th width="220">Credit Memo Amount</th>
                <td class="text-right font-weight-500"><?= number_format((float)$creditMemo->amount, 2) ?></td>
              </tr>
              <tr>
                <th>Reusable Credit Created</th>
                <td class="text-right"><?= number_format((float)$creditMemo->available_credit, 2) ?></td>
              </tr>
              <tr>
                <th>Status</th>
                <td class="text-right"><?= htmlspecialchars($creditMemo->status) ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <?php /*** details */ ?>
    <?php if (!empty($allocations)): ?>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Credit Applications</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover mb-0">
            <thead class="thead-orange">
              <tr>
                <th class="text-center">Applied On</th>
                <th class="text-center">Applied To</th>
                <th class="text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($allocations as $row): ?>
              <tr>
                <td class="text-center">
                  <?= date('m/d/Y H:i', strtotime($row->applied_on)) ?>
                </td>
                <td class="text-center">
                  <a href="<?= $row->si_url ?>" class="font-weight-500 text-olive" target="_blank">
                    <i class="fas fa-external-link-alt fa-xs mr-1"></i><?= htmlspecialchars($row->si_no) ?>
                  </a>
                </td>
                <td class="text-right">
                  <?= number_format((float)$row->amount_applied, 2) ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>