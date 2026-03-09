// assets/js/scripts.js

// Theme toggling functionality
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    html.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}

// Notification functionality
let notificationCount = 0;

function incrementNotifications() {
    notificationCount++;
    const badges = document.querySelectorAll('.notification-badge');
    badges.forEach(badge => {
        badge.textContent = notificationCount;
        badge.classList.remove('d-none');
    });
}

function clearNotifications() {
    notificationCount = 0;
    const badges = document.querySelectorAll('.notification-badge');
    badges.forEach(badge => {
        badge.textContent = '0';
        badge.classList.add('d-none');
    });
}

// Search functionality
function toggleSearch() {
    const searchContainer = document.getElementById('searchContainer');
    searchContainer.classList.toggle('show');
    if (searchContainer.classList.contains('show')) {
        searchContainer.querySelector('input').focus();
    }
}

// User menu functionality
function toggleUserMenu() {
    const userMenu = document.getElementById('userMenu');
    userMenu.classList.toggle('show');
}

// Social media share functionality
function shareOnSocial(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    let shareUrl;

    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`;
            break;
    }

    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}


// Initialize theme on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    }

    // Close user menu when clicking outside
    document.addEventListener('click', function(event) {
        const userMenu = document.getElementById('userMenu');
        const userButton = document.getElementById('userButton');
        if (userMenu && userMenu.classList.contains('show') && 
            !userMenu.contains(event.target) && 
            !userButton.contains(event.target)) {
            userMenu.classList.remove('show');
        }
    });

    // Handle search with Enter key
    const searchInput = document.querySelector('.search-container input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                performSearch(this.value);
            }
        });
    }
});

// Search functionality
function performSearch(query) {
    // Add your search implementation here
    console.log('Searching for:', query);
    // Example: window.location.href = `/search?q=${encodeURIComponent(query)}`;
}

