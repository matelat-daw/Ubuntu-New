// Admin Component

function showAdminDashboard() {
    document.getElementById('adminDashboard').classList.remove('d-none');
    document.getElementById('adminInfo').innerHTML = `
        <h3 class="h5">Bienvenido, ${currentUser.name}</h3>
        <p class="mb-1"><strong>Email:</strong> ${currentUser.email}</p>
        <p class="mb-0"><strong>Rol:</strong> <span class="badge bg-danger">${currentUser.role}</span></p>
    `;
}

function downloadBillingReport() {
    showModal('Próximamente', 'Esta funcionalidad estará disponible próximamente.', 'info');
}

function downloadSalesReport() {
    showModal('Próximamente', 'Esta funcionalidad estará disponible próximamente.', 'info');
}

function viewAllProducts() {
    showModal('Próximamente', 'Esta funcionalidad estará disponible próximamente.', 'info');
}
