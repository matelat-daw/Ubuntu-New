// App principal - Energy
(function() {
    'use strict';
    
    var App = function() {
        this.currentPage = null;
        this.basePath = '/Energy/frontend/';
        this.initialized = false;
    };
    
    App.prototype.init = function() {
        if (this.initialized) return;
        this.initialized = true;
        
        var self = this;
        
        // Cargar componentes estructurales inmediatamente
        this.loadComponent('header');
        this.loadComponent('footer');
        
        // Cargar página inicial desde la URL
        var initialRoute = this.getInitialRoute();
        this.loadPageDirect(initialRoute);
        
        this.setupRouting();
    };
    
    // Obtener ruta inicial desde el hash de la URL
    App.prototype.getInitialRoute = function() {
        var hash = window.location.hash;
        
        if (!hash || hash === '#' || hash === '#home') {
            // Asegurar que la URL inicial sea correcta
            window.history.replaceState({}, '', '/Energy/#home');
            return 'home';
        }
        
        // Extraer nombre de la ruta (ej: #activate?token=... -> activate)
        var route = hash.substring(1); // Quitar el #
        var questionMarkIndex = route.indexOf('?');
        if (questionMarkIndex > 0) {
            route = route.substring(0, questionMarkIndex);
        }
        
        return route || 'home';
    };
    
    App.prototype.loadComponent = function(name) {
        if (window[name + 'Component'] && window[name + 'Component'].init) {
            window[name + 'Component'].init();
        }
    };
    
    App.prototype.setupRouting = function() {
        var self = this;
        
        // Listener para hashchange (navegación manual en URL)
        window.addEventListener('hashchange', function() {
            var hashContent = window.location.hash.substring(1) || 'home';
            var page = hashContent.split('?')[0] || 'home';
            self.loadPageDirect(page);
        });
        
        // Listener para botón atrás/adelante del navegador
        window.addEventListener('popstate', function(e) {
            var hashContent = window.location.hash.substring(1) || 'home';
            var page = hashContent.split('?')[0] || 'home';
            self.loadPageDirect(page);
        });
        
        // Listener para enlaces con data-route
        document.addEventListener('click', function(e) {
            var target = e.target;
            while (target && target !== document) {
                if (target.hasAttribute && target.hasAttribute('data-route')) {
                    e.preventDefault();
                    var route = target.getAttribute('data-route');
                    self.navigate(route);
                    return;
                }
                target = target.parentNode;
            }
        });
    };
    
    // Método público para navegar programáticamente
    App.prototype.navigate = function(route) {
        if (this.currentPage === route) return;
        this.loadPageDirect(route);
        window.history.pushState({}, '', '/Energy/#' + route);
    };
    
    // Cargar página sin actualizar URL (usado internamente)
    App.prototype.loadPageDirect = function(pageName) {
        var mainContent = document.getElementById('main-content');
        if (!mainContent) return;
        
        this.currentPage = pageName;

        // ---- SEO: datos por página ----
        var BASE_URL = 'https://www.energyapp.es';
        var seoData = {
            'home': {
                title: 'Energy App – Compara Proveedores de Energía y Ahorra hasta un 30% en tu Factura de Luz',
                description: 'Compara los mejores proveedores de electricidad en España y encuentra la tarifa más barata para tu hogar o negocio. Calcula tu ahorro en segundos, sin compromiso y con energía 100% renovable.',
                canonical: BASE_URL + '/'
            },
            'calculator': {
                title: 'Calculadora de Ahorro Energético – Descubre Cuánto Puedes Ahorrar | Energy App',
                description: 'Introduce tu consumo actual y descubre cuánto puedes ahorrar cambiando de proveedor de electricidad. Cálculo gratuito, inmediato y sin compromiso.',
                canonical: BASE_URL + '/#calculator'
            },
            'results': {
                title: 'Resultados de tu Ahorro en Electricidad | Energy App',
                description: 'Consulta tu ahorro potencial con Naturgy. Hasta un 30% de descuento en tu factura de luz con energía 100% renovable y sin permanencia.',
                canonical: BASE_URL + '/#results'
            },
            'about': {
                title: 'Acerca de Energy App – Nuestra Misión y Servicios | Energy App',
                description: 'Conoce Energy App: la plataforma española que ayuda a hogares y negocios a comparar tarifas eléctricas y reducir su consumo energético con transparencia y sin letra pequeña.',
                canonical: BASE_URL + '/#about'
            },
            'contact': {
                title: 'Contacto – Habla con Nuestro Equipo | Energy App',
                description: 'Ponte en contacto con Energy App. Atención personalizada de lunes a viernes de 9:00 a 18:00. Llámanos al +34 900 123 456 o escríbenos por el formulario.',
                canonical: BASE_URL + '/#contact'
            },
            'privacy': {
                title: 'Política de Privacidad | Energy App',
                description: 'Consulta nuestra política de privacidad. Energy App cumple con el RGPD y garantiza la protección de tus datos personales en todo momento.',
                canonical: BASE_URL + '/#privacy'
            },
            'login': {
                title: 'Iniciar Sesión en tu Cuenta | Energy App',
                description: 'Accede a tu cuenta de Energy App para gestionar tu contrato, consultar tu ahorro y administrar tu perfil energético.',
                canonical: BASE_URL + '/#login'
            },
            'register': {
                title: 'Crear Cuenta Gratuita | Energy App',
                description: 'Regístrate gratis en Energy App y empieza a ahorrar en tu factura de electricidad. Solo necesitas tu email para comenzar.',
                canonical: BASE_URL + '/#register'
            },
            'profile': {
                title: 'Mi Perfil | Energy App',
                description: 'Gestiona tu cuenta de Energy App: actualiza tus datos, consulta tu contrato actual y controla tu ahorro energético.',
                canonical: BASE_URL + '/#profile'
            },
            'activate': {
                title: 'Activar Cuenta | Energy App',
                description: 'Activa tu cuenta de Energy App para empezar a comparar tarifas de electricidad y ahorrar en tu factura de luz.',
                canonical: BASE_URL + '/#activate'
            }
        };

        var seo = seoData[pageName] || {
            title: pageName.charAt(0).toUpperCase() + pageName.slice(1) + ' | Energy App',
            description: 'Energy App – Compara proveedores de energía eléctrica en España y ahorra hasta un 30% en tu factura de luz.',
            canonical: BASE_URL + '/#' + pageName
        };

        // Actualizar <title>
        document.title = seo.title;

        // Actualizar <meta name="description">
        var metaDesc = document.querySelector('meta[name="description"]');
        if (metaDesc) metaDesc.setAttribute('content', seo.description);

        // Actualizar Open Graph
        var ogTitle = document.getElementById('og-title');
        var ogDesc = document.getElementById('og-description');
        var ogUrl = document.getElementById('og-url');
        if (ogTitle) ogTitle.setAttribute('content', seo.title);
        if (ogDesc) ogDesc.setAttribute('content', seo.description);
        if (ogUrl) ogUrl.setAttribute('content', seo.canonical);

        // Actualizar Twitter Card
        var twTitle = document.getElementById('tw-title');
        var twDesc = document.getElementById('tw-description');
        if (twTitle) twTitle.setAttribute('content', seo.title);
        if (twDesc) twDesc.setAttribute('content', seo.description);

        // Actualizar URL canónica
        var canonical = document.getElementById('canonical-url');
        if (canonical) canonical.setAttribute('href', seo.canonical);

        // Mostrar loading
        mainContent.innerHTML = '<div class="loading"><div class="spinner" role="status" aria-label="Cargando..."></div></div>';
        
        // Cargar HTML de la página
        var pageUrl = this.basePath + 'pages/' + pageName + '/' + pageName + '.html';
        
        fetch(pageUrl)
            .then(function(response) {
                if (!response.ok) throw new Error('Página no encontrada');
                return response.text();
            })
            .then(function(html) {
                mainContent.innerHTML = html;
                
                // Actualizar breadcrumb
                if (window.headerComponent && window.headerComponent.updateBreadcrumb) {
                    window.headerComponent.updateBreadcrumb();
                }
                
                // Cargar CSS de la página
                var cssUrl = '/Energy/frontend/pages/' + pageName + '/' + pageName + '.css';
                var existingLink = document.querySelector('link[href="' + cssUrl + '"]');
                if (!existingLink) {
                    var link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = cssUrl;
                    document.head.appendChild(link);
                }
                
                // Cargar JS de la página
                var scriptUrl = '/Energy/frontend/pages/' + pageName + '/' + pageName + '.js';
                var existingScript = document.querySelector('script[src="' + scriptUrl + '"]');
                if (!existingScript) {
                    var script = document.createElement('script');
                    script.src = scriptUrl;
                    script.onload = function() {
                        // Inicializar página si tiene método init
                        if (window[pageName + 'Page'] && window[pageName + 'Page'].init) {
                            window[pageName + 'Page'].init();
                        }
                    };
                    document.body.appendChild(script);
                } else {
                    // Si el script ya existe, solo inicializar
                    if (window[pageName + 'Page'] && window[pageName + 'Page'].init) {
                        window[pageName + 'Page'].init();
                    }
                }
            })
            .catch(function(error) {
                mainContent.innerHTML = 
                    '<div class="card" style="max-width: 600px; margin: 2rem auto; text-align: center;">' +
                    '<h2><span aria-hidden="true">⚠️</span> Página no encontrada</h2>' +
                    '<p>La página "' + pageName + '" no existe.</p>' +
                    '<button type="button" class="btn btn-primary" onclick="app.navigate(\'home\')">Ir al inicio</button>' +
                    '</div>';
            });
    };
    
    // Cargar página y actualizar URL (para compatibilidad con código existente)
    App.prototype.loadPage = function(pageName) {
        this.navigate(pageName);
    };
    
    // Inicializar app cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            window.app = new App();
            window.app.init();
        });
    } else {
        window.app = new App();
        window.app.init();
    }
})();
