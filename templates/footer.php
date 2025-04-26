    </main>
    
    <script src="/GoldTree/js/bootstrap.bundle.js"></script>
    <script src="/GoldTree/js/jquery.js"></script>
    <script>
        // Sidebar Toggle Functionality
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar')?.classList.toggle('active');
            document.querySelector('.main-content')?.classList.toggle('sidebar-hidden');
        });

        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.card').forEach(function(card) {
                card.classList.add('fade-in');
            });
        });
    </script>
</body>
</html>