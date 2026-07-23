<?php
  $app = atlas_app();
  $company = atlas_company();
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="shortcut icon" href="<?= atlas_asset($app['shortcut_ico']) ?>" type="image/x-icon">
    <?php $this->load->view('reports/styles'); ?>
  </head>

  <body>
    <table class="report-borderless">
      <tr>
        <td width="90">
          <?php if(!empty($app->logo)) : ?>
          <img
            src="<?= atlas_asset($company->logo); ?>"
            width="70">
          <?php endif; ?>
        </td>
        <td class="text-center">
          <div class="company-name">
            <?= htmlspecialchars($app['company_name']); ?>
          </div>
          <div>
            <?= htmlspecialchars($company->address ?? ''); ?>
          </div>
          <div>
            <?= htmlspecialchars($company->contact_no ?? ''); ?>
          </div>
          <br>
          <div class="report-title">
            <?= strtoupper($title); ?>
          </div>
        </td>
        <td width="220">
          <table class="summary-table">
            <tr>
              <td width="90">
                Printed By
              </td>
              <td>
                <?= strtoupper($this->session->userdata('username')); ?>
              </td>
            </tr>
            <tr>
              <td>
                Printed On
              </td>
              <td>
                <?= date('m/d/Y h:i A'); ?>
              </td>
            </tr>
            <?php if(!empty($period)){ ?>
            <tr>
              <td>
                Period
              </td>
              <td>
                <?= $period; ?>
              </td>
            </tr>
            <?php } ?>
          </table>
        </td>
      </tr>
    </table>
    <hr style="margin:12px 0 18px;">