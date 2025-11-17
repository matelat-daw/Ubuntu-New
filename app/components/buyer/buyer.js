// Buyer Component

async function loadBuyerProfile() {
    try {
        const response = await fetch('/api/controllers/user/profile.php', { credentials: 'include' });
        const result = await response.json();
        
        if (result.success) {
            const user = result.data.user;
            currentUser = user;
            
            // Update profile image
            const profileImagePath = user.path ? `/api/${user.path}` : '/api/assets/media/profile.jpg';
            document.getElementById('buyerProfileImage').src = profileImagePath;
            
            // Update info section
            const isPremium = user.role === 'buyer_premium';
            const badgeClass = isPremium ? 'badge bg-warning text-dark' : 'badge bg-primary';
            const upgradeBtn = isPremium ? '' : `<button onclick="upgradeToPremium()" class="btn btn-success btn-sm mt-2"><i class="bi bi-star-fill"></i> Actualizar a Premium</button>`;
            
            document.getElementById('buyerInfo').innerHTML = `
                <h3 class="h4 mb-3">Bienvenido, ${user.name} ${user.surname1 || ''}</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><i class="bi bi-envelope"></i> <strong>Email:</strong> ${user.email}</p>
                        <p class="mb-2"><i class="bi bi-telephone"></i> <strong>Teléfono:</strong> ${user.phone || 'No especificado'}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><i class="bi bi-shield-check"></i> <strong>Estado:</strong> <span class="badge ${user.email_verified ? 'bg-success' : 'bg-warning text-dark'}">${user.email_verified ? 'Verificado' : 'Pendiente verificación'}</span></p>
                        <p class="mb-2"><i class="bi bi-star"></i> <strong>Plan:</strong> <span class="${badgeClass}">${user.role}</span></p>
                    </div>
                </div>
                ${upgradeBtn}
            `;
            
            // Populate form fields
            document.getElementById('updateName').value = user.name || '';
            document.getElementById('updateSurname1').value = user.surname1 || '';
            document.getElementById('updateSurname2').value = user.surname2 || '';
            document.getElementById('updateEmail').value = user.email || '';
            document.getElementById('updatePhone').value = user.phone || '';
            
            // Clear password fields
            document.getElementById('updatePassword').value = '';
            document.getElementById('updateConfirmPassword').value = '';
            document.getElementById('updateProfileImagePreview').innerHTML = '';
        }
    } catch (error) {
        console.error('Error loading profile:', error);
        showModal('Error', 'No se pudo cargar la información del perfil', 'error');
    }
}

async function deleteAccount() {
    if (!confirm('⚠️ ADVERTENCIA ⚠️\n\n¿Estás absolutamente seguro de que quieres eliminar tu cuenta?\n\nEsta acción:\n- Es PERMANENTE e IRREVERSIBLE\n- Eliminará todos tus datos personales\n- Cancelará tus pedidos pendientes\n- No se puede deshacer\n\n¿Deseas continuar?')) {
        return;
    }
    
    // Second confirmation
    const confirmText = prompt('Para confirmar, escribe "ELIMINAR" en mayúsculas:');
    if (confirmText !== 'ELIMINAR') {
        showModal('Cancelado', 'Eliminación de cuenta cancelada', 'warning');
        return;
    }
    
    try {
        const response = await fetch('/api/controllers/user/delete.php', {
            method: 'DELETE',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showModal('Cuenta eliminada', 'Tu cuenta ha sido eliminada exitosamente. Serás redirigido al inicio.', 'success');
            setTimeout(() => {
                currentUser = null;
                closeModal();
                showLogin();
            }, 3000);
        } else {
            showModal('Error', result.message || 'No se pudo eliminar la cuenta', 'error');
        }
    } catch (error) {
        console.error('Error deleting account:', error);
        showModal('Error de conexión', 'No se pudo conectar con el servidor', 'error');
    }
}

// Update profile image preview
document.getElementById('updateProfileImage').addEventListener('change', (e) => {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('updateProfileImagePreview');
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
                <div class="d-inline-block position-relative mt-2">
                    <img src="${e.target.result}" alt="Vista previa" class="rounded-circle border" style="width: 120px; height: 120px; object-fit: cover;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" onclick="clearUpdateProfileImage()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
});

function clearUpdateProfileImage() {
    document.getElementById('updateProfileImage').value = '';
    document.getElementById('updateProfileImagePreview').innerHTML = '';
}

// Update profile form submission
document.getElementById('updateProfileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const password = document.getElementById('updatePassword').value.trim();
    const confirmPassword = document.getElementById('updateConfirmPassword').value.trim();
    
    // Validate password if provided
    if (password || confirmPassword) {
        if (password.length < 8) {
            showModal('Contraseña inválida', 'La contraseña debe tener al menos 8 caracteres.', 'error');
            return;
        }
        
        if (password !== confirmPassword) {
            showModal('Contraseñas no coinciden', 'Las contraseñas no son iguales. Por favor, verifica e intenta nuevamente.', 'error');
            return;
        }
    }
    
    // Use FormData to support file uploads
    const formData = new FormData(e.target);
    
    // Remove confirm_password field
    formData.delete('confirm_password');
    
    // Remove password if empty
    if (!password) {
        formData.delete('password');
    }
    
    try {
        const response = await fetch('/api/controllers/user/update.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('updateProfileMessage', 'Perfil actualizado exitosamente!', 'success');
            // Reload profile data
            setTimeout(() => {
                loadBuyerProfile();
            }, 1500);
        } else {
            const errors = result.errors ? Object.values(result.errors).join(', ') : result.message;
            showMessage('updateProfileMessage', errors, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('updateProfileMessage', 'Error al actualizar el perfil', 'danger');
    }
});
