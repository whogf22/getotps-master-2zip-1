<!-- Admin Activations History View -->
<?php
use Core\Helper;
$title = 'Activations History';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>📱 Activations History</h2>
        <p style="margin: 0; font-size: 0.9rem; color: #666;">View all number purchases and activations in the system</p>
    </div>
    
    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; color: white;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $stats['total'] ?></div>
            <div style="opacity: 0.9;">Total Activations</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 10px; color: white;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $stats['pending'] ?></div>
            <div style="opacity: 0.9;">Pending</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 20px; border-radius: 10px; color: white;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $stats['completed'] ?></div>
            <div style="opacity: 0.9;">Completed</div>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 20px; border-radius: 10px; color: white;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $stats['expired'] ?></div>
            <div style="opacity: 0.9;">Expired</div>
        </div>
        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 20px; border-radius: 10px; color: white;">
            <div style="font-size: 2rem; font-weight: bold;"><?= $stats['cancelled'] ?></div>
            <div style="opacity: 0.9;">Cancelled</div>
        </div>
    </div>
    
    <!-- Filter Tabs -->
    <div style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : '' ?>" style="<?= $filter !== 'all' ? 'background: #e0e0e0; color: #333;' : '' ?>">All</a>
        <a href="?filter=pending" class="btn <?= $filter === 'pending' ? 'btn-primary' : '' ?>" style="<?= $filter !== 'pending' ? 'background: #e0e0e0; color: #333;' : '' ?>">⏳ Pending</a>
        <a href="?filter=completed" class="btn <?= $filter === 'completed' ? 'btn-primary' : '' ?>" style="<?= $filter !== 'completed' ? 'background: #e0e0e0; color: #333;' : '' ?>">✅ Completed</a>
        <a href="?filter=expired" class="btn <?= $filter === 'expired' ? 'btn-primary' : '' ?>" style="<?= $filter !== 'expired' ? 'background: #e0e0e0; color: #333;' : '' ?>">⌛ Expired</a>
        <a href="?filter=cancelled" class="btn <?= $filter === 'cancelled' ? 'btn-primary' : '' ?>" style="<?= $filter !== 'cancelled' ? 'background: #e0e0e0; color: #333;' : '' ?>">❌ Cancelled</a>
    </div>
    
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Country</th>
                    <th>Phone Number</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Cost</th>
                    <th>Created</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activations as $activation): ?>
                <tr>
                    <td>#<?= $activation['id'] ?></td>
                    <td>
                        <div style="font-weight: 600;"><?= htmlspecialchars($activation['user_name']) ?></div>
                        <div style="font-size: 0.85rem; color: #666;"><?= htmlspecialchars($activation['user_email']) ?></div>
                    </td>
                    <td>
                        <span style="background: #e7f3ff; color: #0066cc; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                            <?= strtoupper(htmlspecialchars($activation['service'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars(Helper::getCountryName($activation['country'])) ?></td>
                    <td>
                        <span style="font-family: monospace; background: #f5f5f5; padding: 4px 8px; border-radius: 4px;">
                            <?= htmlspecialchars($activation['phone']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($activation['code']): ?>
                            <strong style="color: #10b981; font-size: 1.1rem;"><?= htmlspecialchars($activation['code']) ?></strong>
                        <?php else: ?>
                            <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $activation['status'] ?>">
                            <?php
                            $statusIcons = [
                                'pending' => '⏳',
                                'completed' => '✅',
                                'expired' => '⌛',
                                'cancelled' => '❌'
                            ];
                            echo ($statusIcons[$activation['status']] ?? '') . ' ' . ucfirst($activation['status']);
                            ?>
                        </span>
                    </td>
                    <td><?= Helper::money($activation['cost']) ?></td>
                    <td><?= Helper::timeAgo($activation['created_at']) ?></td>
                    <td>
                        <?= $activation['completed_at'] ? Helper::timeAgo($activation['completed_at']) : '<span style="color: #999;">-</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($activations)): ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📱</div>
                        <div style="font-size: 1.1rem;">No activations found</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($pagination['total_pages'] > 1): ?>
        <div style="margin-top: 20px; text-align: center;">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?filter=<?= $filter ?>&page=<?= $i ?>" class="btn" style="<?= $i === $pagination['current_page'] ? 'opacity: 0.6;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .data-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .data-table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .data-table tr:hover {
        background: #f8f9fa;
    }
    
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .badge-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .badge-completed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-expired {
        background: #fee;
        color: #991b1b;
    }
    
    .badge-cancelled {
        background: #f3f4f6;
        color: #6b7280;
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
