</main>
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/js/jquery.js"></script>
    <script>

        document.getElementById('adminSidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.admin-sidebar')?.classList.toggle('active');
            document.querySelector('.main-content')?.classList.toggle('sidebar-hidden');
        });

        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.admin-sidebar');
            const toggle = document.getElementById('adminSidebarToggle');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target)) {
                sidebar.classList.remove('active');
                document.querySelector('.main-content')?.classList.remove('sidebar-hidden');
            }
        });

     
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.querySelector('.admin-sidebar')?.classList.remove('active');
                document.querySelector('.main-content')?.classList.remove('sidebar-hidden');
            }
        });

      
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

    
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.card').forEach(function(card) {
                card.classList.add('fade-in');
            });
        });
    </script>
</body>
</html>