// Fluent App JavaScript - Windows Store Inspired

document.addEventListener('DOMContentLoaded', function() {
    console.log('Fluent App JS Loaded');
    initializeToastrContainer();
    initializeSidebar();
    highlightActiveNavLink();
    initializeDarkMode();
});

/**
 * Initialize sidebar, add toggle functionality and restore state from localStorage
 */
function initializeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (!sidebar || !mainContent) {
        console.warn('Sidebar or main content elements not found');
        return;
    }
    
    // Create the toggle button if it doesn't exist (common for dynamically creating the sidebar)
    if (!document.querySelector('.sidebar-toggle')) {
        const header = createSidebarHeader();
        if (sidebar.firstChild) {
            sidebar.insertBefore(header, sidebar.firstChild);
        } else {
            sidebar.appendChild(header);
        }
    }
    
    // Get the toggle button that should now exist
    const toggleButton = document.querySelector('.sidebar-toggle');
    if (!toggleButton) {
        console.warn('Sidebar toggle button not found');
        return;
    }
    
    // Apply saved state (collapsed or expanded)
    const sidebarState = localStorage.getItem('sidebarState') || 'expanded';
    if (sidebarState === 'collapsed') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
    }
    
    // Add click event to toggle button
    toggleButton.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('sidebar-collapsed');
        
        // Save state to localStorage
        const newState = sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded';
        localStorage.setItem('sidebarState', newState);
    });
    
    // Add icons to navigation items if they don't already have them
    addIconsToNavItems();
}

/**
 * Creates a sidebar header with a title and toggle button
 */
function createSidebarHeader() {
    const header = document.createElement('div');
    header.className = 'sidebar-header';
    
    // Get the existing title or create a new one
    let title = document.querySelector('.sidebar-title');
    if (!title) {
        title = document.createElement('h1');
        title.className = 'sidebar-title';
        title.textContent = 'Bible Crawl';
    } else {
        // Move the existing title to the header
        title.parentNode.removeChild(title);
    }
    
    const toggle = document.createElement('button');
    toggle.className = 'sidebar-toggle';
    toggle.setAttribute('aria-label', 'Toggle Sidebar');
    toggle.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18">
        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" fill="currentColor"/>
    </svg>`;
    
    header.appendChild(title);
    header.appendChild(toggle);
    
    return header;
}

/**
 * Initialize dark mode functionality
 */
function initializeDarkMode() {
    // Add theme toggle button to main header
    const mainHeader = document.querySelector('.main-header');
    if (mainHeader) {
        const themeToggle = document.createElement('button');
        themeToggle.className = 'theme-toggle';
        themeToggle.setAttribute('aria-label', 'Toggle Dark/Light Mode');
        themeToggle.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path d="M20 8.69V4h-4.69L12 .69 8.69 4H4v4.69L.69 12 4 15.31V20h4.69L12 23.31 15.31 20H20v-4.69L23.31 12 20 8.69zm-2 5.79V18h-3.52L12 20.48 9.52 18H6v-3.52L3.52 12 6 9.52V6h3.52L12 3.52 14.48 6H18v3.52L20.48 12 18 14.48zM12 6.5c-3.03 0-5.5 2.47-5.5 5.5s2.47 5.5 5.5 5.5 5.5-2.47 5.5-5.5-2.47-5.5-5.5-5.5zm0 9c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z" fill="currentColor"/>
        </svg>`;
        
        mainHeader.appendChild(themeToggle);
        
        // Add click event to toggle dark mode
        themeToggle.addEventListener('click', function() {
            toggleDarkMode();
        });
    }
    
    // Apply saved theme setting
    const darkModeEnabled = localStorage.getItem('darkMode') === 'true';
    if (darkModeEnabled) {
        document.documentElement.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
}

/**
 * Toggle dark mode on/off
 */
function toggleDarkMode() {
    const isDarkMode = document.documentElement.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', isDarkMode);
    updateDarkModeIcon(isDarkMode);
    
    // Show toast notification
    if (isDarkMode) {
        showToast('Dark Mode Enabled', 'The dark theme has been applied.', 'info', 3000);
    } else {
        showToast('Light Mode Enabled', 'The light theme has been applied.', 'info', 3000);
    }
}

/**
 * Update the dark mode toggle icon based on current state
 */
function updateDarkModeIcon(isDarkMode) {
    const themeToggle = document.querySelector('.theme-toggle');
    if (!themeToggle) return;
    
    if (isDarkMode) {
        themeToggle.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zm0 16c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7z" fill="currentColor"/>
            <path d="M12 5c-3.86 0-7 3.14-7 7s3.14 7 7 7 7-3.14 7-7-3.14-7-7-7z" fill="currentColor"/>
        </svg>`;
    } else {
        themeToggle.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path d="M20 8.69V4h-4.69L12 .69 8.69 4H4v4.69L.69 12 4 15.31V20h4.69L12 23.31 15.31 20H20v-4.69L23.31 12 20 8.69zm-2 5.79V18h-3.52L12 20.48 9.52 18H6v-3.52L3.52 12 6 9.52V6h3.52L12 3.52 14.48 6H18v3.52L20.48 12 18 14.48zM12 6.5c-3.03 0-5.5 2.47-5.5 5.5s2.47 5.5 5.5 5.5 5.5-2.47 5.5-5.5-2.47-5.5-5.5-5.5zm0 9c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z" fill="currentColor"/>
        </svg>`;
    }
}

/**
 * Add icons to sidebar navigation items
 */
