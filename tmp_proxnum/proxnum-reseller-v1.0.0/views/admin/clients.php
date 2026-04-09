<!-- Admin Clients Management View -->
<?php
use Core\Helper;
$title = 'Manage Clients';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Client Management</h2>
        <button class="btn" onclick="showAddClientModal()">➕ Add New Client</button>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td>#<?= $client['id'] ?></td>
                    <td><?= htmlspecialchars($client['name']) ?></td>
                    <td><?= htmlspecialchars($client['email']) ?></td>
                    <td><?= Helper::money($client['balance']) ?></td>
                    <td><span class="badge badge-<?= $client['status'] ?>"><?= ucfirst($client['status']) ?></span></td>
                    <td><?= Helper::date($client['created_at'], 'M d, Y') ?></td>
                    <td><?= $client['last_login'] ? Helper::timeAgo($client['last_login']) : 'Never' ?></td>
                    <td>
                        <button class="btn-link" onclick="viewProfile(<?= $client['id'] ?>)">👤 View</button> |
                        <button class="btn-link" onclick="showAddBalanceModal(<?= $client['id'] ?>, '<?= htmlspecialchars($client['name']) ?>')">💰 Balance</button> |
                        <button class="btn-link" onclick="changeStatus(<?= $client['id'] ?>, '<?= $client['status'] ?>', '<?= htmlspecialchars($client['name']) ?>')">🔄 Status</button> |
                        <a href="<?= Helper::url('/admin/sendEmail?user=') . $client['id'] ?>" class="btn-link" title="Send email to this user">✉️ Email</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($pagination['total_pages'] > 1): ?>
        <div style="margin-top: 20px; text-align: center;">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <a href="?page=<?= $i ?>" class="btn" style="<?= $i === $pagination['current_page'] ? 'opacity: 0.6;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Client Modal -->
<div id="addClientModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Add New Client</h2>
        <form id="addClientForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Initial Balance</label>
                <input type="number" name="initial_balance" step="0.01" value="0">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn">Create Client</button>
                <button type="button" class="btn" style="background: #6c757d;" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Balance Modal -->
<div id="addBalanceModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Add Balance</h2>
        <p id="clientNameDisplay"></p>
        <form id="addBalanceForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="user_id" id="balanceUserId">
            <div class="form-group">
                <label>Amount ($)</label>
                <input type="number" name="amount" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" value="Balance added by admin">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn">Add Balance</button>
                <button type="button" class="btn" style="background: #6c757d;" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Change Status Modal -->
<div id="changeStatusModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Change Client Status</h2>
        <p id="statusClientDisplay"></p>
        <form id="changeStatusForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="user_id" id="statusUserId">
            <div class="form-group">
                <label>Select Status</label>
                <select name="status" required style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    <option value="active">✅ Active - Can login and use services</option>
                    <option value="suspended">🚫 Suspended - Cannot login</option>
                    <option value="inactive">⏸️ Inactive - Account disabled</option>
                </select>
            </div>
            <div class="form-group" style="background: #fff3cd; padding: 10px; border-radius: 6px; font-size: 14px;">
                <strong>⚠️ Note:</strong> Suspended and inactive users cannot login to the system.
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn">Update Status</button>
                <button type="button" class="btn" style="background: #6c757d;" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- View Profile Modal -->
<div id="viewProfileModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <h2>Client Profile</h2>
        <div id="profileContent" style="margin: 20px 0;">
            <div style="text-align: center; padding: 20px;">
                Loading...
            </div>
        </div>
        <div style="text-align: right;">
            <button type="button" class="btn" style="background: #6c757d;" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 10px;
        max-width: 500px;
        width: 90%;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .form-group input {
        width: 100%;
        padding: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
    }
</style>

