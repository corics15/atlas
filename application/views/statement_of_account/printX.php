<?php $this->load->view('partials/reports/header'); ?>

<div class="report soa-report">

  <?php /*** main statement body */ ?>
  <div class="soa-body">


    <?php /*** statement title */ ?>
    <div class="text-center mb-4">

      <h4 class="font-weight-bold mb-1">
        STATEMENT OF ACCOUNT
      </h4>

      <div class="text-muted">
        Statement Date:
        <?= date('m/d/Y', strtotime($date_to)) ?>
      </div>

    </div>


    <?php /*** bill to + account summary */ ?>
    <div class="row mb-4">


      <?php /*** bill to */ ?>
      <div class="col-7">

        <div class="soa-section-title">
          BILL TO
        </div>

        <div class="soa-customer-box">

          <div class="soa-customer-name">
            <?= htmlspecialchars(
              $customer->customer_name ?? ''
            ) ?>
          </div>

          <?php if (!empty($customer->address)): ?>

            <div class="mt-1">
              <?= nl2br(
                htmlspecialchars($customer->address)
              ) ?>
            </div>

          <?php endif; ?>


          <div class="mt-3">

            <div>
              <strong>Customer ID:</strong>

              <?= (int)($customer->id ?? 0) ?>
            </div>


            <?php if (!empty($customer->terms_name)): ?>

              <div>
                <strong>Terms:</strong>

                <?= htmlspecialchars(
                  $customer->terms_name
                ) ?>
              </div>

            <?php endif; ?>


            <div>
              <strong>Statement Period:</strong>

              <?= date(
                'm/d/Y',
                strtotime($date_from)
              ) ?>

              -

              <?= date(
                'm/d/Y',
                strtotime($date_to)
              ) ?>
            </div>

          </div>

        </div>

      </div>


      <?php /*** account summary */ ?>
      <div class="col-5">

        <div class="soa-section-title">
          ACCOUNT SUMMARY
        </div>

        <div class="soa-summary-box">

          <div class="soa-summary-row">

            <span>
              Previous Balance
            </span>

            <span>
              <?= number_format(
                (float)$openingBalance,
                2
              ) ?>
            </span>

          </div>


          <div class="soa-summary-row">

            <span>
              New Charges
            </span>

            <span>
              <?= number_format(
                (float)$periodInvoiced,
                2
              ) ?>
            </span>

          </div>


          <div class="soa-summary-row">

            <span>
              Payments / Credits
            </span>

            <span>
              <?= number_format(
                (float)$periodPaid,
                2
              ) ?>
            </span>

          </div>


          <div class="soa-summary-total">

            <span>
              BALANCE DUE
            </span>

            <span>
              <?= number_format(
                (float)$amountDue,
                2
              ) ?>
            </span>

          </div>

        </div>

      </div>

    </div>


    <?php /*** transaction details */ ?>
    <table
      class="table table-sm soa-transactions"
      style="width:100%; table-layout:auto;">

      <thead>

        <tr>

          <th
            width="110"
            class="text-center">
            Date
          </th>

          <th
            width="170"
            class="text-center">
            Reference
          </th>

          <th>
            Description
          </th>

          <th
            width="130"
            class="text-right">
            Charges
          </th>

          <th
            width="130"
            class="text-right">
            Credits
          </th>

          <th
            width="130"
            class="text-right">
            Balance
          </th>

        </tr>

      </thead>


      <tbody>


        <?php /*** opening balance */ ?>
        <?php if ((float)$openingBalance != 0): ?>

          <tr class="soa-balance-forward">

            <td></td>

            <td></td>

            <td>
              <strong>
                Previous Balance (Forwarded)
              </strong>
            </td>

            <td></td>

            <td></td>

            <td class="text-right">

              <strong>
                <?= number_format(
                  (float)$openingBalance,
                  2
                ) ?>
              </strong>

            </td>

          </tr>

        <?php endif; ?>


        <?php /*** no transactions */ ?>
        <?php if (empty($transactions)): ?>

          <tr>

            <td
              colspan="6"
              class="text-center">

              No transactions found for the selected period.

            </td>

          </tr>


        <?php /*** transactions */ ?>
        <?php else: ?>

          <?php foreach ($transactions as $row): ?>

            <?php

              $description = '';

              if (
                $row->transaction_type ===
                'SALES INVOICE'
              ) {

                $description = 'Sales Invoice';

              }

              elseif (
                $row->transaction_type ===
                'CUSTOMER PAYMENT'
              ) {

                $description = 'Payment Received';

              }

              else {

                $description =
                    $row->transaction_type;

              }

            ?>


            <tr>

              <td
                class="text-center"
                style="white-space:nowrap;">

                <?= date(
                  'm/d/Y',
                  strtotime(
                    $row->transaction_date
                  )
                ) ?>

              </td>


              <td
                class="text-center"
                style="white-space:nowrap;">

                <?= htmlspecialchars(
                  $row->reference_no
                ) ?>

              </td>


              <td>

                <?= htmlspecialchars(
                  $description
                ) ?>

              </td>


              <td
                class="text-right"
                style="white-space:nowrap;">

                <?= (float)$row->debit > 0
                  ? number_format(
                      (float)$row->debit,
                      2
                    )
                  : '' ?>

              </td>


              <td
                class="text-right"
                style="white-space:nowrap;">

                <?= (float)$row->credit > 0
                  ? number_format(
                      (float)$row->credit,
                      2
                    )
                  : '' ?>

              </td>


              <td
                class="text-right"
                style="white-space:nowrap;">

                <?= number_format(
                  (float)$row->balance,
                  2
                ) ?>

              </td>

            </tr>

          <?php endforeach; ?>

        <?php endif; ?>


      </tbody>

    </table>


  </div>
  <?php /*** end main statement body */ ?>


  <?php /*** statement footer */ ?>
  <div class="soa-footer">

    <div class="soa-balance">

      <div class="row align-items-center">

        <div
          class="col-8 text-right soa-balance-label">

          ACCOUNT CURRENT BALANCE

        </div>

        <div
          class="col-4 text-right soa-balance-amount">

          <?= number_format(
            (float)$amountDue,
            2
          ) ?>

        </div>

      </div>

    </div>


    <div class="text-center mt-3">
      Thank you for your business!
    </div>

  </div>
  <?php /*** end statement footer */ ?>


</div>


<?php $this->load->view('partials/reports/scripts'); ?>