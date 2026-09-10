<?php
  $app = atlas_app();
  $company = atlas_company();
?>

<style>
  body {
    font-family: helvetica;
    /* font-size: 9px; */
    color: #000;
  }
  table {
    width: 100%;
    font-size: 9px;
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
  .report-table {
    width: 100%;
    border-collapse: collapse;
  }
  .report-table th,
  .report-table td {
    border: 0.5px solid #000;
    /* padding: 5px; */
  }
  .font-7 {
		font-size: 7px;
	}
  .font-8 {
		font-size: 8px;
	}
  .font-12 {
		font-size: 12px;
	}
  .font-14 {
		font-size: 14px;
	}
  .font-weight-bold {
    font-weight: bold;
  }
  .border-left-end {
    border-left: 0.5px solid #000;
    border-bottom: 0.5px solid #000;
    border-top: 0.5px solid #000;
  }
  .border-right-end {
    border-right: 0.5px solid #000;
    border-bottom: 0.5px solid #000;
    border-top: 0.5px solid #000;
  }
  .border-top-bottom {
    border-bottom: 0.5px solid #000;
    border-top: 0.5px solid #000;
  }
  .border-left-bottom-right {
    border-left: 0.2px solid #000;
    border-bottom: 0.2px solid #000;
    border-right: 0.2px solid #000;
  }
  .border-bottom-right {
    border-bottom: 0.2px solid #000;
    border-right: 0.2px solid #000;
  }
  .border-top-left-bottom {
    border-bottom: 0.2px solid #000;
    border-top: 0.2px solid #000;
    border-left: 0.2px solid #000;
  }
  .border-top-left-bottom-right {
    border-bottom: 0.2px solid #000;
    border-left: 0.2px solid #000;
    border-top: 0.2px solid #000;
    border-right: 0.2px solid #000;
  }
</style>

<table class="report-borderless">
  <tr>
    <td class="text-center" width="13%">
      <?php if (!empty($company->logo)) : ?>
        <img src="<?= atlas_asset($company->logo); ?>" width="75" alt="<?= htmlspecialchars($company->company_name); ?>">
      <?php endif; ?>
    </td>
    <td width="0.5%" style="background-color:#37474f;"></td>
    <td width="39.5%">
      <table style="line-height:12px">
        <tr>
          <td class="font-12 font-weight-bold"><?= htmlspecialchars($company->company_name); ?></td>
        </tr>
        <tr>
          <td class="font-8"><?= htmlspecialchars($company->address ?? ''); ?></td>
        </tr>
        <tr>
          <td class="font-8"><?= 'TIN: '.htmlspecialchars($company->tin_no ?? ''); ?></td>
        </tr>
        <tr>
          <td><?= htmlspecialchars($company->contact_no ?? ''); ?></td>
        </tr>
      </table>
    </td>
    <td width="25%"></td>
    <td width="0.5%" style="background-color:#37474f;"></td>
    <td width="21.5%" class="font-14 font-weight-bold"><br><br><?= ucwords($title); ?></td>
  </tr>
</table>

<br><br>