<?php $this->load->view('partials/page_header'); ?>

<section class="content">
  <div class="container-fluid">

    <?php if (!$company): ?>

      <div class="alert alert-warning">
        Company information is not available.
      </div>

    <?php else: ?>

      <div class="card">

        <div class="card-header">
          <h3 class="card-title">
            Company Information
          </h3>
        </div>

        <div class="card-body">

          <form id="frmCompany">

            <input
              type="hidden"
              id="companyId"
              value="<?= (int)$company->id; ?>">

            <div class="form-row">

              <div class="form-group col-md-8">
                <label for="companyName">
                  Company Name
                </label>

                <input
                  type="text"
                  id="companyName"
                  class="form-control form-control-sm"
                  value="<?= htmlspecialchars($company->company_name); ?>"
                  maxlength="255"
                  required>
              </div>

              <div class="form-group col-md-4">
                <label for="tinNo">
                  TIN No.
                </label>

                <input
                  type="text"
                  id="tinNo"
                  class="form-control form-control-sm"
                  value="<?= htmlspecialchars($company->tin_no ?? ''); ?>"
                  maxlength="100">
              </div>

            </div>

            <div class="form-group">
              <label for="address">
                Address
              </label>

              <textarea
                id="address"
                class="form-control form-control-sm"
                rows="3"><?= htmlspecialchars($company->address ?? ''); ?></textarea>
            </div>

            <div class="form-row">

              <div class="form-group col-md-4">
                <label for="telephoneNo">
                  Telephone No.
                </label>

                <input
                  type="text"
                  id="telephoneNo"
                  class="form-control form-control-sm"
                  value="<?= htmlspecialchars($company->telephone_no ?? ''); ?>"
                  maxlength="100">
              </div>

              <div class="form-group col-md-4">
                <label for="mobileNo">
                  Mobile No.
                </label>

                <input
                  type="text"
                  id="mobileNo"
                  class="form-control form-control-sm"
                  value="<?= htmlspecialchars($company->mobile_no ?? ''); ?>"
                  maxlength="100">
              </div>

              <div class="form-group col-md-4">
                <label for="emailAddress">
                  Email Address
                </label>

                <input
                  type="email"
                  id="emailAddress"
                  class="form-control form-control-sm"
                  value="<?= htmlspecialchars($company->email_address ?? ''); ?>"
                  maxlength="255">
              </div>

            </div>

            <div class="form-group">
              <label for="companyLogo">
                Company Logo
              </label>
              <div class="row">
                <div class="col-md-8">
                  <div class="custom-file">
                    <input
                      type="file"
                      class="custom-file-input"
                      id="companyLogo"
                      accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                    <label
                      class="custom-file-label"
                      for="companyLogo">
                      Choose logo...
                    </label>
                  </div>

                  <small class="form-text text-muted">
                    PNG or JPG, maximum 2 MB. PNG with transparent background is recommended.
                  </small>

                  <input
                    type="hidden"
                    id="currentLogo"
                    value="<?= htmlspecialchars($company->logo ?? ''); ?>">
                </div>

                <div class="col-md-4">
                  <div
                    class="border rounded bg-light d-flex align-items-center justify-content-center p-2"
                    style="height: 120px;">

                    <img
                      id="companyLogoPreview"
                      src="<?= !empty($company->logo)
                          ? base_url($company->logo)
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
    <?php endif; ?>
  </div>
</section>