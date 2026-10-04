const btnNewProduct = document.getElementById("btnNewProduct");
const btnEditProduct = document.getElementById("btnEditProduct");
const btnActivateProduct = document.getElementById("btnActivateProduct");
const btnDeactivateProduct = document.getElementById("btnDeactivateProduct");
const btnRefreshProduct = document.getElementById("btnRefreshProduct");
const btnInventoryInquiry = document.getElementById("btnInventoryInquiry");
const btnDownloadProductExcel = document.getElementById(
	"btnDownloadProductExcel",
);

const frmProduct = document.getElementById("frmProduct");

const txtBarcode = document.getElementById("txtBarcode");
const txtDescription = document.getElementById("txtDescription");
const txtCost = document.getElementById("txtCost");
const txtSRP = document.getElementById("txtSRP");
const btnGenerateBarcode = document.getElementById("btnGenerateBarcode");
const tblProductUomsBody = document.getElementById("tblProductUomsBody");
const btnProductUoms = document.getElementById("btnProductUoms");
const selProductUom = document.getElementById("selProductUom");
const txtProductUomConversion = document.getElementById(
	"txtProductUomConversion",
);
const txtProductUomSrp = document.getElementById("txtProductUomSrp");
const txtProductUomCost = document.getElementById("txtProductUomCost");
const btnAddProductUom = document.getElementById("btnAddProductUom");
const chkProductUomSales = document.getElementById("chkProductUomSales");
const chkProductUomPurchase = document.getElementById("chkProductUomPurchase");

const hidProductId = document.getElementById("hidProductId");

