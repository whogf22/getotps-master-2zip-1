<?php
use Core\Helper;
$title = 'Email Templates';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Email Templates</h2>
    </div>
    <div class="panel-body">
        <?php if (Helper::isDemo()): ?>
        <div class="alert alert-info" style="margin-bottom: 20px; padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; color: #0c5460;">
            <strong>ℹ️ Demo Mode</strong><br>
            You're viewing this page in demo mode. You can view and preview templates, but editing is disabled for demo accounts.
        </div>
        <?php endif; ?>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Template Name</th>
                    <th>Subject</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $template): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($template['name']) ?></strong></td>
                    <td><?= htmlspecialchars($template['subject']) ?></td>
                    <td><?= Helper::timeAgo($template['updated_at'] ?? $template['created_at']) ?></td>
                    <td>
                        <a href="#" class="btn-link" onclick="editTemplate(<?= $template['id'] ?>)">Edit</a>
                        <a href="#" class="btn-link" onclick="previewTemplate(<?= $template['id'] ?>)">Preview</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Template Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3>Edit Email Template</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editTemplateForm">
                <input type="hidden" id="template_id" name="template_id">
                <input type="hidden" name="action" value="update">
                
                <div class="form-group">
                    <label>Template Name</label>
                    <input type="text" id="template_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" id="template_subject" name="subject" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Body (HTML allowed)</label>
                    <textarea id="template_body" name="body" class="form-control" rows="15" required></textarea>
                </div>
                
                <div class="form-group">
                    <small class="text-muted">
                        Available variables: {user_name}, {user_email}, {domain}, {license_key}, {expires_at}, {amount}, {transaction_id}, {otp_code}
                    </small>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Template Modal -->
<div id="previewModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3>Preview Email Template</h3>
            <span class="close" onclick="closePreviewModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="preview-container">
                <h4 id="preview_subject" style="margin-bottom: 15px; color: #333;"></h4>
                <div id="preview_body" style="border: 1px solid #ddd; padding: 20px; background: #fff; border-radius: 4px;"></div>
            </div>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 90%;
    max-width: 600px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px 8px 0 0;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.5rem;
}

.modal-body {
    padding: 20px;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover,
.close:focus {
    color: #000;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
    font-family: monospace;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.text-muted {
    color: #6c757d;
    font-size: 13px;
}

.preview-container {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}
</style>

<script>
const basePath = '<?= $basePath ?>';

function editTemplate(id) {
    // Fetch template data
    fetch(basePath + '/admin/emailTemplates', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get&template_id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const template = data.template;
            document.getElementById('template_id').value = template.id;
            document.getElementById('template_name').value = template.name;
            document.getElementById('template_subject').value = template.subject;
            document.getElementById('template_body').value = template.body;
            
            document.getElementById('editModal').style.display = 'block';
        } else {
            alert('Error loading template: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error loading template: ' + error);
    });
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function previewTemplate(id) {
    // Fetch template data
    fetch(basePath + '/admin/emailTemplates', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get&template_id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const template = data.template;
            document.getElementById('preview_subject').textContent = template.subject;
            document.getElementById('preview_body').innerHTML = template.body;
            
            document.getElementById('previewModal').style.display = 'block';
        } else {
            alert('Error loading template: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error loading template: ' + error);
    });
}

function closePreviewModal() {
    document.getElementById('previewModal').style.display = 'none';
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('editTemplateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(basePath + '/admin/emailTemplates', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Template updated successfully!');
                closeEditModal();
                location.reload();
            } else {
                alert('Error updating template: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error updating template: ' + error);
        });
    });
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const editModal = document.getElementById('editModal');
        const previewModal = document.getElementById('previewModal');
        if (event.target == editModal) {
            closeEditModal();
        }
        if (event.target == previewModal) {
            closePreviewModal();
        }
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
