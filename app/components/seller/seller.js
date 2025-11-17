// Seller Component

async function loadSellerProfile() {
    try {
        const response = await fetch('/api/controllers/user/profile.php', { credentials: 'include' });
        const result = await response.json();
        
        if (result.success) {
            const user = result.data.user;
            currentUser = user;
            
            // Update profile image
            const profileImagePath = user.path ? `/api/${user.path}` : '/api/assets/media/profile.jpg';
            document.getElementById('sellerProfileImage').src = profileImagePath;
            
            // Update info section
            const isPremium = user.role === 'seller_premium';
            const badgeClass = isPremium ? 'badge bg-warning text-dark' : 'badge bg-primary';
            const upgradeBtn = isPremium ? '' : `<button onclick="upgradeToPremium()" class="btn btn-success btn-sm mt-2"><i class="bi bi-star-fill"></i> Actualizar a Premium</button>`;
            
            document.getElementById('sellerInfo').innerHTML = `
                <h3 class="h4 mb-2">Bienvenido, ${user.name} ${user.surname1 || ''}</h3>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><i class="bi bi-envelope"></i> ${user.email}</p>
                        <p class="mb-1"><i class="bi bi-telephone"></i> ${user.phone || 'No especificado'}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><i class="bi bi-shield-check"></i> <span class="badge ${user.email_verified ? 'bg-success' : 'bg-warning text-dark'}">${user.email_verified ? 'Verificado' : 'Pendiente'}</span></p>
                        <p class="mb-1"><i class="bi bi-star"></i> <span class="${badgeClass}">${user.role}</span></p>
                    </div>
                </div>
                ${upgradeBtn}
            `;
            
            // Populate seller profile form
            document.getElementById('updateSellerName').value = user.name || '';
            document.getElementById('updateSellerSurname1').value = user.surname1 || '';
            document.getElementById('updateSellerSurname2').value = user.surname2 || '';
            document.getElementById('updateSellerEmail').value = user.email || '';
            document.getElementById('updateSellerPhone').value = user.phone || '';
            document.getElementById('updateSellerPassword').value = '';
            document.getElementById('updateSellerConfirmPassword').value = '';
            document.getElementById('updateSellerProfileImagePreview').innerHTML = '';
        }
    } catch (error) {
        console.error('Error loading seller profile:', error);
    }
}

async function loadSellerProducts() {
    const container = document.getElementById('sellerProductsContainer');
    container.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border text-primary"></div><p class="text-muted mt-2">Cargando productos...</p></div>';
    
    try {
        const response = await fetch('/api/controllers/products/list.php?seller_id=' + currentUser.id + '&limit=100', { credentials: 'include' });
        const result = await response.json();
        
        if (result.success && result.data.products.length > 0) {
            container.innerHTML = result.data.products.map(product => `
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 shadow-sm">
                        <img src="/api/${product.primary_image || 'assets/no-image.jpg'}" class="card-img-top" alt="${product.name}" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">${product.name}</h5>
                            <p class="h4 text-success mb-2">€${parseFloat(product.price).toFixed(2)}</p>
                            <p class="text-muted mb-2">
                                <i class="bi bi-box"></i> Stock: ${product.stock} 
                                ${product.stock < 5 ? '<span class="badge bg-warning text-dark">Bajo stock</span>' : ''}
                            </p>
                            <p class="card-text text-muted small">${product.short_description || ''}</p>
                            ${product.featured ? '<span class="badge bg-primary"><i class="bi bi-star-fill"></i> Destacado</span>' : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> No tienes productos publicados aún.
                        <br>
                        <button class="btn btn-primary btn-sm mt-2" onclick="document.getElementById('add-product-tab').click()">
                            <i class="bi bi-plus-circle"></i> Agregar mi primer producto
                        </button>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error al cargar productos</div></div>';
    }
}

function showSellerProfileManagement() {
    document.getElementById('profile-management-tab').click();
}

async function deleteSellerAccount() {
    if (!confirm('⚠️ ADVERTENCIA ⚠️\n\n¿Estás absolutamente seguro de que quieres eliminar tu cuenta de vendedor?\n\nEsta acción:\n- Es PERMANENTE e IRREVERSIBLE\n- Eliminará TODOS tus productos\n- Cancelará pedidos pendientes\n- Eliminará todos tus datos personales\n- No se puede deshacer\n\n¿Deseas continuar?')) {
        return;
    }
    
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
            showModal('Cuenta eliminada', 'Tu cuenta y todos tus productos han sido eliminados exitosamente. Serás redirigido al inicio.', 'success');
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

// Update seller profile image preview
document.getElementById('updateSellerProfileImage').addEventListener('change', (e) => {
    const file = e.target.files[0];
    const previewContainer = document.getElementById('updateSellerProfileImagePreview');
    previewContainer.innerHTML = '';
    
    if (file) {
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
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" onclick="clearUpdateSellerProfileImage()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
});

function clearUpdateSellerProfileImage() {
    document.getElementById('updateSellerProfileImage').value = '';
    document.getElementById('updateSellerProfileImagePreview').innerHTML = '';
}

// Update seller profile form submission
document.getElementById('updateSellerProfileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const password = document.getElementById('updateSellerPassword').value.trim();
    const confirmPassword = document.getElementById('updateSellerConfirmPassword').value.trim();
    
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
    
    const formData = new FormData(e.target);
    formData.delete('confirm_password');
    
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
            showMessage('updateSellerProfileMessage', 'Perfil actualizado exitosamente!', 'success');
            setTimeout(() => {
                loadSellerProfile();
            }, 1500);
        } else {
            const errors = result.errors ? Object.values(result.errors).join(', ') : result.message;
            showMessage('updateSellerProfileMessage', errors, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('updateSellerProfileMessage', 'Error al actualizar el perfil', 'danger');
    }
});
