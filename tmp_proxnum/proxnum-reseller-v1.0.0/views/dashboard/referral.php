<?php
use Core\Helper;
$title = 'Referral Program';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Referral Program</h2>
    </div>
    <div class="panel-body">
        <div style="padding: 30px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 10px; color: white; margin-bottom: 30px;">
            <h2 style="margin-bottom: 15px;">Earn by Referring Friends!</h2>
            <p style="margin-bottom: 20px; opacity: 0.9;">Share your unique referral link and earn 10% commission on every transaction your referrals make!</p>
            
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; opacity: 0.8;">Your Referral Code:</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="referralCode" value="<?= $referral_code ?>" readonly 
                           style="flex: 1; padding: 10px; border: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                    <button onclick="copyCode()" class="btn" style="background: white; color: #667eea;">📋 Copy</button>
                </div>
            </div>
            
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 6px;">
                <label style="display: block; margin-bottom: 5px; opacity: 0.8;">Your Referral Link:</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="referralUrl" value="<?= $referral_url ?>" readonly 
                           style="flex: 1; padding: 10px; border: none; border-radius: 6px; font-size: 14px;">
                    <button onclick="copyLink()" class="btn" style="background: white; color: #667eea;">📋 Copy</button>
                </div>
            </div>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: #10b981;">👥</div>
                <div class="stat-info">
                    <h3><?= count($referrals) ?></h3>
                    <p>Total Referrals</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: #f59e0b;"></div>
                <div class="stat-info">
                    <h3><?= Helper::money($earnings) ?></h3>
                    <p>Total Earnings</p>
                </div>
            </div>
        </div>
        
        <h3 style="margin-top: 30px; margin-bottom: 15px;">Your Referrals</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($referrals as $referral): ?>
                <tr>
                    <td><?= htmlspecialchars($referral['name']) ?></td>
                    <td><?= htmlspecialchars($referral['email']) ?></td>
                    <td><span class="badge badge-<?= $referral['status'] ?>"><?= ucfirst($referral['status']) ?></span></td>
                    <td><?= Helper::timeAgo($referral['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($referrals)): ?>
                <tr>
                    <td colspan="4" class="text-center" style="padding: 40px;">
                        No referrals yet. Start sharing your link to earn commissions!
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyCode() {
    const input = document.getElementById('referralCode');
    input.select();
    document.execCommand('copy');
    alert('Referral code copied to clipboard!');
}

function copyLink() {
    const input = document.getElementById('referralUrl');
    input.select();
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
