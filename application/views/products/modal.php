<div class="modal fade" id="mdlProduct" tabindex="-1" aria-labelledby="mdlProductLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlProductLabel">Product Information</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmProduct">
        <input type="hidden" id="hidProductId" name="id">

        <div class="modal-body">
          <div class="form-row">

            <div class="form-group col-md-6">
              <label for="selSupplier">Supplier</label>
              <select id="selSupplier" name="supplier_id" class="form-control form-control-sm">
                <option value="">Select Supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                  <option value="<?= $supplier->id; ?>">
                    <?= htmlspecialchars($supplier->supplier_name); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small id="errSupplierId" class="text-danger"></small>
            </div>

            <div class="form-group col-md-6">
              <label for="txtBarcode">Item Barcode</label>
              <div class="input-group input-group-sm">
                <input type="text" id="txtBarcode" name="barcode" class="form-control form-control-sm" placeholder="Enter barcode">
                <div class="input-group-append">
                  <button type="button" id="btnGenerateBarcode" class="btn btn-default" title="Generate Barcode" data-toggle="tooltip">
                    <i class="fas fa-barcode"></i>
                  </button>
                </div>
              </div>
              <small id="errBarcode" class="text-danger"></small>
            </div>

          </div>

          <div class="form-row">

            <div class="form-group col-md-6">
              <label for="txtCaseBarcode">Case Barcode</label>
              <input type="text" id="txtCaseBarcode" name="case_barcode" class="form-control form-control-sm text-uppercase" placeholder="Enter case barcode">
              <small id="errCaseBarcode" class="text-danger"></small>
            </div>

            <div class="form-group col-md-6">
              <label for="txtDescription">Product Name</label>
              <input type="text" id="txtDescription" name="description" class="form-control form-control-sm text-uppercase" placeholder="Enter product name">
              <small id="errDescription" class="text-danger"></small>
            </div>

          </div>

          <div class="form-row">

            <div class="form-group col-md-6">
              <label for="txtCost">Base Cost</label>
              <input type="number" id="txtCost" name="cost" step="any" class="form-control form-control-sm" placeholder="Enter cost">
              <small id="errCost" class="text-danger"></small>
            </div>

            <div class="form-group col-md-6">
              <label for="txtSRP">Base SRP</label>
              <input type="number" id="txtSRP" name="srp" step="any" class="form-control form-control-sm" placeholder="Enter SRP">
              <small id="errSRP" class="text-danger"></small>
            </div>

          </div>

          <div class="form-row">

            <div class="form-group col-md-6">
              <label for="selUom">Base UOM</label>
              <select id="selUom" name="uom_id" class="form-control form-control-sm">
                <option value="">Select UOM</option>
                <?php foreach ($uoms as $uom): ?>
                  <option value="<?= $uom->id; ?>">
                    <?= htmlspecialchars($uom->uom); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small id="errUomId" class="text-danger"></small>
            </div>

            <div class="form-group col-md-6">
              <label for="txtPkg">Packing</label>
              <input type="text" id="txtPkg" name="pkg" class="form-control form-control-sm" placeholder="Enter packing">
              <small id="errPkg" class="text-danger"></small>
            </div>

          </div>

          <div class="form-row">
            <div class="form-group col-12 mb-0">
              <button type="button" id="btnProductUoms" class="btn btn-sm btn-default" disabled>
                <i class="fas fa-boxes mr-1"></i>
                Additional UOMs
              </button>
              <small class="text-muted ml-2">
                Save the product first to manage additional UOMs.
              </small>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" id="btnSaveProduct" class="btn btn-sm btn-default">Save Product</button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php /*** additional UOMs */ ?>
<div class="modal fade" id="mdlProductUoms" tabindex="-1" aria-labelledby="mdlProductUomsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="mdlProductUomsLabel">Additional UOMs</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="alert alert-light border small py-2 px-3 mb-3">
        <div class="font-weight-500 mb-1">
          <i class="fas fa-info-circle mr-1 text-info"></i>
          Quick Guide
        </div>
        <div>
          <span class="font-weight-500 text-red">Units in Base UOM</span> is the number of base units contained
          in one selected UOM. <span class="text-brown">Example:</span> if the Base UOM is PACK and 1 BAG contains
          20 PACKS, enter <span class="font-weight-500 text-indigo">20</span>.
        </div>
        <div class="mt-1">
          SRP is <span class="font-weight-500">"suggested"</span> from <span class="font-weight-500 text-orange">Units in Base UOM x Base SRP</span>, but may be
          changed to the actual selling price.
        </div>
        <div class="mt-1">
          Click an existing UOM below to edit or deactivate it. To reactivate, simply select the deactivated UOM to reactivate.
        </div>
      </div>

      <div class="modal-body">

        <div class="form-row align-items-end mb-3">

          <div class="form-group col-md-4 mb-0">
            <label for="selProductUom">UOM</label>
            <select id="selProductUom" class="form-control form-control-sm custom-select">
              <option value="">Select UOM</option>
              <?php foreach ($uoms as $uom): ?>
                <option value="<?= $uom->id; ?>">
                  <?= htmlspecialchars($uom->uom); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group col-md-3 mb-0">
            <label for="txtProductUomConversion">Units in Base UOM</label>
            <input type="number" id="txtProductUomConversion" class="form-control form-control-sm" min="0.0001" step="any" placeholder="e.g. 20">
          </div>

          <div class="form-group col-md-3 mb-0">
            <label for="txtProductUomSrp">SRP</label>
            <input type="number" id="txtProductUomSrp" class="form-control form-control-sm" min="0" step="any">
          </div>

          <div class="form-group col-md-2 mb-0">
            <button type="button" id="btnAddProductUom" class="btn btn-sm btn-link btn-block">
              <i class="fas fa-plus mr-1"></i>
              Add
            </button>
          </div>

        </div>

        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="thead-orange">
              <tr>
                <th>UOM</th>
                <th class="text-right">Units in Base UOM</th>
                <th class="text-right">SRP</th>
                <th class="text-center"></th>
              </tr>
            </thead>
            <tbody id="tblProductUomsBody">
              <tr>
                <td colspan="4" class="text-center text-muted">
                  No additional UOMs.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>
  </div>
</div>