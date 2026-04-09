<?php
use Core\Helper;
$title = 'Support Tickets';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-panel">
    <div class="panel-header">
        <h2>Support Tickets</h2>
        <button onclick="showCreateTicket()" class="btn">🎫 Create Ticket</button>
    </div>
    <div class="panel-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Last Update</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong></td>
                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                    <td><span class="badge badge-<?= $ticket['priority'] ?>"><?= ucfirst($ticket['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $ticket['status'] ?>"><?= ucfirst($ticket['status']) ?></span></td>
                    <td><?= Helper::timeAgo($ticket['updated_at'] ?? $ticket['created_at']) ?></td>
                    <td>
                        <a href="<?= Helper::url('/dashboard/support/' . $ticket['id']) ?>" class="btn-link">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 40px;">
                        No tickets yet. Need help? Create a ticket!
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="createTicketModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 10px; max-width: 600px; width: 90%;">
        <h2 style="margin-bottom: 20px;">Create Support Issue</h2>
        <form id="ticketForm">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Subject</label>
                <input type="text" id="subject" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Priority</label>
                <select id="priority" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Message</label>
                <textarea id="message" required rows="6" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="hideCreateTicket()" class="btn" style="background: #6b7280;">Cancel</button>
                <button type="submit" class="btn">Create Ticket</button>
            </div>
        </form>
    </div>
</div>

<script>
const basePath = '<?= $basePath ?>';

function showCreateTicket() {
    document.getElementById('createTicketModal').style.display = 'flex';
}

function hideCreateTicket() {
    document.getElementById('createTicketModal').style.display = 'none';
    document.getElementById('ticketForm').reset();
}

document.getElementById('ticketForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';
    
    const data = {
        action: 'create_ticket',
        subject: document.getElementById('subject').value.trim(),
        priority: document.getElementById('priority').value,
        message: document.getElementById('message').value.trim()
    };
    
    if (!data.subject || !data.message) {
        alert('Please fill in all required fields');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    console.log('Submitting ticket:', data);
    console.log('URL:', basePath + '/dashboard/support');
    
    fetch(basePath + '/dashboard/support', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(async (r) => {
        console.log('Response status:', r.status);
        const text = await r.text();
        console.log('Response text:', text);
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Invalid response from server: ' + text.substring(0, 100));
        }
    })
    .then(result => {
        console.log('Parsed result:', result);
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        
        if (result.success) {
            alert(result.message + '\nTicket Number: ' + result.ticket_number);
            hideCreateTicket();
            location.reload();
        } else {
            alert(result.message || 'Failed to create ticket');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        alert('Error creating ticket: ' + err.message);
    });
});

// Close modal when clicking outside
document.getElementById('createTicketModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideCreateTicket();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
