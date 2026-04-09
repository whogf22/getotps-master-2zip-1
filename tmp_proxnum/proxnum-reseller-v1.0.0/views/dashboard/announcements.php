<?php
use Core\Helper;
$title = 'Updates';
include __DIR__ . '/../layouts/header.php';
?>

<div class="updates-dashboard">
    <div class="updates-container">
        <!-- Page Header -->
        <div class="page-head">
            <div class="head-left">
                <div class="head-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                </div>
                <div>
                    <h1>System updates</h1>
                    <p>Important news and announcements</p>
                </div>
            </div>
            <div class="head-right">
                <div class="update-badge">
                    <span class="badge-dot"></span>
                    <span><?= count($announcements) ?> update<?= count($announcements) !== 1 ? 's' : '' ?></span>
                </div>
            </div>
        </div>

        <!-- Updates Feed -->
        <div class="updates-feed">
            <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $index => $announcement): ?>
                <div class="update-timeline-item">
                    <div class="timeline-marker">
                        <div class="marker-dot marker-<?= htmlspecialchars($announcement['type']) ?>"></div>
                        <?php if ($index < count($announcements) - 1): ?>
                        <div class="marker-line"></div>
                        <?php endif; ?>
                    </div>
                    <div class="update-card update-<?= htmlspecialchars($announcement['type']) ?>">
                        <div class="update-icon">
                            <?php
                            $icons = [
                                'info' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
                                'warning' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
                                'success' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
                                'critical' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'
                            ];
                            echo $icons[$announcement['type']] ?? $icons['info'];
                            ?>
                        </div>
                        <div class="update-content">
                            <div class="update-header">
                                <h3><?= htmlspecialchars($announcement['title']) ?></h3>
                                <div class="update-meta">
                                    <span class="update-type type-<?= htmlspecialchars($announcement['type']) ?>">
                                        <?= ucfirst(htmlspecialchars($announcement['type'])) ?>
                                    </span>
                                    <?php if ($announcement['priority'] > 0): ?>
                                    <span class="priority-badge">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                        </svg>
                                        Important
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="update-body">
                                <?= nl2br(htmlspecialchars($announcement['content'])) ?>
                            </div>
                            <div class="update-footer">
                                <span class="update-time">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <?= Helper::timeAgo($announcement['created_at']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-feed">
                    <div class="empty-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                    </div>
                    <h3>No updates available</h3>
                    <p>There are no active announcements at this time. Check back later for important news and system updates.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Note -->
        <div class="info-note">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>Updates are posted here when there are important changes, maintenance, or announcements about the system.</span>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-dark: #0a2540;
        --primary-light: #4361ee;
        --accent-teal: #1e7e6c;
        --accent-gold: #c9a03d;
        --success: #10b981;
        --success-light: #e3f9ee;
        --success-dark: #0b7e55;
        --warning: #f59e0b;
        --warning-light: #fff3d4;
        --warning-dark: #b45b0a;
        --danger: #ef4444;
        --danger-light: #fee9e7;
        --danger-dark: #b91c1c;
        --info: #3b82f6;
        --info-light: #dbeafe;
        --info-dark: #1e40af;
        --neutral-50: #f8fafc;
        --neutral-100: #f1f5f9;
        --neutral-200: #e2e8f0;
        --neutral-300: #cbd5e1;
        --neutral-400: #94a3b8;
        --neutral-500: #64748b;
        --neutral-600: #475569;
        --neutral-700: #334155;
        --neutral-800: #1e293b;
        --neutral-900: #0f172a;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f3f6fd;
        color: var(--neutral-800);
    }

    .updates-dashboard {
        padding: 2rem;
        min-height: 100vh;
    }

    .updates-container {
        max-width: 900px;
        margin: 0 auto;
    }

    /* Page Head */
    .page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .head-left {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .head-icon {
        width: 56px;
        height: 56px;
        background: white;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-light);
        box-shadow: 0 8px 16px -8px rgba(0,0,0,0.08);
    }

    .head-left h1 {
        font-size: 2rem;
        font-weight: 600;
        color: var(--neutral-900);
        margin: 0;
        line-height: 1.2;
    }

    .head-left p {
        color: var(--neutral-500);
        margin: 0.2rem 0 0 0;
    }

    .update-badge {
        background: white;
        padding: 0.5rem 1.2rem;
        border-radius: 40px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: var(--neutral-700);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        border: 1px solid var(--neutral-200);
    }

    .badge-dot {
        width: 8px;
        height: 8px;
        background: var(--primary-light);
        border-radius: 50%;
    }

    /* Updates Feed */
    .updates-feed {
        position: relative;
        margin-bottom: 2rem;
    }

    .update-timeline-item {
        display: flex;
        gap: 1.5rem;
        position: relative;
    }

    .timeline-marker {
        position: relative;
        width: 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .marker-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--neutral-300);
        margin: 0.5rem 0;
        position: relative;
        z-index: 2;
    }

    .marker-dot.marker-info {
        background: var(--info-dark);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }

    .marker-dot.marker-success {
        background: var(--success-dark);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }

    .marker-dot.marker-warning {
        background: var(--warning-dark);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
    }

    .marker-dot.marker-critical {
        background: var(--danger-dark);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
    }

    .marker-line {
        width: 2px;
        flex: 1;
        background: linear-gradient(to bottom, var(--neutral-300), transparent);
        min-height: 30px;
    }

    .update-card {
        flex: 1;
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--neutral-200);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        transition: all 0.2s;
        display: flex;
        gap: 1.2rem;
        position: relative;
    }

    .update-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1);
        border-color: var(--neutral-300);
    }

    .update-card.update-info {
        border-left: 4px solid var(--info-dark);
    }

    .update-card.update-success {
        border-left: 4px solid var(--success-dark);
    }

    .update-card.update-warning {
        border-left: 4px solid var(--warning-dark);
    }

    .update-card.update-critical {
        border-left: 4px solid var(--danger-dark);
    }

    .update-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .update-info .update-icon {
        background: var(--info-light);
        color: var(--info-dark);
    }

    .update-success .update-icon {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .update-warning .update-icon {
        background: var(--warning-light);
        color: var(--warning-dark);
    }

    .update-critical .update-icon {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    .update-content {
        flex: 1;
    }

    .update-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .update-header h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--neutral-800);
        margin: 0;
        line-height: 1.4;
    }

    .update-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .update-type {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .update-type.type-info {
        background: var(--info-light);
        color: var(--info-dark);
    }

    .update-type.type-success {
        background: var(--success-light);
        color: var(--success-dark);
    }

    .update-type.type-warning {
        background: var(--warning-light);
        color: var(--warning-dark);
    }

    .update-type.type-critical {
        background: var(--danger-light);
        color: var(--danger-dark);
    }

    .priority-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.2rem 0.6rem;
        background: var(--warning-light);
        color: var(--warning-dark);
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .update-body {
        color: var(--neutral-600);
        line-height: 1.7;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .update-footer {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--neutral-200);
    }

    .update-time {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.8rem;
        color: var(--neutral-500);
    }

    /* Empty State */
    .empty-feed {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 24px;
        border: 1px solid var(--neutral-200);
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }

    .empty-icon {
        color: var(--neutral-400);
        margin-bottom: 1.5rem;
        opacity: 0.5;
    }

    .empty-feed h3 {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--neutral-700);
        margin-bottom: 0.3rem;
    }

    .empty-feed p {
        color: var(--neutral-500);
        max-width: 400px;
        margin: 0 auto;
    }

    /* Info Note */
    .info-note {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 1rem 1.5rem;
        background: #f0f4ff;
        border-radius: 60px;
        color: #1e40af;
        border: 1px solid #dbeafe;
        margin-top: 2rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .updates-dashboard { padding: 1rem; }
        
        .head-left h1 { font-size: 1.6rem; }
        
        .update-timeline-item { gap: 0.8rem; }
        
        .timeline-marker { width: 24px; }
        
        .update-card { 
            flex-direction: column; 
            gap: 0.8rem;
            padding: 1.2rem;
        }
        
        .update-header { flex-direction: column; align-items: flex-start; }
        
        .info-note { flex-direction: column; text-align: center; }
    }
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
```