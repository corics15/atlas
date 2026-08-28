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
    <?php $this->load->view('partials/reports/styles'); ?>
  </head>

  <body>
    <table class="report-borderless" style="table-layout:auto" cellpadding="0" cellspacing="0">
      <tr>
        <td class="text-right" width="15%">
          <?php if (!empty($company->logo)) : ?>
            <img src="<?= atlas_asset($company->logo); ?>" width="75" alt="<?= htmlspecialchars($company->company_name); ?>">
          <?php endif; ?>
        </td>
        <td class="text-center">
          <table style="line-height:8px">
            <tbody>
              <tr>
                <td class="company-name"><?= htmlspecialchars($company->company_name); ?></td>
              </tr>
              <tr>
                <td><?= htmlspecialchars($company->address ?? ''); ?></td>
              </tr>
              <tr>
                <td><?= htmlspecialchars($company->contact_no ?? ''); ?></td>
              </tr>
              <tr>
                <td class="report-title"><?= strtoupper($title); ?></td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </table>
    <hr style="margin:12px 0 18px;">