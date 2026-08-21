<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">
          Dashboard
        </h1>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <div class="row">

      <?php /*** ACTIVE CUSTOMERS */ ?>
      <div class="col-lg-3 col-md-6">
        <div class="small-box bg-gray">
          <div class="inner">
            <h3>
              <?= number_format($activeCustomerCount); ?>
            </h3>

            <p>Active Customers</p>
          </div>

          <div class="icon">
            <i class="fas fa-users"></i>
          </div>

          <a
            href="<?= base_url('customers/'); ?>"
            class="font-smr small-box-footer"
          >
            View Customers
            <i class="fas fa-arrow-circle-right ml-1"></i>
          </a>
        </div>
      </div>


      <?php /*** ACTIVE PRODUCTS */ ?>
      <div class="col-lg-3 col-md-6">
        <div class="small-box bg-olive">
          <div class="inner">
            <h3>
              <?= number_format($activeProductCount); ?>
            </h3>

            <p>Active Products</p>
          </div>

          <div class="icon">
            <i class="fas fa-boxes"></i>
          </div>

          <a
            href="<?= base_url('products/'); ?>"
            class="font-smr small-box-footer"
          >
            View Products
            <i class="fas fa-arrow-circle-right ml-1"></i>
          </a>
        </div>
      </div>


      <?php /*** INVENTORY QUANTITY */ ?>
      <div class="col-lg-3 col-md-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>
              <?= number_format($totalInventoryQty, 0); ?>
            </h3>

            <p>Inventory Quantity</p>
          </div>

          <div class="icon">
            <i class="fas fa-cubes"></i>
          </div>

          <a
            href="<?= base_url('inventory/'); ?>"
            class="font-smr small-box-footer"
          >
            View Inventory
            <i class="fas fa-arrow-circle-right ml-1"></i>
          </a>
        </div>
      </div>


      <?php /*** INVENTORY VALUE */ ?>
      <div class="col-lg-3 col-md-6">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3>
              <?= number_format($totalInventoryAmount, 2); ?>
            </h3>

            <p>Inventory Value</p>
          </div>

          <div class="icon">
            <i class="fas fa-coins"></i>
          </div>

          <a
            href="<?= base_url('inventory/'); ?>"
            class="font-smr small-box-footer"
          >
            View Inventory
            <i class="fas fa-arrow-circle-right ml-1"></i>
          </a>
        </div>
      </div>

    </div>

    <div class="row">

      <?php /*** SALES TODAY */ ?>
      <div class="col-lg-3 col-md-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>
              <?= number_format($salesToday, 2); ?>
            </h3>

            <p>Sales Today</p>
          </div>

          <div class="icon">
            <i class="fas fa-chart-line"></i>
          </div>

          <a href="<?= $salesTodayUrl ?>" class="font-smr small-box-footer">
            View Sales Today
            <i class="fas fa-arrow-circle-right ml-1"></i>
          </a>
        </div>
      </div>

      <?php /*** SALES THIS MONTH */ ?>
      <div class="col-lg-3 col-md-6">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3>
              <?= number_format($salesThisMonth, 2); ?>
            </h3>

            <p>Sales This Month</p>
          </div>

          <div class="icon">
            <i class="fas fa-calendar-alt"></i>
          </div>

          <a href="<?= $salesThisMonthUrl ?>" class="font-smr small-box-footer">
            View Sales This Month
            <i class="fas fa-arrow-circle-right ml-1"></i>
          </a>
        </div>
      </div>

      <?php /*** OPEN SALES ORDERS */ ?>
      <div class="col-lg-3 col-md-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>
              <?= number_format($openSalesOrderCount); ?>
            </h3>

            <p>Open Sales Orders</p>
          </div>

          <div class="icon">
            <i class="fas fa-file-invoice"></i>
          </div>

          <a href="<?= $openSalesOrdersUrl ?>" class="font-smr small-box-footer">
            View
            <i class="fas fa-arrow-circle-right ml-1"></i>
          </a>
        </div>
      </div>

      <?php /*** OPEN PURCHASE ORDERS */ ?>
      <div class="col-lg-3 col-md-6">
        <div class="small-box bg-secondary">
          <div class="inner">
            <h3>
              <?= number_format($openPurchaseOrderCount); ?>
            </h3>

            <p>Open Purchase Orders</p>
          </div>

          <div class="icon">
            <i class="fas fa-shopping-cart"></i>
          </div>

          <a href="<?= $openPurchaseOrdersUrl ?>" class="font-smr small-box-footer">
            View
            <i class="fas fa-arrow-circle-right ml-1"></i>
          </a>
        </div>
      </div>

    </div>

    <div class="row">

      <?php /*** Top 5 Selling Products */ ?>
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-trophy mr-2"></i>
              Top Selling Products
            </h3>
          </div>

          <div class="card-body p-0">

            <div class="table-responsive">

              <table class="table table-sm table-hover mb-0">
                <thead class="thead-orange">
                  <tr>
                    <th style="width: 45px;">#</th>
                    <th>Product</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Sales</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($topSellingProducts)) : ?>
                    <?php foreach ($topSellingProducts as $index => $product) : ?>
                      <tr>
                        <td>
                          <?= $index + 1; ?>
                        </td>
                        <td>
                          <?php
                            $description = htmlspecialchars($product->description);
                            echo (mb_strlen($description) > 30)
                              ? mb_strimwidth($description, 0, 30, '...')
                              : $description;
                          ?>
                        </td>
                        <td class="text-right">
                          <?= number_format((float) $product->total_qty); ?>
                        </td>
                        <td class="text-right">
                          <?= number_format((float) $product->total_amount, 2); ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <tr>
                      <td
                        colspan="4"
                        class="text-center text-muted py-4"
                      >
                        No sales data available.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>

      <?php /*** Recent Sales */ ?>
      <div class="col-lg-6">
        <div class="card">

          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-receipt mr-2"></i>
              Recent Sales
            </h3>

            <div class="card-tools">
              <a
                href="<?= base_url('sales-invoices'); ?>"
                class="btn btn-tool font-smr"
                data-toggle="tooltip"
                title="View Sales Invoices"
              >
                View All
                <i class="fas fa-arrow-right ml-1"></i>
              </a>
            </div>
          </div>

          <div class="card-body p-0">
            <div class="table-responsive">

              <table class="table table-hover table-sm mb-0">
                <thead class="thead-orange">
                  <tr>
                    <th class="text-center">SI No.</th>
                    <th class="text-center">Date</th>
                    <th>Customer</th>
                    <th class="text-right">Amount</th>
                  </tr>
                </thead>
                <tbody>

                  <?php if (!empty($recentSales)) : ?>
                    <?php foreach ($recentSales as $sale) : ?>
                      <tr>
                        <td class="text-center">
                          <a href="<?= $sale->url; ?>" class="text-wrap text-olive" target="_blank">
                            <i class="fas fa-external-link-alt fa-xs mr-1"></i>
                            <?= $sale->si_no ?>
                          </a>
                        </td>
                        <td class="text-center"><?= date('m/d/Y', strtotime($sale->invoice_date)); ?></td>
                        <td>
                          <?php
                            $customerName = htmlspecialchars($sale->customer_name);
                            echo (mb_strlen($customerName) > 30)
                              ? mb_strimwidth($customerName, 0, 30, '...')
                              : $customerName;
                          ?>
                        </td>
                        <td class="text-right"><?= number_format($sale->total_amount, 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <tr>
                      <td colspan="4" class="text-center text-muted py-4">
                        No recent sales found.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="row">

      <?php /*** Sales - Last 6 Months */ ?>
      <div class="col-8">
        <div class="card">

          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-chart-line mr-2"></i>
              Sales — Last 6 Months
            </h3>
          </div>

          <div class="card-body">
            <div style="height: 300px;">
              <canvas id="salesTrendChart"></canvas>
            </div>
          </div>

        </div>
      </div>

      <?php /*** Inventory Alerts */ ?>
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              Inventory Alerts
            </h3>

            <div class="card-tools">
              <a
                href="<?= base_url('inventory'); ?>"
                class="btn btn-tool font-smr"
                data-toggle="tooltip"
                title="View Inventory"
              >
                View All
                <i class="fas fa-arrow-right ml-1"></i>
              </a>
            </div>
          </div>

          <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">
              <span>
                Out of Stock
              </span>
              <span class="badge badge-danger">
                <?= number_format($outOfStockCount); ?>
              </span>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
  window.dashboardSalesTrend = <?= json_encode($salesTrend); ?>;
</script>