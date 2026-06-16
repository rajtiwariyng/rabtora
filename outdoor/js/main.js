const menuToggle = document.getElementById('menuToggle');
        const sidebarMenu = document.getElementById('sidebarMenu');
        const menuOverlay = document.getElementById('menuOverlay');
        const menuText = document.getElementById('menuText');

        function toggleMenu() {
            sidebarMenu.classList.toggle('active');
            menuOverlay.classList.toggle('active');
            menuToggle.classList.toggle('active'); // CSS Hamburger transform trigger karta hai
            
            // Text change functionality 'Menu' to 'Close'
            if (sidebarMenu.classList.contains('active')) {
                menuText.textContent = 'Close';
            } else {
                menuText.textContent = 'Menu';
            }
        }

        // Event Listeners
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        // Close state if clicked on backdrop blur overlay
        menuOverlay.addEventListener('click', toggleMenu);

        // Close menu if a safe links inside drawer gets selected
        const sidebarLinks = document.querySelectorAll('.sidebar a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', toggleMenu);
        });