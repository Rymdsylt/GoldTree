<?php
// Only show modal for logged in users who haven't agreed
if (isset($_SESSION['user_id']) && isset($_SESSION['privacy_checked']) && !$_SESSION['privacy_agreed']) {
?>
    <div class="modal fade" id="privacyPolicyModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Privacy Policy</h5>
                </div>
                <div class="modal-body privacy-policy-content">
                    <?php 
                    require_once __DIR__ . '/../vendor/autoload.php';
                    $parsedown = new Parsedown();
                    $privacyContent = file_get_contents(__DIR__ . '/../Privacy Policy for Mater Dolorosa Parish.md');
                    echo $parsedown->text($privacyContent);
                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="disagreeBtn">
                        <i class="bi bi-x-circle"></i> Disagree & Logout
                    </button>
                    <button type="button" class="btn btn-primary" id="agreeBtn">
                        <i class="bi bi-check-circle"></i> I Agree
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    .privacy-policy-content {
        padding: 20px;
        line-height: 1.6;
    }
    .privacy-policy-content h1 {
        color: #2c3e50;
        margin-bottom: 1.5rem;
        font-size: 1.8rem;
    }
    .privacy-policy-content h2 {
        color: #34495e;
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-size: 1.4rem;
    }
    .privacy-policy-content ul {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }
    .privacy-policy-content li {
        margin-bottom: 0.5rem;
    }
    .privacy-policy-content strong {
        color: #2c3e50;
    }
    .privacy-policy-content em {
        color: #7f8c8d;
    }
    .privacy-policy-content hr {
        margin: 2rem 0;
        border-top: 1px solid #eee;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('privacyPolicyModal'));
        modal.show();

        document.getElementById('agreeBtn').addEventListener('click', function() {
            handlePrivacyAgreement(true);
        });

        document.getElementById('disagreeBtn').addEventListener('click', function() {
            handlePrivacyAgreement(false);
        });

        function handlePrivacyAgreement(agreed) {
            const basePath = '<?php echo BASE_PATH; ?>';
            fetch(basePath + '/auth/handle_privacy_agreement.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'agreed=' + agreed
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (!agreed) {
                        window.location.href = basePath + '/login.php';
                    } else {
                        modal.hide();
                        window.location.reload();
                    }
                } else {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your choice. Please try again.');
            });
        }
    });
    </script>
<?php 
}
?>