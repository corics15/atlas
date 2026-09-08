<?php
  $app = atlas_app();
  $company = atlas_company();
?>

<?php if (empty($pdfMode)): ?>
  <!DOCTYPE html>
  <html>
    <head>
      <meta charset="utf-8">
      <title><?= htmlspecialchars($title) ?></title>
      <link rel="shortcut icon" href="<?= atlas_asset($app['shortcut_ico']) ?>" type="image/x-icon">
      <?php $this->load->view('partials/reports/styles'); ?>
    </head>

    <body>

<?php else: ?>
  <style>
    body {
      font-family: helvetica;
      font-size: 9pt;
      color: #000;
    }
    table {
      width: 100%;
    }
    th {
      font-weight: bold;
      background-color: #efefef;
    }
    .text-center {
      text-align: center;
    }
    .text-right {
      text-align: right;
    }
    .report-borderless td,
    .report-borderless th {
      border: none;
    }
  </style>
<?php endif; ?>

  <table class="report-borderless" style="table-layout:auto">
    <tr>
      <td class="text-center" width="13%">
        <?php if (!empty($company->logo)) : ?>
          <img src="<?= atlas_asset($company->logo); ?>" width="75" alt="<?= htmlspecialchars($company->company_name); ?>">
        <?php endif; ?>
      </td>
      <td width="40%" style="border-left:5px solid #37474f">
        <table style="line-height:12px">
          <tbody>
            <tr>
              <td class="company-name"><?= htmlspecialchars($company->company_name); ?></td>
            </tr>
            <tr>
              <td><?= htmlspecialchars($company->address ?? ''); ?></td>
            </tr>
            <tr>
              <td><?= 'TIN: '.htmlspecialchars($company->tin_no ?? ''); ?></td>
            </tr>              
            <tr>
              <td><?= htmlspecialchars($company->contact_no ?? ''); ?></td>
            </tr>
          </tbody>
        </table>
      </td>
      <td width="25%"><?php /*** filler element */ ?></td>
      <td class="report-title" width="15%" style="text-align:left;border-left:5px solid #37474f"><?= ucwords($title); ?></td>
    </tr>
  </table>
  <hr style="margin:12px 0 18px;">