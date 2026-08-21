<?php

  $this->load->view(
    'partials/reports/header',
    [
      'title'  => 'Inventory Adjustment',
      'period' => null
    ]
  );

?>
<?php /*** DOCUMENT INFORMATION */ ?>
<table class="table report-borderless mb-3">
	<tr>
		<td width="120"><strong>Doc. No.</strong></td>
		<td><?= htmlspecialchars($inventoryAdjustment->adjustment_no) ?></td>
		<td width="120"><strong>Date</strong></td>
		<td><?= date('M d, Y', strtotime($inventoryAdjustment->adjustment_date)) ?> </td>
	</tr>
	<tr>
		<td><strong>Status</strong></td>
		<td colspan="3"> <?= htmlspecialchars($inventoryAdjustment->status) ?> </td>
	</tr>
</table>

<?php /*** REMARKS */ ?>
<table class="table report-borderless">
	<tr>
		<td>
			<strong>Remarks:</strong>
		</td>
	</tr>
	<tr>
		<td><?= nl2br(htmlspecialchars($inventoryAdjustment->remarks)) ?></td>
	</tr>
</table>

<?php /*** DETAILS */ ?>
<br>
<h4>Items</h4>
<table class="table report-table table-sm">
	<thead>
		<tr>
			<th width="5%">#</th>
			<th width="15%"> Barcode </th>
			<th> Description </th>
			<th width="10%"> UOM </th>
			<th width="12%" class="text-right"> On Hand </th>
			<th width="12%" class="text-right"> Adjustment </th>
		</tr>
	</thead>
	<tbody>
    <?php if (empty($details)): ?>
      <tr>
        <td colspan="6" class="text-center"> No items found.</td>
      </tr>
      <?php else: ?>
        <?php foreach ($details as $index => $item): ?>
          <tr>
            <td class="text-center"> <?= $index + 1 ?>.</td>
            <td> <?= htmlspecialchars($item->barcode) ?></td>
            <td> <?= htmlspecialchars($item->description) ?></td>
            <td class="text-center"> <?= htmlspecialchars($item->uom) ?></td>
            <td class="text-right"> <?= number_format($item->on_hand) ?></td>
            <td class="text-right"> <?= number_format($item->adjustment_qty) ?></td>
          </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<?php /*** SIGNATURES */ ?>
<br><br>
<table class="table report-borderless">
	<tr>
		<?php /*** PREPARED BY */ ?>
		<td width="33%" class="text-center">
			<strong> Prepared By </strong>
			<br><br><br> __________________________ <br> <?= '<em>' .
        strtoupper(
          htmlspecialchars(
            $inventoryAdjustment->entered_by_name
          )
        ) .
      '</em>' ?> <br>
			<small> <?= date(
          'M d, Y h:i A',
          strtotime(
            $inventoryAdjustment->entered_on
          )
        ) ?> </small>
		</td>
		<?php /*** LAST UPDATED BY */ ?>
		<td width="33%" class="text-center"> <?php if (
        !empty(
          $inventoryAdjustment->updated_by_name
        )
      ): ?> <strong> Last Updated By </strong>
			<br><br><br> __________________________ <br> <?= '<em>' .
          strtoupper(
            htmlspecialchars(
              $inventoryAdjustment->updated_by_name
            )
          ) .
        '</em>' ?> <br>
			<small> <?= date(
            'M d, Y h:i A',
            strtotime(
              $inventoryAdjustment->updated_on
            )
          ) ?> </small> <?php endif; ?>
		</td>

		<?php /*** POSTED / CANCELLED */ ?>
		<td width="33%" class="text-center"> <?php if (
        $inventoryAdjustment->status === 'POSTED'
      ): ?> <strong> Posted By </strong>
			<br><br><br> __________________________ <br> <?= '<em>' .
          strtoupper(
            htmlspecialchars(
              $inventoryAdjustment->posted_by_name
            )
          ) .
        '</em>' ?> <br>
			<small> <?= date(
            'M d, Y h:i A',
            strtotime(
              $inventoryAdjustment->posted_on
            )
          ) ?> </small> <?php elseif (
        $inventoryAdjustment->status === 'CANCELLED'
      ): ?> <strong> Cancelled By </strong>
			<br><br><br> __________________________ <br> <?= '<em>' .
          strtoupper(
            htmlspecialchars(
              $inventoryAdjustment->cancelled_by_name
            )
          ) .
        '</em>' ?> <br>
			<small> <?= date(
            'M d, Y h:i A',
            strtotime(
              $inventoryAdjustment->cancelled_on
            )
          ) ?> </small> <?php if (
          !empty(
            $inventoryAdjustment->cancel_reason
          )
        ): ?> <br><br>
			<strong> Reason: </strong>
			<br> <?= nl2br(
            htmlspecialchars(
              $inventoryAdjustment->cancel_reason
            )
          ) ?> <?php endif; ?> <?php endif; ?>
		</td>
	</tr>
</table>

<div style="text-align:right;font-size:10px;margin-top:20px;">
  Printed By:
  <?= htmlspecialchars($this->session->userdata('username')) ?>
  <?= date('m/d/Y h:i A') ?>
</div>

<?php $this->load->view('partials/reports/scripts'); ?>