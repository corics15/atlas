<div class="mb-0">
  <div class="card-header">
    <h3 class="card-title">
      Product Breakdown <span class="text-orange"> — <?= !empty($salesPerCustomerProducts) ? htmlspecialchars($salesPerCustomerProducts[0]->customer_name) : '' ?></span>
    </h3>

    <div class="card-tools mr-0">
      <?php $this->load->view('reports/toolbar'); ?>
    </div>
  </div>

  <div class="card-body">
    <div class="table-responsive">

      <table class="table table-sm table-bordered table-hover mb-0" id="tblSalesPerCustomerProducts">
        <thead class="thead-orange">
          <tr>
            <th>Product</th>
            <th class="text-center">UOM</th>
            <th class="text-right">Invoices</th>
            <th class="text-right">Qty Sold</th>
            <th class="text-right">Gross Sales</th>
            <th class="text-right">Discount</th>
            <th class="text-right">Net Sales</th>
          </tr>
        </thead>

        <tbody>
          <?php if (!empty($salesPerCustomerProducts)): ?>
            <?php
              $grossTotal = 0;
              $discountTotal = 0;
              $netTotal = 0;
            ?>

            <?php foreach ($salesPerCustomerProducts as $row): ?>
              <?php
                $grossSales = (float)$row->gross_sales;
                $discountAmount = (float)$row->discount_amount;
                $netSales = (float)$row->net_sales;

                $grossTotal += $grossSales;
                $discountTotal += $discountAmount;
                $netTotal += $netSales;
              ?>

              <tr>
                <td><?= htmlspecialchars($row->product_description) ?></td>
                <td class="text-center" data-a-h="center"><?= htmlspecialchars($row->uom) ?></td>
                <td class="text-right" data-t="n" data-num-fmt="#,##0"><?= number_format((int)$row->invoice_count) ?></td>
                <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format((float)$row->qty_sold, 2) ?></td>
                <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format($grossSales, 2) ?></td>
                <td class="text-right" data-t="n" data-num-fmt="#,##0.00"><?= number_format($discountAmount, 2) ?></td>
                <td class="font-weight-500 text-right" data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($netSales, 2) ?></td>
              </tr>
            <?php endforeach; ?>

          <?php else: ?>

            <tr>
              <td colspan="7" class="text-center text-muted py-3">
                No product sales found.
              </td>
            </tr>

          <?php endif; ?>

        </tbody>

        <?php if (!empty($salesPerCustomerProducts)): ?>

          <tfoot class="font-weight-500 text-info text-right">
            <tr>
              <td colspan="2" data-f-bold="true" data-a-h="right">Grand Total</td>
              <td data-a-h="right">-</td>
              <td data-a-h="right">-</td>
              <td data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($grossTotal, 2) ?></td>
              <td data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($discountTotal, 2) ?></td>
              <td data-t="n" data-num-fmt="#,##0.00" data-f-bold="true"><?= number_format($netTotal, 2) ?></td>
            </tr>
          </tfoot>

        <?php endif; ?>
      </table>
    </div>
  </div>
</div>