function addIconsToNavItems() {
    const navItems = document.querySelectorAll('.sidebar-nav li a');
    
    // Define icons for pages
    const icons = {
        'home': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" fill="currentColor"/>
                </svg>`,
        'crawl-bible': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                    <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z" fill="currentColor"/>
                </svg>`,
        'crawl-verse': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                    <path d="M14 17H4v2h10v-2zm6-8H4v2h16V9zM4 15h16v-2H4v2zM4 5v2h16V5H4z" fill="currentColor"/>
                </svg>`,
        'github': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" fill="currentColor"/>
                </svg>`
    };
    
    navItems.forEach(item => {
        const href = item.getAttribute('href');
        let iconType = 'home';
        
        if (href.includes('crawl-bible')) {
            iconType = 'crawl-bible';
        } else if (href.includes('crawl-verse')) {
            iconType = 'crawl-verse';
        } else if (href.includes('github')) {
            iconType = 'github';
        }
        
        // Add data attribute for easier active state detection
        item.setAttribute('data-page', iconType);
        
        // Update item to have necessary classes and structure
        item.classList.add('sidebar-nav-item');
        
        // Only add icon if it doesn't already have one
        if (!item.querySelector('.sidebar-icon')) {
            const textContent = item.textContent;
            item.innerHTML = `
                <span class="sidebar-icon">${icons[iconType]}</span>
                <span class="sidebar-text">${textContent}</span>
            `;
        }
    });
}

/**
 * Highlights the active navigation link based on the current URL
 * Fixed to prevent home page from being highlighted when on crawl-bible page
 */
function highlightActiveNavLink() {
    const currentURL = window.location.href;
    const navLinks = document.querySelectorAll('.sidebar-nav-item');
    
    // Clear active state from all links first
    navLinks.forEach(link => {
        link.classList.remove('active');
    });
    
    // Handle specific cases with page parameter
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page');
    
    if (page) {
        // If there's a page parameter, highlight the link with matching data-page
        const pageLink = document.querySelector(`.sidebar-nav-item[data-page="${page}"]`);
        if (pageLink) {
            pageLink.classList.add('active');
            return;
        }
    }
    
    // If no page parameter or no match found, fall back to URL matching
    // Find the most specific matching URL to avoid home page being highlighted when on other pages
    let bestMatch = null;
    let bestMatchLength = 0;
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        
        // Skip the home page link for now
        if (link.getAttribute('data-page') === 'home') {
            return;
        }
        
        // Check if this link's href is in the current URL
        if (currentURL.includes(href) && href.length > bestMatchLength) {
            bestMatch = link;
            bestMatchLength = href.length;
        }
    });
    
    // If we found a specific match, highlight it
    if (bestMatch) {
        bestMatch.classList.add('active');
    } else if (currentURL.endsWith('/') || currentURL.endsWith('index.php')) {
        // Only highlight home if we're actually on the home page
        const homeLink = document.querySelector('.sidebar-nav-item[data-page="home"]');
        if (homeLink) {
            homeLink.classList.add('active');
        }
    }
}

let toastrContainer = null;

/**
 * Initializes the toastr container if it doesn't exist
 */
function initializeToastrContainer() {
    if (!document.getElementById('toastr-container')) {
        toastrContainer = document.createElement('div');
        toastrContainer.id = 'toastr-container';
        toastrContainer.className = 'toastr-container';
        document.body.appendChild(toastrContainer);
    } else {
        toastrContainer = document.getElementById('toastr-container');
    }
}

/**
 * Displays a toast notification.
 * @param {string} title The title of the toast.
 * @param {string} message The main message content of the toast.
 * @param {string} type The type of toast (e.g., 'success', 'error', 'warning', 'info'). Defaults to 'info'.
 * @param {number} duration Duration in milliseconds before the toast automatically hides. Defaults to 5000. Set to 0 for no auto-hide.
 */
window.showToast = function(title, message, type = 'info', duration = 5000) {
    if (!toastrContainer) {
        console.error('Toastr container not initialized!');
        // Fallback to alert if container is missing for some reason
        alert(title + ": " + message);
        return;
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    // Icons for each type using Windows Fluent system icons
    let iconSvg = '';
    switch(type) {
        case 'success': 
            iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" fill="currentColor"/>
                      </svg>`;
            break;
        case 'error': 
            iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                      </svg>`;
            break;
        case 'warning': 
            iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" fill="currentColor"/>
                      </svg>`;
            break;
        case 'info': 
        default:
            iconSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"/>
                      </svg>`;
            break;
    }

    toast.innerHTML = `
        <div class="toast-icon">${iconSvg}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" aria-label="Close">&times;</button>
    `;

    toastrContainer.appendChild(toast);

    // Trigger the show animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    const closeButton = toast.querySelector('.toast-close');
    closeButton.addEventListener('click', () => {
        removeToast(toast);
    });

    if (duration > 0) {
        setTimeout(() => {
            removeToast(toast);
        }, duration);
    }
    
    return toast; // Return the toast element in case it needs to be manipulated later
};

/**
 * Removes a toast element with animation
 */
function removeToast(toastElement) {
    toastElement.classList.remove('show');
    
    // Wait for the hide animation to complete before removing the element
    toastElement.addEventListener('transitionend', () => {
        if (toastElement.parentElement) {
            toastElement.remove();
        }
    }, { once: true });
}

// Export any functions or variables that need to be accessible globally
window.fluentApp = {
    showToast,
    toggleSidebar: function() {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (sidebar && mainContent) {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            
            // Save state to localStorage
            const newState = sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded';
            localStorage.setItem('sidebarState', newState);
        }
    },
    toggleDarkMode: toggleDarkMode
}; 