document.addEventListener("DOMContentLoaded", () => {
	Atlas.select.init("#selSupplier", "#mdlProduct");
	Atlas.select.init("#selUom", "#mdlProduct");
	Atlas.select.init("#selProductUom", "#mdlProductUoms");

	Atlas.table.init({
		checkbox: ".chkProduct",
		selectAll: "#chkSelectAllProduct",
		onChange: updateToolbarState,
	});

	updateToolbarState();

	/*** new */
	btnNewProduct.addEventListener("click", () => {
		frmProduct.reset();
		hidProductId.value = "";
		btnProductUoms.disabled = true;
		$("#selUom").prop("disabled", false);

		Atlas.validation.clear();
		Atlas.modal.open({
			id: "mdlProduct",
			title: "New Product",
		});
	});

	/*** save */
	frmProduct.addEventListener("submit", async (e) => {
		e.preventDefault();

		await Atlas.form.submit({
			form: frmProduct,
			url: "products/save",
			onSuccess: (result) => {
				frmProduct.reset();
				hidProductId.value = "";
				Atlas.validation.clear();

				Atlas.modal.close("mdlProduct");
				Atlas.toast.success(result.message);
				setTimeout(() => {
					Atlas.page.refresh();
				}, 1500);
			},
			onError: (result) => {
				Atlas.toast.error(result.message);
			},
		});
	});

	/*** edit */
	btnEditProduct.addEventListener("click", async () => {
		const id = getSelectedProductId();

		if (!id) {
			return;
		}

		const result = await Atlas.ajax.get(`products/get/${id}`);

		if (!result.success) {
			Atlas.toast.error(result.message);
			return;
		}

		frmProduct.reset();

		hidProductId.value = result.data.id;
		btnProductUoms.disabled = false;

		txtBarcode.value = result.data.barcode;
		txtDescription.value = result.data.description;
		txtCost.value = result.data.cost;
		txtSRP.value = result.data.srp;
		txtPkg.value = result.data.pkg;

		$("#selSupplier").val(result.data.supplier_id).trigger("change");
		$("#selUom").val(result.data.uom_id).trigger("change");
		$("#selUom").prop("disabled", true);

		Atlas.validation.clear();

		Atlas.modal.open({
			id: "mdlProduct",
			title: "Edit Product",
		});
	});

	/*** additional UOMs */
	btnProductUoms.addEventListener("click", async () => {
		const productId = hidProductId.value;

		if (!productId) {
			return;
		}

		/*** reset entry fields */
		selProductUom.value = "";
		txtProductUomConversion.value = "";
		txtProductUomCost.value = "";
		txtProductUomSrp.value = "";
		chkProductUomSales.checked = false;
		chkProductUomPurchase.checked = false;
		btnAddProductUom.innerHTML = `<i class="fas fa-plus mr-2"></i>Save`;

		const baseSrp = Atlas.format.parseNumber(txtSRP.value);
		const baseCost = Atlas.format.parseNumber(txtCost.value);
		txtProductUomCost.placeholder = `Base Cost: ${Atlas.format.parseNumber(baseCost)}`;
		txtProductUomSrp.placeholder = `Base SRP: ${Atlas.format.parseNumber(baseSrp)}`;

		/*** prevent selecting the product's base UOM */
		const baseUomId = document.getElementById("selUom").value;

		Array.from(selProductUom.options).forEach((option) => {
			option.disabled = option.value !== "" && option.value === baseUomId;
		});

		const result = await Atlas.ajax.get(`products/getUoms/${productId}`);

		if (!result.success) {
			Atlas.toast.error(result.message);
			return;
		}

		tblProductUomsBody.innerHTML = "";

		if (!result.data.length) {
			tblProductUomsBody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-muted">
          No additional UOMs.
        </td>
      </tr>
    `;
		} else {
			result.data.forEach((item) => {
				const row = document.createElement("tr");

				row.dataset.id = item.id;
				row.dataset.uomId = item.uom_id;
				row.dataset.conversionFactor = item.conversion_factor;
				row.dataset.lastCost = item.last_cost;
				row.dataset.sellingPrice = item.selling_price;
				row.dataset.isSalesUom = item.is_sales_uom;
				row.dataset.isPurchaseUom = item.is_purchase_uom;
				row.style.cursor = "pointer";

				row.innerHTML = `
          <td>${item.uom}</td>
          <td class="text-right">${Atlas.format.parseNumber(item.conversion_factor)}</td>
          <td class="text-right">${Atlas.format.parseNumber(item.last_cost)}</td>
          <td class="text-right">${Atlas.format.parseNumber(item.selling_price)}</td>
					<td class="text-center">${item.is_sales_uom === "t" ? '<i class="fas fa-check text-success"></i>' : ""}</td>
					<td class="text-center">${item.is_purchase_uom === "t" ? '<i class="fas fa-check text-success"></i>' : ""}</td>
          <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger btn-deactivate-uom p-0" title="Deactivate UOM" data-toggle="tooltip">
              <i class="fas fa-ban"></i>
            </button>
          </td>
        `;

				row.addEventListener("click", () => {
					selProductUom.value = row.dataset.uomId;

					txtProductUomConversion.value = Atlas.format.parseNumber(
						row.dataset.conversionFactor,
					);

					txtProductUomCost.value = Atlas.format.parseNumber(
						row.dataset.lastCost,
					);

					txtProductUomSrp.value = Atlas.format.parseNumber(
						row.dataset.sellingPrice,
					);

					chkProductUomSales.checked = row.dataset.isSalesUom === "t";
					chkProductUomPurchase.checked = row.dataset.isPurchaseUom === "t";
					btnAddProductUom.innerHTML = `<i class="fas fa-edit mr-2"></i>Update`;

					/*** visually mark selected row */
					tblProductUomsBody
						.querySelectorAll("tr")
						.forEach((item) => item.classList.remove("table-active"));

					row.classList.add("table-active");
				});

				tblProductUomsBody.appendChild(row);
			});
		}

		Atlas.modal.open({
			id: "mdlProductUoms",
			title: "Additional UOMs",
		});

		Atlas.ui.init();
	});

	/*** deactivate additional UOM */
	tblProductUomsBody.addEventListener("click", async (e) => {
		const button = e.target.closest(".btn-deactivate-uom");

		if (!button) {
			return;
		}

		e.stopPropagation();

		const row = button.closest("tr");

		if (!row) {
			return;
		}

		const id = row.dataset.id;
		const uomName = row.querySelector("td")?.textContent.trim();

		const confirmed = await Atlas.dialog.confirm(
			"Deactivate UOM",
			`Deactivate <span class="text-orange">${uomName}</span> for this product?`,
		);

		if (!confirmed) {
			return;
		}

		const result = await Atlas.ajax.post(`products/deactivateUom/${id}`);

		if (!result.success) {
			Atlas.toast.error(result.message);
			return;
		}

		Atlas.toast.success(result.message);
		row.remove();

		if (!tblProductUomsBody.children.length) {
			tblProductUomsBody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center text-muted">
            No additional UOMs.
          </td>
        </tr>
      `;
		}
	});

	/*** suggest additional UOM SRP */
	txtProductUomConversion.addEventListener("input", () => {
		const conversionFactor = Atlas.format.parseNumber(
			txtProductUomConversion.value,
		);

		const baseSrp = Atlas.format.parseNumber(txtSRP.value);

		if (conversionFactor <= 0 || baseSrp <= 0) {
			txtProductUomSrp.value = "";
			return;
		}

		txtProductUomSrp.value = (baseSrp * conversionFactor).toFixed(2);
	});

	/*** save additional UOM */
	btnAddProductUom.addEventListener("click", async () => {
		const productId = hidProductId.value;
		const uomId = selProductUom.value;

		const conversionFactor = Atlas.format.parseNumber(
			txtProductUomConversion.value,
		);
		const lastCost = Atlas.format.parseNumber(txtProductUomCost.value);

		const sellingPrice = Atlas.format.parseNumber(txtProductUomSrp.value);

		if (!uomId) {
			Atlas.toast.warning("Please select a UOM.");
			return;
		}

		if (conversionFactor <= 0) {
			Atlas.toast.warning("Units in Base UOM must be greater than zero.");
			return;
		}

		if (lastCost < 0) {
			Atlas.toast.warning("Cost cannot be negative.");
			return;
		}

		const result = await Atlas.ajax.post("products/saveUom", {
			product_id: productId,
			uom_id: uomId,
			conversion_factor: conversionFactor,
			last_cost: lastCost,
			selling_price: sellingPrice,
			is_sales_uom: chkProductUomSales.checked,
			is_purchase_uom: chkProductUomPurchase.checked,
		});

		if (!result.success) {
			Atlas.toast.error(result.message);
			return;
		}

		Atlas.toast.success(result.message);

		selProductUom.value = "";
		txtProductUomConversion.value = "";
		txtProductUomCost.value = "";
		txtProductUomSrp.value = "";
		chkProductUomSales.checked = false;
		chkProductUomPurchase.checked = false;
		btnAddProductUom.innerHTML = `<i class="fas fa-plus mr-2"></i>Save`;

		/*** reload additional UOM list */
		const uomResult = await Atlas.ajax.get(`products/getUoms/${productId}`);

		if (!uomResult.success) {
			Atlas.toast.error(uomResult.message);
			return;
		}

		tblProductUomsBody.innerHTML = "";

		if (!uomResult.data.length) {
			tblProductUomsBody.innerHTML = `
      <tr>
        <td colspan="7" class="text-center text-muted">
          No additional UOMs.
        </td>
      </tr>
    `;
			return;
		}

		uomResult.data.forEach((item) => {
			const row = document.createElement("tr");

			row.dataset.id = item.id;
			row.dataset.uomId = item.uom_id;
			row.dataset.conversionFactor = item.conversion_factor;
			row.dataset.lastCost = item.last_cost;
			row.dataset.sellingPrice = item.selling_price;
			row.dataset.isSalesUom = item.is_sales_uom;
			row.dataset.isPurchaseUom = item.is_purchase_uom;
			row.style.cursor = "pointer";

			row.innerHTML = `
				<td>${item.uom}</td>
				<td class="text-right">${Atlas.format.parseNumber(item.conversion_factor)}</td>
				<td class="text-right">${Atlas.format.parseNumber(item.last_cost)}</td>
				<td class="text-right">${Atlas.format.parseNumber(item.selling_price)}</td>
				<td class="text-center">${item.is_sales_uom === "t" ? '<i class="fas fa-check text-success"></i>' : ""}</td>
				<td class="text-center">${item.is_purchase_uom === "t" ? '<i class="fas fa-check text-success"></i>' : ""}</td>
				<td class="text-center">
					<button type="button" class="btn btn-sm btn-link text-danger btn-deactivate-uom p-0" title="Deactivate UOM" data-toggle="tooltip">
						<i class="fas fa-ban"></i>
					</button>
				</td>
			`;

			/*** table click event for existing UOMs */
			row.addEventListener("click", () => {
				selProductUom.value = row.dataset.uomId;

				txtProductUomConversion.value = Atlas.format.parseNumber(
					row.dataset.conversionFactor,
				);

				txtProductUomCost.value = Atlas.format.parseNumber(
					row.dataset.lastCost,
				);

				txtProductUomSrp.value = Atlas.format.parseNumber(
					row.dataset.sellingPrice,
				);

				chkProductUomSales.checked = row.dataset.isSalesUom === "t";
				chkProductUomPurchase.checked = row.dataset.isPurchaseUom === "t";

				btnAddProductUom.innerHTML = `<i class="fas fa-edit mr-2"></i>Update`;

				/*** visually mark selected row */
				tblProductUomsBody
					.querySelectorAll("tr")
					.forEach((item) => item.classList.remove("table-active"));

				row.classList.add("table-active");
			});

			tblProductUomsBody.appendChild(row);
			Atlas.ui.init();
		});
	});

	/*** activate */
	btnActivateProduct.addEventListener("click", async () => {
		const id = getSelectedProductId();

		if (!id) {
			return;
		}

		const confirmed = await Atlas.dialog.confirm(
			"Activate Product",
			"Are you sure you want to activate the selected product?",
		);

		if (!confirmed) {
			return;
		}

		const result = await Atlas.ajax.post("products/activate/" + id);

		if (result.success) {
			Atlas.toast.success(result.message);
			setTimeout(() => {
				Atlas.page.refresh();
			}, 500);
		} else {
			Atlas.toast.error(result.message);
		}
	});

	/*** deactivate */
	btnDeactivateProduct.addEventListener("click", async () => {
		const id = getSelectedProductId();

		if (!id) {
			return;
		}

		const confirmed = await Atlas.dialog.confirm(
			"Deactivate Product",
			"Are you sure you want to deactivate the selected product?",
		);

		if (!confirmed) {
			return;
		}

		const result = await Atlas.ajax.post("products/deactivate/" + id);

		if (result.success) {
			Atlas.toast.success(result.message);
			setTimeout(() => {
				Atlas.page.refresh();
			}, 500);
		} else {
			Atlas.toast.error(result.message);
		}
	});

	/*** inventory inquiry or stock ledger */
	btnInventoryInquiry.addEventListener("click", () => {
		const id = getSelectedProductId();

		if (!id) {
			return;
		}

		Atlas.page.redirect(`inventory/ledger/${Atlas.id.encode(id)}`);
	});

	/*** excel download */
	btnDownloadProductExcel?.addEventListener("click", () => {
		Atlas.excel.download(document.getElementById("tblProducts"), {
			title: "Product List",
			generatedBy: Atlas.config.userName,
			fileName: "product-list",
			sheetName: "ProductList",
		});
	});

	/*** refresh */
	btnRefreshProduct.addEventListener("click", () =>
		Atlas.page.redirect(`products`),
	);

	/*** generate barcode */
	btnGenerateBarcode?.addEventListener("click", async () => {
		const currentBarcode = txtBarcode.value.trim();

		if (currentBarcode !== "") {
			const confirmed = await Atlas.dialog.confirm(
				"Generate Barcode",
				"This will generate a new barcode, increment the barcode counter, and replace the existing barcode. Continue?",
			);

			if (!confirmed) {
				return;
			}
		}

		const result = await Atlas.ajax.get("products/generateBarcode");
		if (!result.success) {
			Atlas.toast.error(result.message);
			return;
		}

		Atlas.toast.success(result.message);
		txtBarcode.value = result.data.barcode;
		txtBarcode.focus();
		txtBarcode.select();
	});
});

const getSelectedProductId = () => {
	const checked = Atlas.table.selected();

	if (checked.length === 0) {
		Atlas.toast.warning("Please select a product.");
		return null;
	}

	if (checked.length > 1) {
		Atlas.toast.warning("Please select only one product.");
		return null;
	}

	return checked[0].value;
};

const updateToolbarState = (selected = Atlas.table.selected()) => {
	const checked = selected.length;

	btnEditProduct.disabled = checked !== 1;
	btnActivateProduct.disabled = checked !== 1;
	btnDeactivateProduct.disabled = checked !== 1;
	btnInventoryInquiry.disabled = checked !== 1;
};
