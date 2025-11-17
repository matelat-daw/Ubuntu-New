// Header and Navigation Functions

function updateHeaderInfo() {
    const headerUserInfo = document.getElementById('headerUserInfo');
    const mainNav = document.getElementById('mainNav');
    
    if (currentUser) {
        const userName = currentUser.name || 'Usuario';
        headerUserInfo.innerHTML = `
            <span class="d-none d-md-inline">Hola, ${userName}</span>
            <i class="bi bi-person-circle ms-2"></i>
        `;
        
        // Show navigation
        mainNav.classList.remove('d-none');
        
        // Update nav visibility based on role
        updateNavigation();
    } else {
        headerUserInfo.innerHTML = '';
        mainNav.classList.add('d-none');
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

function goToDashboard(event) {
    if (event) event.preventDefault();
    if (currentUser) {
        showDashboard();
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
    if (currentUser && currentUser.role) {
        if (currentUser.role.startsWith('seller_')) {
            const tab = document.getElementById('profile-management-tab');
            if (tab) tab.click();
        } else {
            // For buyers, just scroll to profile section
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}
