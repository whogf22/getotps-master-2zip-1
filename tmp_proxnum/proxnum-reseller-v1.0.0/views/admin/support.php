<?php
use Core\Helper;
$title = 'Support Tickets';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Support Tickets</h2>
        <div style="display: flex; gap: 10px;">
            <a href="<?= $basePath ?>/admin/support?status=open" class="btn <?= $current_status === 'open' ? 'active' : '' ?>">Open (<?= $stats['open'] ?>)</a>
            <a href="<?= $basePath ?>/admin/support?status=pending" class="btn <?= $current_status === 'pending' ? 'active' : '' ?>">Pending (<?= $stats['pending'] ?>)</a>
            <a href="<?= $basePath ?>/admin/support?status=resolved" class="btn <?= $current_status === 'resolved' ? 'active' : '' ?>">Resolved (<?= $stats['resolved'] ?>)</a>
            <a href="<?= $basePath ?>/admin/support?status=all" class="btn <?= $current_status === 'all' ? 'active' : '' ?>">All</a>
        </div>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Client</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong></td>
                    <td><?= htmlspecialchars($ticket['user_name']) ?><br><small><?= htmlspecialchars($ticket['user_email']) ?></small></td>
                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                    <td><span class="badge badge-<?= $ticket['priority'] ?>"><?= ucfirst($ticket['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $ticket['status'] ?>"><?= ucfirst($ticket['status']) ?></span></td>
                    <td><?= Helper::timeAgo($ticket['created_at']) ?></td>
                    <td>
                        <a href="<?= $basePath ?>/admin/support/<?= $ticket['id'] ?>" class="btn-link">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 40px;">No tickets found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
