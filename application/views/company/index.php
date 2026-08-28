<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <div class="card">

      <div class="card-header">
        <h3 class="card-title">
          Company Information
        </h3>
      </div>

      <div class="card-body">

        <form id="frmCompany">

          <input type="hidden" id="companyId" value="<?= (int)$company->id; ?>">

          <div class="form-row">

            <div class="form-group col-md-8">
              <label for="companyName">
                Company Name
              </label>

              <input type="text" id="companyName" class="form-control form-control-sm" value="<?= htmlspecialchars($company->company_name); ?>" maxlength="255" required>
            </div>

            <div class="form-group col-md-4">
              <label for="tinNo">
                TIN No.
              </label>

              <input type="text" id="tinNo" class="form-control form-control-sm" value="<?= htmlspecialchars($company->tin_no ?? ''); ?>" maxlength="100">
            </div>

          </div>

          <div class="form-group">
            <label for="address">
              Address
            </label>

            <textarea id="address" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($company->address ?? ''); ?></textarea>
          </div>

          <div class="form-row">

            <div class="form-group col-md-4">
              <label for="telephoneNo">
                Telephone No.
              </label>

              <input type="text" id="telephoneNo" class="form-control form-control-sm" value="<?= htmlspecialchars($company->telephone_no ?? ''); ?>" maxlength="100">
            </div>

            <div class="form-group col-md-4">
              <label for="mobileNo">
                Mobile No.
              </label>

              <input type="text" id="mobileNo" class="form-control form-control-sm" value="<?= htmlspecialchars($company->mobile_no ?? ''); ?>" maxlength="100">
            </div>

            <div class="form-group col-md-4">
              <label for="emailAddress">
                Email Address
              </label>

              <input type="email" id="emailAddress" class="form-control form-control-sm" value="<?= htmlspecialchars($company->email_address ?? ''); ?>" maxlength="255">
            </div>

          </div>

          <?php /*** VAT settings */ ?>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="vatMode">
                VAT Pricing Mode
              </label>

              <select id="vatMode" class="form-control form-control-sm custom-select">
                <option
                  value="INCLUSIVE"
                  <?= ($company->vat_mode ?? 'INCLUSIVE') === 'INCLUSIVE'
                    ? 'selected'
                    : '' ?>>
                  VAT Inclusive
                </option>
                <option
                  value="EXCLUSIVE"
                  <?= ($company->vat_mode ?? '') === 'EXCLUSIVE'
                    ? 'selected'
                    : '' ?>>
                  VAT Exclusive
                </option>
              </select>

              <small class="form-text text-muted">
                Determines whether selling prices already include VAT
                or VAT is added on top.
              </small>
            </div>

            <div class="form-group col-md-6">
              <label for="vatRate">
                VAT Rate (%)
              </label>
              <input type="number" id="vatRate" class="form-control form-control-sm text-right" min="0" max="100" step="0.01"
                value="<?= number_format(
                  (float)($company->vat_rate ?? 12),
                  2,
                  '.',
                  ''
                ); ?>">

              <small class="form-text text-muted">
                Example: enter 12 for 12% VAT.
              </small>
            </div>
          </div>
          <?php /*** end VAT settings */ ?>

          <div class="form-group">
            <label for="companyLogo">
              Company Logo
            </label>
            <div class="row">
              <div class="col-md-8">
                <div class="custom-file">
                  <input type="file" class="custom-file-input" id="companyLogo" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                  <label
                    class="custom-file-label"
                    for="companyLogo">
                    Choose logo...
                  </label>
                </div>

                <small class="form-text text-muted">
                  PNG or JPG, maximum 2 MB. PNG with transparent background is recommended.
                </small>

                <input type="hidden" id="currentLogo" value="<?= htmlspecialchars($company->logo ?? ''); ?>">
              </div>

              <div class="col-md-4">
                <div
                  class="border rounded bg-light d-flex align-items-center justify-content-center p-2"
                  style="height: 120px;">

                  <img
                    id="companyLogoPreview"
                    src="<?= !empty($company->logo)
                        ? atlas_asset($company->logo)
                        : ''; ?>"
                    alt="Company Logo"
                    style="
                      max-width: 100%;
                      max-height: 100px;
                      object-fit: contain;
                    "
                    <?= empty($company->logo) ? 'hidden' : ''; ?>>
                  <span
                    id="companyLogoPlaceholder"
                    class="text-muted"
                    <?= !empty($company->logo) ? 'hidden' : ''; ?>>
                    No logo
                  </span>

                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end">

            <button type="submit" class="btn btn-sm btn-default" id="btnSaveCompany">
              Save Company
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</section>