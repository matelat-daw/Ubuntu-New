<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Platform</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .glass-effect { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body>
    <!-- Login Form -->
    <div id="loginContainer" class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg glass-effect">
                    <div class="card-body p-5">
                        <h1 class="card-title text-center mb-2">Iniciar Sesión</h1>
                        <p class="text-muted text-center mb-4">E-commerce Platform</p>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="loginEmail" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="loginEmail" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="loginPassword" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="loginPassword" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Iniciar Sesión</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="#" class="text-decoration-none" id="showRegisterLink">¿No tienes cuenta? Créala aquí</a>
                        </div>
                        <div id="loginMessage" class="alert mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Register Form -->
    <div id="registerContainer" class="container d-none">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg glass-effect">
                    <div class="card-body p-5">
                        <h1 class="card-title text-center mb-2">Crear Cuenta</h1>
                        <p class="text-muted text-center mb-4">Registro de nuevo usuario</p>
                        <form id="registerForm">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" id="name" name="name" required placeholder="Ej: Juan">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="surname1" class="form-label">Primer apellido *</label>
                                    <input type="text" class="form-control" id="surname1" name="surname1" required placeholder="Ej: García">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="surname2" class="form-label">Segundo apellido</label>
                                    <input type="text" class="form-control" id="surname2" name="surname2" placeholder="Ej: López">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico *</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="tu@email.com">
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Teléfono *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required placeholder="Ej: +34 600 123 456">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Mínimo 8 caracteres">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar contraseña *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Repite tu contraseña">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Imagen de perfil (opcional)</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                <small class="text-muted">Formatos aceptados: JPG, PNG, GIF (máx. 5MB)</small>
                                <div id="profileImagePreview" class="mt-2"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tipo de cuenta *</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="role" value="seller_basic" id="roleSeller" required>
                                        <label class="form-check-label" for="roleSeller">
                                            🏪 Vendedor
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="role" value="buyer_basic" id="roleBuyer" required>
                                        <label class="form-check-label" for="roleBuyer">
                                            🛒 Comprador
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted">Podrás actualizar a Premium más adelante</small>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Acepto los términos y condiciones y he leído la 
                                    <a href="#" onclick="showPrivacyPolicy(event)" class="text-primary">Política de Privacidad</a>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Crear cuenta</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="#" class="text-decoration-none" id="showLoginLink">¿Ya tienes cuenta? Inicia sesión</a>
                        </div>
                        <div id="registerMessage" class="alert mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dashboard (Seller) -->
    <div id="sellerDashboard" class="container d-none">
        <div class="card shadow-lg glass-effect mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 class="h3 mb-1">Panel de Vendedor</h1>
                        <p class="text-muted mb-0">Gestiona tus productos</p>
                    </div>
                    <button onclick="logout()" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</button>
                </div>
            </div>
        </div>
        
        <div class="card shadow glass-effect mb-4">
            <div class="card-body" id="sellerInfo"></div>
        </div>
        
        <div class="card shadow glass-effect">
            <div class="card-body p-4">
                <h2 class="h4 mb-4">Subir Nuevo Producto</h2>
                <form id="productForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Nombre del producto *</label>
                            <input type="text" class="form-control" id="productName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productShortDesc" class="form-label">Descripción corta</label>
                            <input type="text" class="form-control" id="productShortDesc" name="short_description">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="productDesc" class="form-label">Descripción completa *</label>
                        <textarea class="form-control" id="productDesc" name="description" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productPrice" class="form-label">Precio (€) *</label>
                            <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productStock" class="form-label">Stock *</label>
                            <input type="number" class="form-control" id="productStock" name="stock" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imágenes del producto * (mínimo 1, máximo 10)</label>
                        <input type="file" class="form-control" id="productImages" name="images[]" accept="image/*" multiple required>
                        <div id="imagePreview" class="row g-2 mt-2"></div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="productFeatured" name="featured">
                        <label class="form-check-label" for="productFeatured">Producto destacado</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload"></i> Publicar Producto</button>
                </form>
                <div id="productMessage" class="alert mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
    
    <!-- Dashboard (Buyer) -->
    <div id="buyerDashboard" class="container d-none">
        <div class="card shadow-lg glass-effect mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 class="h3 mb-1">Catálogo de Productos</h1>
                        <p class="text-muted mb-0">Encuentra lo que necesitas</p>
                    </div>
                    <button onclick="logout()" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</button>
                </div>
            </div>
        </div>
        
        <div class="card shadow glass-effect mb-4">
            <div class="card-body" id="buyerInfo"></div>
        </div>
        
        <div class="card shadow glass-effect mb-4">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchProducts" placeholder="Buscar productos...">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="sortProducts">
                            <option value="creation_date">Más recientes</option>
                            <option value="price">Precio (bajo a alto)</option>
                            <option value="name">Nombre A-Z</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button onclick="loadProducts()" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="productsContainer" class="row g-3"></div>
    </div>
    
    <!-- Dashboard (Admin) -->
    <div id="adminDashboard" class="container d-none">
        <div class="card shadow-lg glass-effect mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1 class="h3 mb-1">Panel de Administración</h1>
                        <p class="text-muted mb-0">Gestión completa del sistema</p>
                    </div>
                    <button onclick="logout()" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</button>
                </div>
            </div>
        </div>
        
        <div class="card shadow glass-effect mb-4">
            <div class="card-body" id="adminInfo"></div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <h3 class="h5 text-primary mb-3"><i class="bi bi-file-earmark-bar-graph"></i> Reportes de Facturación</h3>
                        <div class="mb-2">
                            <label class="form-label">Fecha inicio</label>
                            <input type="date" class="form-control" id="billingStartDate">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha fin</label>
                            <input type="date" class="form-control" id="billingEndDate">
                        </div>
                        <button onclick="downloadBillingReport()" class="btn btn-success w-100">
                            <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <h3 class="h5 text-success mb-3"><i class="bi bi-graph-up"></i> Reporte de Ventas</h3>
                        <p class="text-muted">Productos vendidos y comisiones</p>
                        <button onclick="downloadSalesReport()" class="btn btn-success w-100 mt-auto">
                            <i class="bi bi-file-earmark-excel"></i> Descargar Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-warning">
                    <div class="card-body">
                        <h3 class="h5 text-warning mb-3"><i class="bi bi-box-seam"></i> Ver Productos</h3>
                        <p class="text-muted">Todos los productos del sistema</p>
                        <button onclick="viewAllProducts()" class="btn btn-warning w-100 mt-auto">
                            <i class="bi bi-eye"></i> Ver Catálogo
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="adminProductsContainer" class="row g-3 d-none"></div>
    </div>
    
    <!-- Bootstrap Modal Universal -->
    <div class="modal fade" id="customModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" id="modalDialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Título</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">Mensaje</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentUser = null;
        let selectedImages = [];
        let modalInstance = null;
        
        // Initialize modal
        document.addEventListener('DOMContentLoaded', () => {
            modalInstance = new bootstrap.Modal(document.getElementById('customModal'));
            checkSession();
        });
        
        // Modal functions
        function showModal(title, message, type = 'error') {
            const modalDialog = document.getElementById('modalDialog');
            const modalBody = document.getElementById('modalBody');
            
            // Reset to normal size
            modalDialog.className = 'modal-dialog modal-dialog-centered modal-dialog-scrollable';
            
            document.getElementById('modalTitle').textContent = title;
            modalBody.textContent = message;
            modalInstance.show();
        }
        
        function showPrivacyPolicy(event) {
            if (event) event.preventDefault();
            
            const modalDialog = document.getElementById('modalDialog');
            const modalBody = document.getElementById('modalBody');
            
            // Make modal larger for privacy policy
            modalDialog.className = 'modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl';
            
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-shield-lock"></i> Política de Privacidad';
            
            modalBody.innerHTML = `
                <p class="text-muted fst-italic small">Última actualización: 16 de noviembre de 2025</p>
                
                <div class="alert alert-primary">
                    <strong>E-commerce Platform</strong> se compromete a proteger su privacidad. Esta Política de Privacidad explica cómo recopilamos, usamos, divulgamos y salvaguardamos su información cuando visita nuestro sitio web y utiliza nuestros servicios.
                </div>

                <h5 class="mt-4 text-primary">1. Información que Recopilamos</h5>
                
                <h6 class="mt-3">1.1 Información Personal</h6>
                <p>Recopilamos la siguiente información personal que usted nos proporciona voluntariamente al registrarse en nuestra plataforma:</p>
                <ul>
                    <li><strong>Datos de identificación:</strong> Nombre, apellidos, correo electrónico</li>
                    <li><strong>Datos de contacto:</strong> Número de teléfono</li>
                    <li><strong>Datos de cuenta:</strong> Contraseña cifrada, tipo de cuenta (vendedor/comprador)</li>
                    <li><strong>Imagen de perfil:</strong> Fotografía opcional que usted sube voluntariamente</li>
                    <li><strong>Información de productos:</strong> Si es vendedor, datos de productos que publica</li>
                    <li><strong>Historial de transacciones:</strong> Compras, ventas y pagos realizados</li>
                </ul>

                <h6 class="mt-3">1.2 Información Automática</h6>
                <p>Cuando visita nuestro sitio, recopilamos automáticamente cierta información sobre su dispositivo, incluyendo:</p>
                <ul>
                    <li>Dirección IP</li>
                    <li>Tipo de navegador</li>
                    <li>Sistema operativo</li>
                    <li>Páginas visitadas y tiempo de permanencia</li>
                    <li>Cookies y tecnologías similares</li>
                </ul>

                <h5 class="mt-4 text-primary">2. Cómo Utilizamos su Información</h5>
                <p>Utilizamos la información recopilada para los siguientes propósitos:</p>
                <ul>
                    <li><strong>Prestación de servicios:</strong> Crear y gestionar su cuenta, procesar transacciones</li>
                    <li><strong>Comunicación:</strong> Enviar confirmaciones de pedidos, actualizaciones de cuenta, alertas de stock</li>
                    <li><strong>Mejora del servicio:</strong> Analizar el uso de la plataforma para mejorar la experiencia del usuario</li>
                    <li><strong>Seguridad:</strong> Prevenir fraudes y actividades maliciosas</li>
                    <li><strong>Marketing:</strong> Con su consentimiento, enviar ofertas y promociones relevantes</li>
                    <li><strong>Cumplimiento legal:</strong> Cumplir con obligaciones legales y regulatorias</li>
                </ul>

                <h5 class="mt-4 text-primary">3. Base Legal para el Tratamiento (RGPD)</h5>
                <p>Procesamos su información personal bajo las siguientes bases legales:</p>
                <ul>
                    <li><strong>Consentimiento:</strong> Usted ha dado su consentimiento explícito al registrarse</li>
                    <li><strong>Ejecución de contrato:</strong> Necesario para proporcionar los servicios solicitados</li>
                    <li><strong>Interés legítimo:</strong> Para mejorar nuestros servicios y prevenir fraudes</li>
                    <li><strong>Obligación legal:</strong> Cumplimiento de requisitos legales y fiscales</li>
                </ul>

                <h5 class="mt-4 text-primary">4. Compartir Información con Terceros</h5>
                <p>No vendemos su información personal. Podemos compartir su información únicamente en las siguientes circunstancias:</p>
                
                <h6 class="mt-3">4.1 Proveedores de Servicios</h6>
                <ul>
                    <li>Procesadores de pagos para gestionar transacciones</li>
                    <li>Servicios de hosting y almacenamiento de datos</li>
                    <li>Servicios de email para comunicaciones</li>
                </ul>

                <h6 class="mt-3">4.2 Entre Usuarios</h6>
                <ul>
                    <li>Compradores pueden ver información pública de vendedores (nombre de tienda, productos)</li>
                    <li>Vendedores reciben información de contacto de compradores para procesar pedidos</li>
                </ul>

                <h6 class="mt-3">4.3 Requisitos Legales</h6>
                <ul>
                    <li>Cuando sea requerido por ley o autoridades competentes</li>
                    <li>Para proteger nuestros derechos legales o los de terceros</li>
                    <li>Para prevenir fraudes o actividades ilegales</li>
                </ul>

                <h5 class="mt-4 text-primary">5. Seguridad de los Datos</h5>
                <p>Implementamos medidas de seguridad técnicas y organizativas para proteger su información:</p>
                <ul>
                    <li>Cifrado SSL/TLS para transmisión de datos</li>
                    <li>Contraseñas hasheadas con algoritmos seguros (bcrypt)</li>
                    <li>Acceso restringido a datos personales solo para personal autorizado</li>
                    <li>Copias de seguridad regulares</li>
                    <li>Monitoreo de seguridad y auditorías periódicas</li>
                </ul>
                
                <div class="alert alert-warning">
                    <strong>Importante:</strong> Ningún método de transmisión por Internet o almacenamiento electrónico es 100% seguro. Aunque nos esforzamos por proteger su información, no podemos garantizar su seguridad absoluta.
                </div>

                <h5 class="mt-4 text-primary">6. Retención de Datos</h5>
                <p>Conservamos su información personal durante el tiempo que sea necesario para:</p>
                <ul>
                    <li>Proporcionar nuestros servicios mientras mantenga una cuenta activa</li>
                    <li>Cumplir con obligaciones legales (por ejemplo, registros fiscales durante 7 años)</li>
                    <li>Resolver disputas y hacer cumplir nuestros acuerdos</li>
                </ul>
                <p>Cuando elimine su cuenta, anonimizaremos o eliminaremos su información personal, excepto la que debamos conservar por requisitos legales.</p>

                <h5 class="mt-4 text-primary">7. Sus Derechos (RGPD y LOPDGDD)</h5>
                <p>De acuerdo con el Reglamento General de Protección de Datos (RGPD) y la Ley Orgánica de Protección de Datos (LOPDGDD), usted tiene los siguientes derechos:</p>
                <ul>
                    <li><strong>Derecho de acceso:</strong> Solicitar una copia de sus datos personales</li>
                    <li><strong>Derecho de rectificación:</strong> Corregir datos inexactos o incompletos</li>
                    <li><strong>Derecho de supresión:</strong> Solicitar la eliminación de sus datos ("derecho al olvido")</li>
                    <li><strong>Derecho de limitación:</strong> Restringir el procesamiento de sus datos</li>
                    <li><strong>Derecho de portabilidad:</strong> Recibir sus datos en formato estructurado</li>
                    <li><strong>Derecho de oposición:</strong> Oponerse al procesamiento de sus datos</li>
                    <li><strong>Derecho a retirar el consentimiento:</strong> En cualquier momento sin afectar la legalidad del tratamiento previo</li>
                    <li><strong>Derecho a presentar una reclamación:</strong> Ante la Agencia Española de Protección de Datos (AEPD)</li>
                </ul>
                
                <p>Para ejercer cualquiera de estos derechos, contáctenos en: <a href="mailto:privacy@ecommerce-platform.com">privacy@ecommerce-platform.com</a></p>

                <h5 class="mt-4 text-primary">8. Cookies y Tecnologías de Seguimiento</h5>
                <p>Utilizamos cookies y tecnologías similares para:</p>
                <ul>
                    <li><strong>Cookies esenciales:</strong> Necesarias para el funcionamiento del sitio (sesiones de usuario)</li>
                    <li><strong>Cookies de funcionalidad:</strong> Recordar preferencias y configuraciones</li>
                    <li><strong>Cookies analíticas:</strong> Comprender cómo los usuarios interactúan con el sitio</li>
                </ul>
                <p>Puede configurar su navegador para rechazar cookies, aunque esto puede afectar la funcionalidad del sitio.</p>

                <h5 class="mt-4 text-primary">9. Transferencias Internacionales de Datos</h5>
                <p>Sus datos se almacenan y procesan en servidores ubicados en la Unión Europea. Si transferimos datos fuera de la UE, garantizamos que:</p>
                <ul>
                    <li>El país de destino tiene un nivel adecuado de protección reconocido por la Comisión Europea</li>
                    <li>Se implementan cláusulas contractuales tipo aprobadas por la UE</li>
                    <li>Se aplican otras salvaguardas apropiadas según el RGPD</li>
                </ul>

                <h5 class="mt-4 text-primary">10. Privacidad de Menores</h5>
                <p>Nuestros servicios no están dirigidos a menores de 18 años. No recopilamos intencionadamente información de menores. Si descubrimos que hemos recopilado información de un menor sin consentimiento parental, eliminaremos esa información inmediatamente.</p>

                <h5 class="mt-4 text-primary">11. Cambios en esta Política</h5>
                <p>Podemos actualizar esta Política de Privacidad periódicamente. Notificaremos cualquier cambio significativo mediante:</p>
                <ul>
                    <li>Publicación de la política actualizada en este sitio con una nueva fecha de "última actualización"</li>
                    <li>Envío de un correo electrónico a los usuarios registrados</li>
                    <li>Notificación prominente en nuestro sitio web</li>
                </ul>
                <p>Le recomendamos revisar esta política periódicamente para estar informado sobre cómo protegemos su información.</p>

                <h5 class="mt-4 text-primary">12. Contacto</h5>
                <p>Si tiene preguntas, inquietudes o desea ejercer sus derechos sobre esta Política de Privacidad o el tratamiento de sus datos personales, puede contactarnos:</p>
                
                <div class="alert alert-info">
                    <p class="mb-2"><strong>Responsable del Tratamiento:</strong><br>
                    E-commerce Platform<br>
                    Dirección: Calle Principal, 123, 28001 Madrid, España<br>
                    Email: <a href="mailto:privacy@ecommerce-platform.com">privacy@ecommerce-platform.com</a><br>
                    Teléfono: +34 900 123 456</p>
                    
                    <p class="mb-0"><strong>Delegado de Protección de Datos (DPO):</strong><br>
                    Email: <a href="mailto:dpo@ecommerce-platform.com">dpo@ecommerce-platform.com</a></p>
                </div>

                <h5 class="mt-4 text-primary">13. Autoridad de Supervisión</h5>
                <p>Tiene derecho a presentar una reclamación ante la autoridad de protección de datos competente:</p>
                <div class="alert alert-secondary">
                    <p class="mb-0"><strong>Agencia Española de Protección de Datos (AEPD)</strong><br>
                    Calle Jorge Juan, 6, 28001 Madrid<br>
                    Web: <a href="https://www.aepd.es" target="_blank">www.aepd.es</a><br>
                    Teléfono: +34 901 100 099</p>
                </div>

                <hr class="my-4">
                
                <p class="text-center text-muted fst-italic">
                    Al utilizar E-commerce Platform, usted acepta los términos de esta Política de Privacidad.
                </p>
            `;
            
            modalInstance.show();
        }
        
        function closeModal() {
            modalInstance.hide();
        }
        
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
            document.getElementById('loginContainer').classList.remove('d-none');
            document.getElementById('registerContainer').classList.add('d-none');
            hideAllDashboards();
        }
        
        function showRegister() {
            document.getElementById('loginContainer').classList.add('d-none');
            document.getElementById('registerContainer').classList.remove('d-none');
            hideAllDashboards();
        }
        
        function hideAllDashboards() {
            document.getElementById('sellerDashboard').classList.add('d-none');
            document.getElementById('buyerDashboard').classList.add('d-none');
            document.getElementById('adminDashboard').classList.add('d-none');
        }
        
        function showDashboard() {
            document.getElementById('loginContainer').classList.add('d-none');
            document.getElementById('registerContainer').classList.add('d-none');
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
            const isPremium = currentUser.role === 'seller_premium';
            const badgeClass = isPremium ? 'badge bg-warning' : 'badge bg-info';
            const upgradeBtn = isPremium ? '' : `<button onclick="upgradeToPremium()" class="btn btn-success btn-sm mt-2"><i class="bi bi-star-fill"></i> Actualizar a Premium</button>`;
            document.getElementById('sellerInfo').innerHTML = `
                <h3 class="h5">Bienvenido, ${currentUser.name}</h3>
                <p class="mb-1"><strong>Email:</strong> ${currentUser.email}</p>
                <p class="mb-2"><strong>Rol:</strong> <span class="${badgeClass}">${currentUser.role}</span></p>
                ${upgradeBtn}
            `;
        }
        
        function showBuyerDashboard() {
            document.getElementById('buyerDashboard').classList.remove('d-none');
            const isPremium = currentUser.role === 'buyer_premium';
            const badgeClass = isPremium ? 'badge bg-warning' : 'badge bg-info';
            const upgradeBtn = isPremium ? '' : `<button onclick="upgradeToPremium()" class="btn btn-success btn-sm mt-2"><i class="bi bi-star-fill"></i> Actualizar a Premium</button>`;
            document.getElementById('buyerInfo').innerHTML = `
                <h3 class="h5">Bienvenido, ${currentUser.name}</h3>
                <p class="mb-1"><strong>Email:</strong> ${currentUser.email}</p>
                <p class="mb-2"><strong>Rol:</strong> <span class="${badgeClass}">${currentUser.role}</span></p>
                ${upgradeBtn}
            `;
            loadProducts();
        }
        
        function showAdminDashboard() {
            document.getElementById('adminDashboard').classList.remove('d-none');
            document.getElementById('adminInfo').innerHTML = `
                <h3 class="h5">Bienvenido, ${currentUser.name}</h3>
                <p class="mb-1"><strong>Email:</strong> ${currentUser.email}</p>
                <p class="mb-0"><strong>Rol:</strong> <span class="badge bg-danger">${currentUser.role}</span></p>
            `;
        }
        
        document.getElementById('showRegisterLink').addEventListener('click', (e) => { e.preventDefault(); showRegister(); });
        document.getElementById('showLoginLink').addEventListener('click', (e) => { e.preventDefault(); showLogin(); });
        
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
                    showMessage('loginMessage', result.message, 'danger');
                }
            } catch (error) {
                showMessage('loginMessage', 'Error al conectar con el servidor', 'danger');
            }
        });
        
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
                    // Don't set Content-Type header - browser will set it with boundary for multipart/form-data
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
        
        document.getElementById('productImages').addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            selectedImages = files;
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';
            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'col-4 col-md-3 col-lg-2';
                    div.innerHTML = `
                        <div class="position-relative border rounded overflow-hidden">
                            <img src="${e.target.result}" alt="Preview ${index + 1}" class="img-fluid" style="height: 120px; object-fit: cover; width: 100%;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="removeImage(${index})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    `;
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
                    showMessage('productMessage', errors, 'danger');
                }
            } catch (error) {
                showMessage('productMessage', 'Error al publicar el producto', 'danger');
            }
        });
        
        async function loadProducts() {
            const search = document.getElementById('searchProducts')?.value || '';
            const sort = document.getElementById('sortProducts')?.value || 'creation_date';
            const container = document.getElementById('productsContainer');
            container.innerHTML = '<div class="col-12"><div class="alert alert-info">Cargando productos...</div></div>';
            try {
                const url = `/api/controllers/products/list.php?search=${encodeURIComponent(search)}&sort_by=${sort}&limit=20`;
                const response = await fetch(url);
                const result = await response.json();
                if (result.success && result.data.products.length > 0) {
                    container.innerHTML = result.data.products.map(product => `
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 shadow-sm">
                                <img src="/api/${product.primary_image || 'assets/no-image.jpg'}" class="card-img-top" alt="${product.name}" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">${product.name}</h5>
                                    <p class="h4 text-primary mb-2">€${parseFloat(product.price).toFixed(2)}</p>
                                    <p class="text-muted mb-2"><small>Stock: ${product.stock} unidades</small></p>
                                    <p class="card-text text-muted small">${product.short_description || ''}</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div class="col-12"><div class="alert alert-warning">No se encontraron productos</div></div>';
                }
            } catch (error) {
                container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error al cargar productos</div></div>';
            }
        }
        
        async function downloadBillingReport() {
            const startDate = document.getElementById('billingStartDate').value;
            const endDate = document.getElementById('billingEndDate').value;
            if (!startDate || !endDate) {
                showModal('Fechas requeridas', 'Por favor selecciona ambas fechas', 'warning');
                return;
            }
            window.location.href = `/api/controllers/reports/billing.php?start_date=${startDate}&end_date=${endDate}&format=excel`;
        }
        
        function downloadSalesReport() {
            window.location.href = '/api/controllers/reports/sales.php?format=excel';
        }
        
        async function viewAllProducts() {
            const container = document.getElementById('adminProductsContainer');
            container.classList.remove('d-none');
            container.innerHTML = '<div class="col-12"><div class="alert alert-info">Cargando productos...</div></div>';
            try {
                const response = await fetch('/api/controllers/products/list.php?limit=100');
                const result = await response.json();
                if (result.success && result.data.products.length > 0) {
                    container.innerHTML = result.data.products.map(product => `
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 shadow-sm">
                                <img src="/api/${product.primary_image || 'assets/no-image.jpg'}" class="card-img-top" alt="${product.name}" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">${product.name}</h5>
                                    <p class="h4 text-primary mb-2">€${parseFloat(product.price).toFixed(2)}</p>
                                    <p class="text-muted mb-2"><small>Stock: ${product.stock}</small></p>
                                    <p class="text-muted small">Vendedor: ${product.seller_name}</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<div class="col-12"><div class="alert alert-warning">No hay productos</div></div>';
                }
            } catch (error) {
                container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error al cargar productos</div></div>';
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
                    currentUser.role = result.data.user.role;
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
            messageDiv.className = `alert alert-${type}`;
            messageDiv.style.display = 'block';
            setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
        }
    </script>
</body>
</html>
