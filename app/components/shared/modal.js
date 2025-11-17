// Modal Functions

let modalInstance = null;

// Initialize modal
document.addEventListener('DOMContentLoaded', () => {
    modalInstance = new bootstrap.Modal(document.getElementById('customModal'));
});

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
