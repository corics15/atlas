<?php $this->load->view('partials/page_header'); ?>
<?php $this->load->view('customer_payments/form'); ?>
<?php $this->load->view('customer_payments/outstanding_invoices'); ?>

<script>
  window.customerPaymentId = <?= (int)($customerPaymentId ?? 0); ?>;
  window.customerPaymentAllocations = <?= json_encode(
    array_map(function ($allocation) {
      return [
        'sales_invoice_id' => (int)$allocation->sales_invoice_id,
        'amount_applied' => (float)$allocation->amount_applied
      ];
    }, $allocations ?? [])
  ); ?>;
</script>