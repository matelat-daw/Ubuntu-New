<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - API Business</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
            text-align: center;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert.show {
            display: block;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        .user-info {
            display: none;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .user-info.show {
            display: block;
        }
        
        .user-info h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .user-info p {
            color: #666;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }
        
        .spinner.show {
            display: block;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Iniciar Sesión</h1>
        <p class="subtitle">Accede a tu cuenta</p>
        
        <div id="alert" class="alert"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn" id="submitBtn">
                <span id="btn-text">Iniciar Sesión</span>
                <div class="spinner" id="spinner"></div>
            </button>
        </form>
        
        <div class="links">
            <a href="/">¿No tienes cuenta? Regístrate</a>
        </div>
        
        <div id="user-info" class="user-info">
            <h3>✅ Sesión Iniciada</h3>
            <p><strong>Nombre:</strong> <span id="user-name"></span></p>
            <p><strong>Email:</strong> <span id="user-email"></span></p>
            <p><strong>Rol:</strong> <span id="user-role"></span></p>
            <p><strong>Token guardado en cookie</strong></p>
            <button class="btn" onclick="logout()" style="margin-top: 15px;">Cerrar Sesión</button>
            <button class="btn" onclick="getProfile()" style="margin-top: 10px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">Ver Perfil Completo</button>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btn-text');
        const spinner = document.getElementById('spinner');
        const alertBox = document.getElementById('alert');
        const userInfo = document.getElementById('user-info');
        
        // Check if already logged in
        if (document.cookie.includes('auth_token')) {
            checkSession();
        }
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            alertBox.classList.remove('show');
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            spinner.classList.add('show');
            
            try {
                const formData = {
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value
                };
                
                const response = await fetch('/api/controllers/auth/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData),
                    credentials: 'include' // Important for cookies
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('✅ ' + data.message, 'success');
                    form.style.display = 'none';
                    document.querySelector('.links').style.display = 'none';
                    
                    // Show user info
                    document.getElementById('user-name').textContent = data.data.user.name + ' ' + data.data.user.surname1;
                    document.getElementById('user-email').textContent = data.data.user.email;
                    document.getElementById('user-role').textContent = data.data.user.role;
                    userInfo.classList.add('show');
                    
                    console.log('JWT Token:', data.data.token);
                    console.log('Session ID:', data.data.session_id);
                    console.log('Expires at:', data.data.expires_at);
                } else {
                    showAlert('❌ ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('❌ Error de conexión con el servidor', 'error');
            } finally {
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                spinner.classList.remove('show');
            }
        });
        
        async function checkSession() {
            try {
                const response = await fetch('/api/controllers/user/profile.php', {
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    form.style.display = 'none';
                    document.querySelector('.links').style.display = 'none';
                    
                    document.getElementById('user-name').textContent = data.data.user.name + ' ' + data.data.user.surname1;
                    document.getElementById('user-email').textContent = data.data.user.email;
                    document.getElementById('user-role').textContent = data.data.user.role;
                    userInfo.classList.add('show');
                }
            } catch (error) {
                console.error('Error checking session:', error);
            }
        }
        
        async function logout() {
            try {
                const response = await fetch('/api/controllers/auth/logout.php', {
                    method: 'POST',
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('✅ Sesión cerrada correctamente', 'success');
                    userInfo.classList.remove('show');
                    form.style.display = 'block';
                    document.querySelector('.links').style.display = 'block';
                    form.reset();
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('❌ Error al cerrar sesión', 'error');
            }
        }
        
        async function getProfile() {
            try {
                const response = await fetch('/api/controllers/user/profile.php', {
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    console.log('Perfil completo:', data.data.user);
                    alert('Perfil obtenido. Revisa la consola (F12)');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('❌ Error al obtener perfil', 'error');
            }
        }
        
        function showAlert(message, type) {
            alertBox.innerHTML = message;
            alertBox.className = 'alert alert-' + type + ' show';
            
            if (type === 'success') {
                setTimeout(() => {
                    alertBox.classList.remove('show');
                }, 5000);
            }
        }
    </script>
</body>
</html>
