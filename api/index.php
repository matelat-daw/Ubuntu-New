<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Platform</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 40px; max-width: 500px; margin: 20px auto; }
        .dashboard { background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 40px; max-width: 1200px; margin: 20px auto; }
        h1 { color: #333; margin-bottom: 10px; font-size: 28px; }
        h2 { color: #333; margin-bottom: 20px; font-size: 24px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px; }
        input, select, textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; font-family: inherit; }
        textarea { resize: vertical; min-height: 100px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; }
        button, .btn { padding: 14px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; display: inline-block; }
        button:hover, .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
        button:active, .btn:active { transform: translateY(0); }
        .btn-secondary { background: #6c757d; }
        .btn-danger { background: #dc3545; }
        .btn-success { background: #28a745; }
        .message { margin-top: 20px; padding: 12px; border-radius: 8px; font-size: 14px; display: none; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .link { display: block; text-align: center; margin-top: 20px; color: #667eea; text-decoration: none; font-weight: 500; }
        .link:hover { text-decoration: underline; }
        .hidden { display: none !important; }
        .user-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .user-info h3 { margin-bottom: 10px; color: #333; }
        .user-info p { color: #666; margin-bottom: 5px; }
        .role-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-top: 5px; }
        .role-badge.seller { background: #e3f2fd; color: #1976d2; }
        .role-badge.buyer { background: #f3e5f5; color: #7b1fa2; }
        .role-badge.admin { background: #ffebee; color: #c62828; }
        .product-form { background: #f8f9fa; padding: 30px; border-radius: 8px; margin-top: 20px; }
        .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; width: 100%; }
        .file-input-wrapper input[type=file] { position: absolute; left: -9999px; }
        .file-input-label { display: block; padding: 12px; border: 2px dashed #667eea; border-radius: 8px; text-align: center; cursor: pointer; background: #f8f9fa; transition: all 0.3s; }
        .file-input-label:hover { background: #e9ecef; border-color: #764ba2; }
        .preview-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 15px; }
        .preview-item { position: relative; border-radius: 8px; overflow: hidden; border: 2px solid #e0e0e0; }
        .preview-item img { width: 100%; height: 150px; object-fit: cover; }
        .preview-item .remove-btn { position: absolute; top: 5px; right: 5px; background: rgba(220, 53, 69, 0.9); color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; font-size: 16px; line-height: 1; padding: 0; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
        .product-card { border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .product-card img { width: 100%; height: 200px; object-fit: cover; }
        .product-card-body { padding: 15px; }
        .product-card h3 { font-size: 18px; margin-bottom: 8px; color: #333; }
        .product-card .price { font-size: 24px; font-weight: bold; color: #667eea; margin-bottom: 8px; }
        .product-card .stock { font-size: 14px; color: #666; margin-bottom: 10px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .checkbox-group { display: flex; align-items: center; margin-bottom: 20px; }
        .checkbox-group input[type="checkbox"] { width: auto; margin-right: 8px; }
        .checkbox-group label { margin-bottom: 0; font-weight: 400; }
        .filter-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-bar input, .filter-bar select { flex: 1; min-width: 200px; }
        .loading { text-align: center; padding: 40px; color: #666; }
        
        /* Modal styles */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .modal { background: white; border-radius: 12px; padding: 30px; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: modalFadeIn 0.3s ease; }
        @keyframes modalFadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { display: flex; align-items: center; margin-bottom: 20px; }
        .modal-icon { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 15px; }
        .modal-icon.error { background: #fee; color: #c62828; }
        .modal-icon.success { background: #e8f5e9; color: #2e7d32; }
        .modal-icon.warning { background: #fff3e0; color: #f57c00; }
        .modal-title { font-size: 20px; font-weight: 600; color: #333; margin: 0; }
        .modal-body { color: #666; margin-bottom: 25px; line-height: 1.5; }
        .modal-footer { display: flex; gap: 10px; justify-content: flex-end; }
        .modal-btn { padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .modal-btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .modal-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .modal-btn-secondary { background: #e0e0e0; color: #333; }
        .modal-btn-secondary:hover { background: #d0d0d0; }
    </style>
</head>
<body>
    <!-- Login Form -->
    <div id="loginContainer" class="container">
        <h1>Iniciar Sesión</h1>
        <p class="subtitle">E-commerce Platform</p>
        <form id="loginForm">
            <div class="form-group">
                <label for="loginEmail">Correo electrónico</label>
                <input type="email" id="loginEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="loginPassword">Contraseña</label>
                <input type="password" id="loginPassword" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <a href="#" class="link" id="showRegisterLink">¿No tienes cuenta? Créala aquí</a>
        <div id="loginMessage" class="message"></div>
    </div>
    
    <!-- Register Form -->
    <div id="registerContainer" class="container hidden">
        <h1>Crear Cuenta</h1>
        <p class="subtitle">Registro de nuevo usuario</p>
        <form id="registerForm">
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" required placeholder="Ej: Juan">
            </div>
            <div class="form-group">
                <label for="surname1">Primer apellido *</label>
                <input type="text" id="surname1" name="surname1" required placeholder="Ej: García">
            </div>
            <div class="form-group">
                <label for="surname2">Segundo apellido</label>
                <input type="text" id="surname2" name="surname2" placeholder="Ej: López (opcional)">
            </div>
            <div class="form-group">
                <label for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email" required placeholder="tu@email.com">
            </div>
            <div class="form-group">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required placeholder="Mínimo 8 caracteres">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña *</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repite tu contraseña">
            </div>
            <div class="form-group">
                <label>Tipo de cuenta *</label>
                <div style="display: flex; gap: 20px; margin-top: 10px;">
                    <label style="display: flex; align-items: center; cursor: pointer; font-weight: 400;">
                        <input type="radio" name="role" value="seller_basic" required style="width: auto; margin-right: 8px;">
                        🏪 Vendedor
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer; font-weight: 400;">
                        <input type="radio" name="role" value="buyer_basic" required style="width: auto; margin-right: 8px;">
                        🛒 Comprador
                    </label>
                </div>
                <small style="color: #999; font-size: 12px; margin-top: 5px; display: block;">Podrás actualizar a Premium más adelante</small>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">Acepto los términos y condiciones</label>
            </div>
            <button type="submit">Crear cuenta</button>
        </form>
        <a href="#" class="link" id="showLoginLink">¿Ya tienes cuenta? Inicia sesión</a>
        <div id="registerMessage" class="message"></div>
    </div>
    
    <!-- Dashboard (Seller) -->
    <div id="sellerDashboard" class="dashboard hidden">
        <div class="top-bar">
            <div>
                <h1>Panel de Vendedor</h1>
                <p class="subtitle">Gestiona tus productos</p>
            </div>
            <button onclick="logout()" class="btn btn-danger">Cerrar Sesión</button>
        </div>
        <div class="user-info" id="sellerInfo"></div>
        <div class="product-form">
            <h2>Subir Nuevo Producto</h2>
            <form id="productForm">
                <div class="form-group">
                    <label for="productName">Nombre del producto *</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="productShortDesc">Descripción corta</label>
                    <input type="text" id="productShortDesc" name="short_description">
                </div>
                <div class="form-group">
                    <label for="productDesc">Descripción completa *</label>
                    <textarea id="productDesc" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="productPrice">Precio (€) *</label>
                    <input type="number" id="productPrice" name="price" step="0.01" min="0.01" required>
                </div>
                <div class="form-group">
                    <label for="productStock">Stock *</label>
                    <input type="number" id="productStock" name="stock" min="0" required>
                </div>
                <div class="form-group">
                    <label>Imágenes del producto * (mínimo 1, máximo 10)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="productImages" name="images[]" accept="image/*" multiple required>
                        <label for="productImages" class="file-input-label">📷 Haz clic para seleccionar imágenes</label>
                    </div>
                    <div id="imagePreview" class="preview-container"></div>
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" id="productFeatured" name="featured">
                    <label for="productFeatured">Producto destacado</label>
                </div>
                <button type="submit">Publicar Producto</button>
            </form>
        </div>
        <div id="productMessage" class="message"></div>
    </div>
    
    <!-- Dashboard (Buyer) -->
    <div id="buyerDashboard" class="dashboard hidden">
        <div class="top-bar">
            <div>
                <h1>Catálogo de Productos</h1>
                <p class="subtitle">Encuentra lo que necesitas</p>
            </div>
            <button onclick="logout()" class="btn btn-danger">Cerrar Sesión</button>
        </div>
        <div class="user-info" id="buyerInfo"></div>
        <div class="filter-bar">
            <input type="text" id="searchProducts" placeholder="Buscar productos...">
            <select id="sortProducts">
                <option value="creation_date">Más recientes</option>
                <option value="price">Precio (bajo a alto)</option>
                <option value="name">Nombre A-Z</option>
            </select>
            <button onclick="loadProducts()" class="btn">Buscar</button>
        </div>
        <div id="productsContainer" class="products-grid"></div>
    </div>
    
    <!-- Dashboard (Admin) -->
    <div id="adminDashboard" class="dashboard hidden">
        <div class="top-bar">
            <div>
                <h1>Panel de Administración</h1>
                <p class="subtitle">Gestión completa del sistema</p>
            </div>
            <button onclick="logout()" class="btn btn-danger">Cerrar Sesión</button>
        </div>
        <div class="user-info" id="adminInfo"></div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
            <div style="background: #e3f2fd; padding: 20px; border-radius: 8px;">
                <h3 style="color: #1976d2; margin-bottom: 15px;">📊 Reportes de Facturación</h3>
                <div class="form-group">
                    <label>Fecha inicio</label>
                    <input type="date" id="billingStartDate">
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" id="billingEndDate">
                </div>
                <button onclick="downloadBillingReport()" class="btn btn-success" style="width: 100%;">Descargar Excel</button>
            </div>
            <div style="background: #f3e5f5; padding: 20px; border-radius: 8px;">
                <h3 style="color: #7b1fa2; margin-bottom: 15px;">📈 Reporte de Ventas</h3>
                <p style="color: #666; margin-bottom: 15px;">Productos vendidos y comisiones</p>
                <button onclick="downloadSalesReport()" class="btn btn-success" style="width: 100%;">Descargar Excel</button>
            </div>
            <div style="background: #fff3e0; padding: 20px; border-radius: 8px;">
                <h3 style="color: #e65100; margin-bottom: 15px;">🛍️ Ver Productos</h3>
                <p style="color: #666; margin-bottom: 15px;">Todos los productos del sistema</p>
                <button onclick="viewAllProducts()" class="btn" style="width: 100%;">Ver Catálogo</button>
            </div>
        </div>
        <div id="adminProductsContainer" class="products-grid hidden" style="margin-top: 30px;"></div>
    </div>
    
    <!-- Modal Container -->
    <div id="modalOverlay" class="modal-overlay hidden" onclick="closeModal()">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div class="modal-icon" id="modalIcon">⚠️</div>
                <h3 class="modal-title" id="modalTitle">Título</h3>
            </div>
            <div class="modal-body" id="modalBody">Mensaje</div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-primary" onclick="closeModal()">Aceptar</button>
            </div>
        </div>
    </div>
    
    <script>
        let currentUser = null;
        let selectedImages = [];
        
        // Modal functions
        function showModal(title, message, type = 'error') {
            const modal = document.getElementById('modalOverlay');
            const icon = document.getElementById('modalIcon');
            const titleEl = document.getElementById('modalTitle');
            const bodyEl = document.getElementById('modalBody');
            
            titleEl.textContent = title;
            bodyEl.textContent = message;
            
            // Set icon based on type
            icon.className = 'modal-icon ' + type;
            if (type === 'error') {
                icon.textContent = '❌';
            } else if (type === 'success') {
                icon.textContent = '✅';
            } else if (type === 'warning') {
                icon.textContent = '⚠️';
            }
            
            modal.classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('modalOverlay').classList.add('hidden');
        }
        
        document.addEventListener('DOMContentLoaded', () => { checkSession(); });
        
        async function checkSession() {
            try {
                const response = await fetch('/api/controllers/user/profile.php', { credentials: 'include' });
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        currentUser = result.data.user;
                        showDashboard();
                        return;
                    }
                }
            } catch (error) {
                console.log('No active session');
            }
            showLogin();
        }
        
        function showLogin() {
            document.getElementById('loginContainer').classList.remove('hidden');
            document.getElementById('registerContainer').classList.add('hidden');
            hideAllDashboards();
        }
        
        function showRegister() {
            document.getElementById('loginContainer').classList.add('hidden');
            document.getElementById('registerContainer').classList.remove('hidden');
            hideAllDashboards();
        }
        
        function hideAllDashboards() {
            document.getElementById('sellerDashboard').classList.add('hidden');
            document.getElementById('buyerDashboard').classList.add('hidden');
            document.getElementById('adminDashboard').classList.add('hidden');
        }
        
        function showDashboard() {
            document.getElementById('loginContainer').classList.add('hidden');
            document.getElementById('registerContainer').classList.add('hidden');
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
            document.getElementById('sellerDashboard').classList.remove('hidden');
            const isPremium = currentUser.role === 'seller_premium';
            const upgradeBtn = isPremium ? '' : `<button onclick="upgradeToPremium()" class="btn btn-success" style="margin-top: 10px;">⭐ Actualizar a Premium</button>`;
            document.getElementById('sellerInfo').innerHTML = `
                <h3>Bienvenido, ${currentUser.name}</h3>
                <p><strong>Email:</strong> ${currentUser.email}</p>
                <p><strong>Rol:</strong> <span class="role-badge seller">${currentUser.role}</span></p>
                ${upgradeBtn}
            `;
        }
        
        function showBuyerDashboard() {
            document.getElementById('buyerDashboard').classList.remove('hidden');
            const isPremium = currentUser.role === 'buyer_premium';
            const upgradeBtn = isPremium ? '' : `<button onclick="upgradeToPremium()" class="btn btn-success" style="margin-top: 10px;">⭐ Actualizar a Premium</button>`;
            document.getElementById('buyerInfo').innerHTML = `
                <h3>Bienvenido, ${currentUser.name}</h3>
                <p><strong>Email:</strong> ${currentUser.email}</p>
                <p><strong>Rol:</strong> <span class="role-badge buyer">${currentUser.role}</span></p>
                ${upgradeBtn}
            `;
            loadProducts();
        }
        
        function showAdminDashboard() {
            document.getElementById('adminDashboard').classList.remove('hidden');
            document.getElementById('adminInfo').innerHTML = `<h3>Bienvenido, ${currentUser.name}</h3><p><strong>Email:</strong> ${currentUser.email}</p><p><strong>Rol:</strong> <span class="role-badge admin">${currentUser.role}</span></p>`;
        }
        
        document.getElementById('showRegisterLink').addEventListener('click', (e) => { e.preventDefault(); showRegister(); });
        document.getElementById('showLoginLink').addEventListener('click', (e) => { e.preventDefault(); showLogin(); });
        
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            try {
                const response = await fetch('/api/controllers/auth/login.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'include', body: JSON.stringify(data) });
                const result = await response.json();
                if (result.success) {
                    currentUser = result.data.user;
                    showDashboard();
                } else {
                    showMessage('loginMessage', result.message, 'error');
                }
            } catch (error) {
                showMessage('loginMessage', 'Error al conectar con el servidor', 'error');
            }
        });
        
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Validate passwords match FIRST
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
            
            // Check if terms are accepted
            if (!document.getElementById('terms').checked) {
                showModal('Términos y condiciones', 'Debes aceptar los términos y condiciones para continuar.', 'warning');
                return false;
            }
            
            // NOW create FormData after validations pass
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            delete data.confirm_password;
            delete data.terms;
            
            try {
                const response = await fetch('/api/controllers/auth/register.php', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify(data) 
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showModal('¡Registro exitoso!', result.message + ' Revisa tu correo para verificar tu cuenta. Serás redirigido al login en unos segundos.', 'success');
                    e.target.reset();
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
        
        document.getElementById('productImages').addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            selectedImages = files;
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';
            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'preview-item';
                    div.innerHTML = `<img src="${e.target.result}" alt="Preview ${index + 1}"><button type="button" class="remove-btn" onclick="removeImage(${index})">×</button>`;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
        
        function removeImage(index) {
            selectedImages.splice(index, 1);
            const dataTransfer = new DataTransfer();
            selectedImages.forEach(file => dataTransfer.items.add(file));
            document.getElementById('productImages').files = dataTransfer.files;
            document.getElementById('productImages').dispatchEvent(new Event('change'));
        }
        
        document.getElementById('productForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch('/api/controllers/products/create.php', { method: 'POST', credentials: 'include', body: formData });
                const result = await response.json();
                if (result.success) {
                    showMessage('productMessage', 'Producto publicado exitosamente! La mejor imagen fue seleccionada automáticamente.', 'success');
                    e.target.reset();
                    document.getElementById('imagePreview').innerHTML = '';
                    selectedImages = [];
                } else {
                    const errors = result.errors ? Object.values(result.errors).join(', ') : result.message;
                    showMessage('productMessage', errors, 'error');
                }
            } catch (error) {
                showMessage('productMessage', 'Error al publicar el producto', 'error');
            }
        });
        
        async function loadProducts() {
            const search = document.getElementById('searchProducts')?.value || '';
            const sort = document.getElementById('sortProducts')?.value || 'creation_date';
            const container = document.getElementById('productsContainer');
            container.innerHTML = '<div class="loading">Cargando productos...</div>';
            try {
                const url = `/api/controllers/products/list.php?search=${encodeURIComponent(search)}&sort_by=${sort}&limit=20`;
                const response = await fetch(url);
                const result = await response.json();
                if (result.success && result.data.products.length > 0) {
                    container.innerHTML = result.data.products.map(product => `
                        <div class="product-card">
                            <img src="/api/${product.primary_image || 'assets/no-image.jpg'}" alt="${product.name}">
                            <div class="product-card-body">
                                <h3>${product.name}</h3>
                                <div class="price">€${parseFloat(product.price).toFixed(2)}</div>
                                <div class="stock">Stock: ${product.stock} unidades</div>
                                <p style="font-size: 14px; color: #666; margin-bottom: 10px;">${product.short_description || ''}</p>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div class="loading">No se encontraron productos</div>';
                }
            } catch (error) {
                container.innerHTML = '<div class="loading">Error al cargar productos</div>';
            }
        }
        
        async function downloadBillingReport() {
            const startDate = document.getElementById('billingStartDate').value;
            const endDate = document.getElementById('billingEndDate').value;
            if (!startDate || !endDate) {
                alert('Por favor selecciona ambas fechas');
                return;
            }
            window.location.href = `/api/controllers/reports/billing.php?start_date=${startDate}&end_date=${endDate}&format=excel`;
        }
        
        function downloadSalesReport() {
            window.location.href = '/api/controllers/reports/sales.php?format=excel';
        }
        
        async function viewAllProducts() {
            const container = document.getElementById('adminProductsContainer');
            container.classList.remove('hidden');
            container.innerHTML = '<div class="loading">Cargando productos...</div>';
            try {
                const response = await fetch('/api/controllers/products/list.php?limit=100');
                const result = await response.json();
                if (result.success && result.data.products.length > 0) {
                    container.innerHTML = result.data.products.map(product => `
                        <div class="product-card">
                            <img src="/api/${product.primary_image || 'assets/no-image.jpg'}" alt="${product.name}">
                            <div class="product-card-body">
                                <h3>${product.name}</h3>
                                <div class="price">€${parseFloat(product.price).toFixed(2)}</div>
                                <div class="stock">Stock: ${product.stock}</div>
                                <p style="font-size: 12px; color: #999; margin-top: 5px;">Vendedor: ${product.seller_name}</p>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div class="loading">No hay productos</div>';
                }
            } catch (error) {
                container.innerHTML = '<div class="loading">Error al cargar productos</div>';
            }
        }
        
        async function logout() {
            try {
                await fetch('/api/controllers/auth/logout.php', { method: 'POST', credentials: 'include' });
            } catch (error) {
                console.error('Logout error:', error);
            }
            currentUser = null;
            showLogin();
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
                    showModal('¡Actualización exitosa!', result.message + ' Recarga la página para ver los cambios.', 'success');
                    // Update current user
                    currentUser.role = result.data.user.role;
                    // Refresh dashboard after 2 seconds
                    setTimeout(() => {
                        closeModal();
                        showDashboard();
                    }, 2000);
                } else {
                    let errorMsg = result.message;
                    if (result.errors && result.errors.requirements) {
                        errorMsg += '\n\nRequisitos faltantes:\n' + result.errors.requirements.join('\n');
                    }
                    showModal('No se pudo actualizar', errorMsg, 'error');
                }
            } catch (error) {
                console.error('Upgrade error:', error);
                showModal('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
            }
        }
        
        function showMessage(elementId, text, type) {
            const messageDiv = document.getElementById(elementId);
            messageDiv.textContent = text;
            messageDiv.className = 'message ' + type;
            messageDiv.style.display = 'block';
            setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
        }
    </script>
</body>
</html>
