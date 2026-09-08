<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">
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
  </div>
</section>