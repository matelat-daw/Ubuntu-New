// Header and Navigation Functions

// Font Size Management
let currentFontSizeIndex = 2; // Start at 1rem (index 2)
const fontSizes = [0.5, 0.75, 1, 1.25, 1.5]; // rem values - 5 levels for 4 changes

function changeFontSize(direction) {
    if (direction === 'decrease' && currentFontSizeIndex > 0) {
        currentFontSizeIndex--;
    } else if (direction === 'increase' && currentFontSizeIndex < fontSizes.length - 1) {
        currentFontSizeIndex++;
    }
    
    const multiplier = fontSizes[currentFontSizeIndex];
    document.documentElement.style.fontSize = (16 * multiplier) + 'px';
    
    // Save preference
    localStorage.setItem('fontSizeIndex', currentFontSizeIndex);
}

// Theme Management
let isDarkTheme = false;

function toggleTheme() {
    isDarkTheme = !isDarkTheme;
    const body = document.body;
    const icon = document.querySelector('#themeToggle i');
    
    if (isDarkTheme) {
        body.classList.add('dark-theme');
        icon.classList.remove('bi-moon-fill');
        icon.classList.add('bi-sun-fill');
    } else {
        body.classList.remove('dark-theme');
        icon.classList.remove('bi-sun-fill');
        icon.classList.add('bi-moon-fill');
    }
    
    // Save preference
    localStorage.setItem('theme', isDarkTheme ? 'dark' : 'light');
}

// Load saved preferences
function loadUserPreferences() {
    // Load font size
    const savedIndex = localStorage.getItem('fontSizeIndex');
    if (savedIndex !== null) {
        currentFontSizeIndex = parseInt(savedIndex);
        const multiplier = fontSizes[currentFontSizeIndex];
        document.documentElement.style.fontSize = (16 * multiplier) + 'px';
    }
    
    // Load theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        toggleTheme();
    }
}

// Header Info Management
function updateHeaderInfo() {
    const authButtons = document.getElementById('authButtons');
    const userDropdown = document.getElementById('userDropdown');
    
    if (currentUser) {
        // Hide auth buttons, show user dropdown
        authButtons.classList.add('d-none');
        userDropdown.classList.remove('d-none');
        
        // Update user info
        const userName = document.getElementById('userName');
        const userAvatar = document.getElementById('userAvatar');
        
        userName.textContent = currentUser.name || 'Usuario';
        
        if (currentUser.path) {
            userAvatar.src = `/api/${currentUser.path}`;
        }
        
        // Update navigation visibility
        updateNavigation();
    } else {
        // Show auth buttons, hide user dropdown
        authButtons.classList.remove('d-none');
        userDropdown.classList.add('d-none');
        
        // Update navigation visibility
        updateNavigation();
    }
}

function updateNavigation() {
    const sellerItems = document.querySelectorAll('.seller-only');
    const authItems = document.querySelectorAll('.authenticated-only');
    
    if (currentUser) {
        // Show authenticated items
        authItems.forEach(item => item.classList.remove('d-none'));
        
        // Show seller items only for sellers
        if (currentUser.role && currentUser.role.startsWith('seller_')) {
            sellerItems.forEach(item => item.classList.remove('d-none'));
        } else {
            sellerItems.forEach(item => item.classList.add('d-none'));
        }
    } else {
        authItems.forEach(item => item.classList.add('d-none'));
        sellerItems.forEach(item => item.classList.add('d-none'));
    }
}

// Navigation Functions
function showLoginForm() {
    document.getElementById('homeContainer')?.classList.add('d-none');
    document.getElementById('registerContainer')?.classList.add('d-none');
    document.getElementById('loginContainer')?.classList.remove('d-none');
    hideAllDashboards();
}

function showRegisterForm() {
    document.getElementById('homeContainer')?.classList.add('d-none');
    document.getElementById('loginContainer')?.classList.add('d-none');
    document.getElementById('registerContainer')?.classList.remove('d-none');
    hideAllDashboards();
}

function goToDashboard(event) {
    if (event) event.preventDefault();
    if (currentUser) {
        document.getElementById('homeContainer')?.classList.add('d-none');
        document.getElementById('loginContainer')?.classList.add('d-none');
        document.getElementById('registerContainer')?.classList.add('d-none');
        showDashboard();
    }
}

function goToProfile(event) {
    if (event) event.preventDefault();
    if (currentUser) {
        showDashboard();
        // Scroll to profile section or open profile tab
        setTimeout(() => {
            if (currentUser.role.startsWith('seller_')) {
                const tab = document.getElementById('profile-management-tab');
                if (tab) tab.click();
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 100);
    }
}

function showProductsTab(event) {
    if (event) event.preventDefault();
    const tab = document.getElementById('products-tab');
    if (tab) tab.click();
}

function showAddProductTab(event) {
    if (event) event.preventDefault();
    const tab = document.getElementById('add-product-tab');
    if (tab) tab.click();
}

function showProfileTab(event) {
    if (event) event.preventDefault();
    goToProfile(event);
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    loadUserPreferences();
});
