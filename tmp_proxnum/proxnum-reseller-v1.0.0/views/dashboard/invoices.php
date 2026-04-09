<?php
use Core\Helper;
$title = 'Invoices';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Invoices</h2>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $invoice): ?>
                <tr>
                    <td><strong>INV-<?= str_pad($invoice['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= htmlspecialchars($invoice['description']) ?></td>
                    <td><strong><?= Helper::money($invoice['amount']) ?></strong></td>
                    <td><?= date('M d, Y', strtotime($invoice['created_at'])) ?></td>
                    <td>
                        <a href="<?= Helper::url('/dashboard/invoices?download=' . $invoice['id']) ?>" class="btn-link">📄 Download PDF</a>
                        <a href="#" class="btn-link" onclick="viewInvoice(<?= $invoice['id'] ?>)">👁️ View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($invoices)): ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 40px;">
                        No invoices yet. Invoices are generated automatically for completed transactions.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function viewInvoice(id) {
    // Open invoice in new window
    window.open('<?= Helper::url("/dashboard/invoices?download=") ?>' + id, '_blank');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
