// Register Component

function showRegister() {
    document.getElementById('loginContainer').classList.add('d-none');
    document.getElementById('registerContainer').classList.remove('d-none');
    document.getElementById('sellerDashboard').classList.add('d-none');
    document.getElementById('buyerDashboard').classList.add('d-none');
    document.getElementById('adminDashboard').classList.add('d-none');
}

// Profile image preview
document.getElementById('profile_image').addEventListener('change', (e) => {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('profileImagePreview');
    previewContainer.innerHTML = '';
    
    if (file) {
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            showModal('Imagen demasiado grande', 'La imagen de perfil no puede superar los 5MB.', 'warning');
            e.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewContainer.innerHTML = `
                <div class="d-inline-block position-relative">
                    <img src="${e.target.result}" alt="Vista previa" class="rounded-circle border" style="width: 120px; height: 120px; object-fit: cover;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" onclick="clearProfileImage()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
});

function clearProfileImage() {
    document.getElementById('profile_image').value = '';
    document.getElementById('profileImagePreview').innerHTML = '';
}

// Register form submission
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm_password').value.trim();
    
    if (!password || password.length < 8) {
        showModal('Contraseña inválida', 'La contraseña debe tener al menos 8 caracteres.', 'error');
        return false;
    }
    
    if (password !== confirmPassword) {
        showModal('Contraseñas no coinciden', 'Las contraseñas que ingresaste no son iguales. Por favor, verifica e intenta nuevamente.', 'error');
        document.getElementById('confirm_password').focus();
        return false;
    }
    
    if (!document.getElementById('terms').checked) {
        showModal('Términos y condiciones', 'Debes aceptar los términos y condiciones para continuar.', 'warning');
        return false;
    }
    
    // Use FormData directly to support file uploads
    const formData = new FormData(e.target);
    
    // Remove fields that shouldn't be sent
    formData.delete('confirm_password');
    formData.delete('terms');
    
    try {
        const response = await fetch('/api/controllers/auth/register.php', { 
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showModal('¡Registro exitoso!', result.message + ' Revisa tu correo para verificar tu cuenta. Serás redirigido al login en unos segundos.', 'success');
            e.target.reset();
            document.getElementById('profileImagePreview').innerHTML = '';
            setTimeout(() => {
                closeModal();
                showLogin();
            }, 3000);
        } else {
            const errors = result.errors ? Object.values(result.errors).join('\n') : result.message;
            showModal('Error en el registro', errors, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showModal('Error de conexión', 'No se pudo conectar con el servidor. Verifica tu conexión e intenta nuevamente.', 'error');
    }
});

// Show login link handler
document.getElementById('showLoginLink').addEventListener('click', (e) => {
    e.preventDefault();
    showLogin();
});
