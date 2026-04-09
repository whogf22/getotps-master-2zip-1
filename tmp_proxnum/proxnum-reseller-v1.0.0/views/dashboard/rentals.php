<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Rental Numbers</h3>
                <button class="btn btn-primary" onclick="document.getElementById('buyModal').style.display='block'">
                    📞 Rent New Number
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($rentals)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Phone Number</th>
                        <th>Service</th>
                        <th>Country</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th>Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rentals as $rental): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($rental['phone']) ?></strong></td>
                        <td><?= htmlspecialchars($rental['service']) ?></td>
                        <td><?= htmlspecialchars(\Core\Helper::getCountryName($rental['country'])) ?></td>
                        <td><?= $rental['days'] ?> days</td>
                        <td>
                            <span class="badge badge-<?= 
                                $rental['status'] === 'active' ? 'success' : 
                                ($rental['status'] === 'expired' ? 'danger' : 'secondary') 
                            ?>">
                                <?= ucfirst($rental['status']) ?>
                            </span>
                        </td>
                        <td><?= \Core\Helper::date($rental['expires_at'], 'Y-m-d H:i') ?></td>
                        <td><?= \Core\Helper::money($rental['cost']) ?></td>
                        <td>
                            <a href="<?= \Core\Helper::url('/dashboard/rentals/' . $rental['id']) ?>" class="btn btn-sm btn-info">View Messages</a>
                            <?php if ($rental['status'] === 'active'): ?>
                            <button class="btn btn-sm btn-danger" onclick="cancelRental(<?= $rental['id'] ?>)">Cancel</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
            <div style="margin-top: 20px; text-align: center;">
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <a href="?page=<?= $i ?>" class="btn btn-sm" style="<?= $i === $pagination['current_page'] ? 'opacity: 0.6;' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 10px;">📞</div>
                <p>No rental numbers yet</p>
                <button class="btn btn-primary" onclick="document.getElementById('buyModal').style.display='block'">
                    Rent Your First Number
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="info-box">
        <strong>ℹ️ About Rental Numbers:</strong><br>
        Rental numbers allow you to receive multiple SMS messages for the duration of your rental period. 
        They're perfect for services that send multiple codes or ongoing verification.
    </div>
</div>

<!-- Buy Rental Modal -->
<div id="buyModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Rent a Number</h3>
            <span class="close" onclick="document.getElementById('buyModal').style.display='none'">&times;</span>
        </div>
        <div class="modal-body">
            <form id="buyRentalForm">
                <div class="form-group">
                    <label>Service:</label>
                    <input type="text" id="service" name="service" placeholder="e.g., whatsapp" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Country:</label>
                    <input type="text" id="country" name="country" placeholder="e.g., US" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Days:</label>
                    <select id="days" name="days" class="form-control" required>
                        <option value="7">7 Days</option>
                        <option value="14">14 Days</option>
                        <option value="30">30 Days</option>
                    </select>
                </div>
                
                <div id="priceDisplay" class="price-display"></div>
                
                <button type="submit" class="btn btn-primary btn-block">Rent Number</button>
            </form>
        </div>
    </div>
</div>

<style>
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 15px;
        margin-top: 20px;
        border-radius: 4px;
    }
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }
    .badge-success {
        background: #28a745;
        color: white;
    }
    .badge-danger {
        background: #dc3545;
        color: white;
    }
    .badge-secondary {
        background: #6c757d;
        color: white;
    }
    .badge-info {
        background: #17a2b8;
        color: white;
    }
    .btn-sm {
        padding: 4px 8px;
        font-size: 12px;
    }
    .btn-info {
        background: #17a2b8;
        color: white;
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
    }
    .modal-content {
        background: white;
        margin: 5% auto;
        width: 500px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #eee;
    }
    .modal-header h3 {
        margin: 0;
    }
    .close {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #999;
    }
    .close:hover {
        color: #333;
    }
    .modal-body {
        padding: 20px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .price-display {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        margin: 20px 0;
        font-weight: bold;
        text-align: center;
    }
    .btn-block {
        width: 100%;
    }
</style>

<script>
function cancelRental(rentalId) {
    if (!confirm('Are you sure you want to cancel this rental?')) return;
    
    fetch('/dashboard/cancelRental', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>',
            rental_id: rentalId
        })
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    })
    .catch(err => alert('Error: ' + err.message));
}

document.getElementById('buyRentalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>',
        service: document.getElementById('service').value,
        country: document.getElementById('country').value,
        days: document.getElementById('days').value
    };
    
    fetch('/dashboard/buyRental', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    })
    .catch(err => alert('Error: ' + err.message));
});

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('buyModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
