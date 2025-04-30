<?php
session_start();
require_once '../auth/login_status.php';
require_once '../db/connection.php';



if (!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$stmt = $conn->prepare("SELECT admin_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin_status'] != 1) {
    header("Location: ../dashboard.php");
    exit();
}

require_once '../templates/admin_header.php';
?>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Send Notification</h5>
            </div>
            <div class="card-body">
                <form id="notificationForm">
                    <div class="mb-3">
                        <label class="form-label">Recipients</label>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                <label class="form-check-label" for="selectAllUsers">
                                    Select All Users
                                </label>
                            </div>
                        </div>
                        <div class="search-container position-relative">
                            <input type="text" 
                                class="form-control" 
                                id="userSearch" 
                                placeholder="Type username or email..." 
                                autocomplete="off">
                            <div id="searchSuggestions" 
                                class="suggestions-dropdown d-none">
                            </div>
                        </div>
                        <div id="selectedUsers" class="selected-users mt-2"></div>
                        <input type="hidden" name="recipients" id="recipientIds">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notification Type</label>
                        <select class="form-select" name="notification_type" required>
                            <option value="announcement">Announcement</option>
                            <option value="event">Event Reminder</option>
                            <option value="donation">Donation Request</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" class="form-control" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendEmail" name="send_email">
                            <label class="form-check-label" for="sendEmail">
                                Also send as email
                            </label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.search-container {
    position: relative;
}
.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
}
.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
}
.suggestion-item:last-child {
    border-bottom: none;
}
.suggestion-item:hover {
    background-color: #f8f9fa;
}
.suggestion-item.keyboard-selected {
    background-color: #e9ecef;
}
.suggestion-info {
    flex-grow: 1;
}
.user-badge {
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 12px;
    margin-left: 8px;
}
.badge-admin {
    background-color: #dc3545;
    color: white;
}
.badge-user {
    background-color: #6c757d;
    color: white;
}
.selected-users {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.user-tag {
    background: #e9ecef;
    border-radius: 16px;
    padding: 4px 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
}
.user-tag .remove {
    cursor: pointer;
    color: #dc3545;
    font-weight: bold;
    padding: 0 4px;
}
</style>

<script>
let selectedUsers = new Set();
let currentSuggestionIndex = -1;
let debounceTimeout;

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const suggestionsBox = document.getElementById('searchSuggestions');

    searchInput.addEventListener('input', handleSearchInput);
    searchInput.addEventListener('keydown', handleKeyboardNavigation);


    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            hideSuggestions();
        }
    });

    document.getElementById('selectAllUsers').addEventListener('change', function(e) {
        if (e.target.checked) {
            fetch('../ajax/search_users.php?all=true')
                .then(response => response.json())
                .then(users => {
                    selectedUsers.clear();
                    document.getElementById('selectedUsers').innerHTML = '';
                    users.forEach(user => {
                        selectedUsers.add(user.id.toString());
                        addUserTag(user);
                    });
                    updateRecipientIds();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching users');
                });
        } else {
            selectedUsers.clear();
            document.getElementById('selectedUsers').innerHTML = '';
            updateRecipientIds();
        }
    });


    document.getElementById('notificationForm').addEventListener('submit', handleFormSubmit);
});

