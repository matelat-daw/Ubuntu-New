// Global Application State and Functions

let currentUser = null;
let selectedImages = [];

// Initialize application
document.addEventListener('DOMContentLoaded', () => {
    checkSession();
});

// Session Management
async function checkSession() {
    try {
        const response = await fetch('/api/controllers/user/profile.php', { credentials: 'include' });
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                currentUser = result.data.user;
                updateHeaderInfo();
                showDashboard();
                return;
            }
        }
    } catch (error) {
        console.log('No active session');
    }
    updateHeaderInfo();
    showLogin();
}

// Logout
async function logout(event) {
    if (event) event.preventDefault();
    try {
        await fetch('/api/controllers/auth/logout.php', { method: 'POST', credentials: 'include' });
    } catch (error) {
        console.error('Logout error:', error);
    }
    currentUser = null;
    updateHeaderInfo();
    showLogin();
}

// Dashboard Router
function showDashboard() {
    document.getElementById('loginContainer').classList.add('d-none');
    document.getElementById('registerContainer').classList.add('d-none');
    updateHeaderInfo();
    const role = currentUser.role;
    if (role === 'admin' || role === 'manager') {
        showAdminDashboard();
    } else if (role.startsWith('seller_')) {
        showSellerDashboard();
    } else if (role.startsWith('buyer_')) {
        showBuyerDashboard();
    }
}

function showSellerDashboard() {
    document.getElementById('sellerDashboard').classList.remove('d-none');
    loadSellerProfile();
    loadSellerProducts();
}

function showBuyerDashboard() {
    document.getElementById('buyerDashboard').classList.remove('d-none');
    loadBuyerProfile();
}

function hideAllDashboards() {
    document.getElementById('sellerDashboard').classList.add('d-none');
    document.getElementById('buyerDashboard').classList.add('d-none');
    document.getElementById('adminDashboard').classList.add('d-none');
}

// Upgrade to Premium
async function upgradeToPremium() {
    if (!confirm('¿Estás seguro de que quieres actualizar a Premium?\n\nRequisitos:\n- Cuenta verificada\n- Cuenta con al menos 7 días de antigüedad\n- Al menos 1 producto publicado (vendedores)')) {
        return;
    }
    
    try {
        const response = await fetch('/api/controllers/user/upgrade-premium.php', {
            method: 'POST',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showModal('¡Actualización exitosa!', result.message, 'success');
            setTimeout(() => {
                closeModal();
                checkSession();
            }, 2000);
        } else {
            showModal('Error', result.message || 'No se pudo procesar la actualización', 'error');
        }
    } catch (error) {
        console.error('Upgrade error:', error);
        showModal('Error de conexión', 'No se pudo conectar con el servidor', 'error');
    }
}
