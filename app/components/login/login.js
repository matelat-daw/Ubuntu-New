// Login Component JavaScript

document.getElementById('showRegisterLink').addEventListener('click', (e) => { 
    e.preventDefault(); 
    showRegister(); 
});

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/controllers/auth/login.php', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            credentials: 'include', 
            body: JSON.stringify(data) 
        });
        
        const result = await response.json();
        
        if (result.success) {
            currentUser = result.data.user;
            showDashboard();
        } else {
            showMessage('loginMessage', result.message, 'danger');
        }
    } catch (error) {
        showMessage('loginMessage', 'Error al conectar con el servidor', 'danger');
    }
});

function showLogin() {
    document.getElementById('loginContainer').classList.remove('d-none');
    document.getElementById('registerContainer').classList.add('d-none');
    hideAllDashboards();
}
