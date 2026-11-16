<?php
/** Printable sales receipt. Staff can view any receipt; customers only their own. */
require __DIR__ . '/app/bootstrap.php';
require_role('admin', 'cashier', 'customer');

$s = SalesService::find(input_int('id', 'GET'));
if (!$s || (Auth::role() === 'customer' && (int)$s['customer_id'] !== Auth::customerId())) {
    http_response_code(404);
    flash('danger', 'Receipt not found.');
    redirect(Auth::homeFor(Auth::role()));
}
$rate = (float)setting('vat_rate', '16');
$pageTitle = 'Receipt ' . $s['receipt_no'];
$active = Auth::role() === 'customer' ? 'purchases' : 'sales';
require OSMS_ROOT . '/app/layout/header.php';
?>
<div class="d-flex gap-2 justify-content-center mb-3 no-print">
  <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
  <a class="btn btn-outline-secondary" href="javascript:history.back()">Back</a>
  <?php if (Auth::role() === 'admin' && $s['status'] === 'completed'): ?>
    <a class="btn btn-outline-danger" href="<?= url('admin/sales.php?void=' . (int)$s['id']) ?>"><i class="bi bi-x-octagon me-1"></i> Void sale</a>
  <?php endif; ?>
</div>
<div class="receipt" id="receipt">
  <div class="text-center">
    <div class="big"><?= e(mb_strtoupper(setting('store_name'))) ?></div>
    <div><?= e(setting('store_location')) ?></div>
    <div>Tel: <?= e(setting('store_phone')) ?> · KRA PIN: <?= e(setting('kra_pin')) ?></div>
  </div>
  <hr>
  <table>
    <tr><td>Receipt</td><td class="r"><?= e($s['receipt_no']) ?></td></tr>
    <tr><td>Date</td><td class="r"><?= e(fmt_date($s['created_at'], 'd/m/Y H:i')) ?></td></tr>
    <tr><td>Served by</td><td class="r"><?= e($s['cashier']) ?></td></tr>
    <?php if ($s['customer']): ?><tr><td>Customer</td><td class="r"><?= e($s['customer']) ?> (<?= e($s['customer_no']) ?>)</td></tr><?php endif; ?>
  </table>
  <?php if ($s['status'] === 'voided'): ?>
    <div class="void-stamp">VOIDED</div>
    <div class="small">Voided by <?= e($s['voided_by_name']) ?> on <?= e(fmt_date($s['voided_at'], 'd/m/Y H:i')) ?>: <?= e($s['void_reason']) ?></div>
  <?php endif; ?>
  <hr>
  <table>
    <?php foreach ($s['items'] as $i): ?>
      <tr><td colspan="2"><?= e($i['product_name']) ?><?= $i['taxable'] ? '' : ' (E)' ?></td></tr>
      <tr><td>&nbsp;&nbsp;<?= (int)$i['quantity'] ?> x <?= money($i['unit_price'], false) ?></td><td class="r"><?= money($i['line_total'], false) ?></td></tr>
    <?php endforeach; ?>
  </table>
  <hr>
  <table>
    <tr><td>Subtotal</td><td class="r"><?= money($s['subtotal'], false) ?></td></tr>
    <?php if ((float)$s['discount'] > 0): ?><tr><td>Discount</td><td class="r">-<?= money($s['discount'], false) ?></td></tr><?php endif; ?>
    <tr class="big"><td>TOTAL <?= e(setting('currency')) ?></td><td class="r"><?= money($s['total'], false) ?></td></tr>
    <tr><td>Paid (<?= e(PAYMENT_METHODS[$s['payment_method']]) ?><?= $s['payment_ref'] ? ' ' . e($s['payment_ref']) : '' ?>)</td><td class="r"><?= money($s['amount_paid'], false) ?></td></tr>
    <tr><td>Change</td><td class="r"><?= money($s['change_due'], false) ?></td></tr>
  </table>
  <hr>
  <table>
    <tr><td>VAT <?= e(rtrim(rtrim(number_format($rate, 2), '0'), '.')) ?>% (included)</td><td class="r"><?= money($s['vat_amount'], false) ?></td></tr>
    <tr><td>Items</td><td class="r"><?= array_sum(array_column($s['items'], 'quantity')) ?></td></tr>
    <?php if ($s['customer']): ?><tr><td>Points earned</td><td class="r"><?= (int)$s['points_earned'] ?></td></tr><?php endif; ?>
  </table>
  <div class="small">(E) = VAT exempt / zero-rated item</div>
  <hr>
  <div class="text-center small"><?= e(setting('receipt_footer')) ?></div>
  <div class="text-center small mt-1">Powered by OSMS</div>
</div>
<?php require OSMS_ROOT . '/app/layout/footer.php'; ?>
