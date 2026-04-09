<?php
use Core\Helper;
$title = 'Favorite Services';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Favorite Services</h2>
        <button onclick="addFavorite()" class="btn">⭐ Add Favorite</button>
    </div>
    <div class="panel-body">
        <?php if (!empty($frequent_services)): ?>
        <div style="padding: 20px; background: #f0f9ff; border-radius: 6px; margin-bottom: 30px;">
            <h3 style="margin-bottom: 15px;"> Frequently Used Services</h3>
            <p style="margin-bottom: 15px; color: #666;">Services you've used multiple times. Click to add to favorites!</p>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <?php foreach ($frequent_services as $freq): ?>
                    <?php 
                        $isFavorite = false;
                        foreach ($favorites as $fav) {
                            if ($fav['service'] === $freq['service'] && $fav['country'] === $freq['country']) {
                                $isFavorite = true;
                                break;
                            }
                        }
                    ?>
                    <?php if (!$isFavorite): ?>
                    <button onclick="quickAddFavorite('<?= htmlspecialchars($freq['service']) ?>', '<?= htmlspecialchars($freq['country']) ?>')" 
                            style="padding: 10px 15px; background: white; border: 2px solid #0D6EFD; border-radius: 6px; cursor: pointer;">
                        <strong><?= strtoupper(htmlspecialchars($freq['service'])) ?></strong> - <?= htmlspecialchars(\Core\Helper::getCountryName($freq['country'])) ?>
                        <span style="color: #666;">(used <?= $freq['usage_count'] ?>x)</span>
                    </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Country</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($favorites as $fav): ?>
                <tr>
                    <td><strong><?= strtoupper(htmlspecialchars($fav['service'])) ?></strong></td>
                    <td><?= htmlspecialchars(\Core\Helper::getCountryName($fav['country'])) ?></td>
                    <td><?= Helper::timeAgo($fav['created_at']) ?></td>
                    <td>
                        <a href="<?= Helper::url('/dashboard/buy?service=' . $fav['service'] . '&country=' . $fav['country']) ?>" class="btn-link">Buy Now</a>
                        <a href="#" class="btn-link" onclick="removeFavorite(<?= $fav['id'] ?>); return false;" style="color: #ef4444;">Remove</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($favorites)): ?>
                <tr>
                    <td colspan="4" class="text-center" style="padding: 40px;">
                        No favorites yet. Add your frequently used services for quick access!
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const basePath = '<?= Helper::url('') ?>';

function quickAddFavorite(service, country) {
    fetch(basePath + '/dashboard/favorites', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', service, country})
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

function addFavorite() {
    const service = prompt('Enter service name (e.g., whatsapp):');
    if (!service) return;
    
    const country = prompt('Enter country code (e.g., US, UK):');
    if (!country) return;
    
    fetch(basePath + '/dashboard/favorites', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'add', service, country})
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

function removeFavorite(id) {
    if (!confirm('Remove from favorites?')) return;
    
    fetch(basePath + '/dashboard/favorites', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'remove', favorite_id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