function handleSearchInput(e) {
    const searchTerm = e.target.value.trim();
    clearTimeout(debounceTimeout);

    if (searchTerm.length === 0) {
        hideSuggestions();
        return;
    }

    const suggestionsBox = document.getElementById('searchSuggestions');
    suggestionsBox.innerHTML = '<div class="suggestion-item text-muted">Searching...</div>';
    suggestionsBox.classList.remove('d-none');

    debounceTimeout = setTimeout(() => {
        fetch(`../ajax/search_users.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(handleSearchResults)
            .catch(error => {
                console.error('Error:', error);
                showErrorSuggestion();
            });
    }, 300);
}

function handleSearchResults(results) {
    const suggestionsBox = document.getElementById('searchSuggestions');
    
    if (!results.length) {
        suggestionsBox.innerHTML = '<div class="suggestion-item text-muted">No users found</div>';
        return;
    }

    suggestionsBox.innerHTML = '';
    currentSuggestionIndex = -1;

    results.forEach((user, index) => {
        if (selectedUsers.has(user.id.toString())) return;

        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.dataset.index = index;
        div.dataset.userId = user.id;
        
        div.innerHTML = `
            <div class="suggestion-info">
                <div><strong>${user.username}</strong></div>
                ${user.email ? `<small class="text-muted">${user.email}</small>` : ''}
            </div>
            <span class="user-badge badge-${user.admin_status == 1 ? 'admin' : 'user'}">
                ${user.admin_status == 1 ? 'Admin' : 'User'}
            </span>
        `;
        
        div.addEventListener('click', () => selectUser(user));
        suggestionsBox.appendChild(div);
    });
}

function handleKeyboardNavigation(e) {
    const suggestionsBox = document.getElementById('searchSuggestions');
    const suggestions = suggestionsBox.querySelectorAll('.suggestion-item');
    
    if (suggestionsBox.classList.contains('d-none') || !suggestions.length) return;

    switch(e.key) {
        case 'ArrowDown':
            e.preventDefault();
            navigateSuggestions(1, suggestions);
            break;
        case 'ArrowUp':
            e.preventDefault();
            navigateSuggestions(-1, suggestions);
            break;
        case 'Enter':
            e.preventDefault();
            if (currentSuggestionIndex >= 0) {
                const selectedItem = suggestions[currentSuggestionIndex];
                const userId = selectedItem.dataset.userId;
                selectUserById(userId);
            }
            break;
        case 'Escape':
            hideSuggestions();
            break;
    }
}

function navigateSuggestions(direction, suggestions) {
    suggestions[currentSuggestionIndex]?.classList.remove('keyboard-selected');
    
    currentSuggestionIndex += direction;
    if (currentSuggestionIndex >= suggestions.length) {
        currentSuggestionIndex = 0;
    } else if (currentSuggestionIndex < 0) {
        currentSuggestionIndex = suggestions.length - 1;
    }

    const selectedItem = suggestions[currentSuggestionIndex];
    selectedItem.classList.add('keyboard-selected');
    selectedItem.scrollIntoView({ block: 'nearest' });
}

function selectUserById(userId) {
    fetch(`../ajax/search_users.php?search=${userId}`)
        .then(response => response.json())
        .then(results => {
            const user = results.find(u => u.id.toString() === userId);
            if (user) {
                selectUser(user);
            }
        })
        .catch(error => console.error('Error:', error));
}

function selectUser(user) {
    if (selectedUsers.has(user.id.toString())) return;
    
    selectedUsers.add(user.id.toString());
    addUserTag(user);
    
    document.getElementById('userSearch').value = '';
    hideSuggestions();
    updateRecipientIds();
}

function addUserTag(user) {
    const selectedContainer = document.getElementById('selectedUsers');
    
    const tag = document.createElement('div');
    tag.className = 'user-tag';
    tag.innerHTML = `
        <span>${user.username}</span>
        <span class="remove" onclick="removeUser('${user.id}')">&times;</span>
    `;
    
    selectedContainer.appendChild(tag);
}

function removeUser(id) {
    selectedUsers.delete(id.toString());
    const tag = document.querySelector(`.user-tag span[onclick*="${id}"]`).parentNode;
    tag.remove();
    updateRecipientIds();
}

function updateRecipientIds() {
    document.getElementById('recipientIds').value = Array.from(selectedUsers).join(',');
}

function hideSuggestions() {
    document.getElementById('searchSuggestions').classList.add('d-none');
    currentSuggestionIndex = -1;
}

function showErrorSuggestion() {
    const suggestionsBox = document.getElementById('searchSuggestions');
    suggestionsBox.innerHTML = '<div class="suggestion-item text-danger">Error loading suggestions</div>';
}

function handleFormSubmit(e) {
    e.preventDefault();
    
   
    const form = document.getElementById('notificationForm');
    const formData = new FormData(form);


    if (selectedUsers.size === 0) {
        alert('Please select at least one recipient');
        return;
    }


    formData.set('recipients', Array.from(selectedUsers).join(','));


    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

    console.log('Sending form data:', Object.fromEntries(formData));

    fetch('../crud/notifications/create_notification.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Server response:', text);
            throw new Error('Invalid JSON response from server');
        }
    })
    .then(data => {
        if (data.success) {
            alert('Notification sent successfully!');
            form.reset();
            selectedUsers.clear();
            document.getElementById('selectedUsers').innerHTML = '';
        } else {
            alert('Error sending notification: ' + (data.message || 'Unknown error'));
            console.error('Server error details:', data.debug);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending notification. Please check the console for details.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}
</script>

<?php require_once '../templates/admin_footer.php'; ?>