<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <?php if (isset($message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3>Profile Information</h3>
                </div>
                <div class="card-body">
                    <?php if (\Core\Helper::isDemo()): ?>
                    <div class="alert alert-warning" style="margin-bottom: 1rem;">
                        <strong>⚠️ Demo Account</strong><br>
                        Profile modifications are disabled in demo mode.
                    </div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                        </div>
                        
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3>Change Password</h3>
                </div>
                <div class="card-body">
                    <?php if (\Core\Helper::isDemo()): ?>
                    <div class="alert alert-warning" style="margin-bottom: 1rem;">
                        <strong>⚠️ Demo Account</strong><br>
                        Password changes are disabled in demo mode.
                    </div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label>Current Password:</label>
                            <input type="password" name="current_password" class="form-control" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                        </div>
                        
                        <div class="form-group">
                            <label>New Password:</label>
                            <input type="password" name="new_password" class="form-control" minlength="8" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                            <small class="form-text">Minimum 8 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm New Password:</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="8" required <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" <?= \Core\Helper::isDemo() ? 'disabled' : '' ?>>Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Details -->
    <div class="card">
        <div class="card-header">
            <h3>Account Details</h3>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <td><strong>User ID:</strong></td>
                    <td><?= $user['id'] ?></td>
                </tr>
                <tr>
                    <td><strong>Role:</strong></td>
                    <td><span class="badge badge-admin">Administrator</span></td>
                </tr>
                <tr>
                    <td><strong>Account Status:</strong></td>
                    <td><span class="badge badge-success"><?= ucfirst($user['status']) ?></span></td>
                </tr>
                <tr>
                    <td><strong>Member Since:</strong></td>
                    <td><?= \Core\Helper::date($user['created_at'], 'F j, Y') ?></td>
                </tr>
                <tr>
                    <td><strong>Last Login:</strong></td>
                    <td><?= isset($user['last_login']) ? \Core\Helper::timeAgo($user['last_login']) : 'N/A' ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<style>
    .row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    .col-6 {
        flex: 0 0 48%;
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
        font-size: 14px;
    }
    .form-text {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 12px;
    }
    .info-table {
        width: 100%;
    }
    .info-table tr {
        border-bottom: 1px solid #eee;
    }
    .info-table td {
        padding: 12px 10px;
    }
    .info-table td:first-child {
        width: 200px;
    }
    .badge {
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    .badge-admin {
        background: #764ba2;
        color: white;
    }
    .badge-success {
        background: #28a745;
        color: white;
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