<script>
    const basePath = '<?= \Core\Helper::url('') ?>';
    
    function showAddClientModal() {
        document.getElementById('addClientModal').style.display = 'flex';
    }
    
    function showAddBalanceModal(userId, name) {
        document.getElementById('balanceUserId').value = userId;
        document.getElementById('clientNameDisplay').textContent = 'Client: ' + name;
        document.getElementById('addBalanceModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('addClientModal').style.display = 'none';
        document.getElementById('addBalanceModal').style.display = 'none';
        document.getElementById('changeStatusModal').style.display = 'none';
        document.getElementById('viewProfileModal').style.display = 'none';
    }
    
    function changeStatus(userId, currentStatus, name) {
        document.getElementById('statusUserId').value = userId;
        document.getElementById('statusClientDisplay').textContent = 'Client: ' + name + ' (Current: ' + currentStatus + ')';
        document.querySelector('#changeStatusForm select[name="status"]').value = currentStatus;
        document.getElementById('changeStatusModal').style.display = 'flex';
    }
    
    function viewProfile(userId) {
        document.getElementById('viewProfileModal').style.display = 'flex';
        document.getElementById('profileContent').innerHTML = '<div style="text-align: center; padding: 20px;">Loading...</div>';
        
        fetch(basePath + '/admin/getClientProfile?user_id=' + userId)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const client = data.client;
                    const html = `
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0;">
                            <div><strong>ID:</strong></div>
                            <div>#${client.id}</div>
                            
                            <div><strong>Name:</strong></div>
                            <div>${client.name}</div>
                            
                            <div><strong>Email:</strong></div>
                            <div>${client.email}</div>
                            
                            <div><strong>Balance:</strong></div>
                            <div style="color: #28a745; font-weight: bold;">${client.balance}</div>
                            
                            <div><strong>Status:</strong></div>
                            <div><span class="badge badge-${client.status}">${client.status}</span></div>
                            
                            <div><strong>Phone:</strong></div>
                            <div>${client.phone || 'Not set'}</div>
                            
                            <div><strong>Country:</strong></div>
                            <div>${client.country || 'Not set'}</div>
                            
                            <div><strong>Joined:</strong></div>
                            <div>${client.created_at}</div>
                            
                            <div><strong>Last Login:</strong></div>
                            <div>${client.last_login || 'Never'}</div>
                            
                            <div><strong>Total Spent:</strong></div>
                            <div style="color: #007bff; font-weight: bold;">${client.total_spent || '$0.00'}</div>
                            
                            <div><strong>Total Activations:</strong></div>
                            <div style="font-weight: bold;">${client.total_activations || 0}</div>
                            
                            <div><strong>Pending:</strong></div>
                            <div style="color: #ffc107;">${client.pending_activations || 0}</div>
                            
                            <div><strong>Completed:</strong></div>
                            <div style="color: #28a745;">${client.completed_activations || 0}</div>
                        </div>
                    `;
                    document.getElementById('profileContent').innerHTML = html;
                } else {
                    document.getElementById('profileContent').innerHTML = '<div style="color: red; text-align: center;">Error: ' + data.message + '</div>';
                }
            })
            .catch(err => {
                document.getElementById('profileContent').innerHTML = '<div style="color: red; text-align: center;">Error loading profile</div>';
            });
    }
    
    document.getElementById('addClientForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Creating...';
        
        fetch(basePath + '/admin/addClient', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            } else {
                btn.disabled = false;
                btn.textContent = 'Create Client';
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.textContent = 'Create Client';
        });
    });
    
    document.getElementById('addBalanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Adding...';
        
        fetch(basePath + '/admin/addBalance', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            } else {
                btn.disabled = false;
                btn.textContent = 'Add Balance';
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.textContent = 'Add Balance';
        });
    });
    
    document.getElementById('changeStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Updating...';
        
        fetch(basePath + '/admin/updateClientStatus', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            } else {
                btn.disabled = false;
                btn.textContent = 'Update Status';
            }
        })
        .catch(err => {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.textContent = 'Update Status';
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
