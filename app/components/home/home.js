// Home Component - Product Catalog

let currentPage = 1;
const productsPerPage = 6; // 3 columnas x 2 filas

async function loadHomeProducts(page = 1) {
    currentPage = page;
    const grid = document.getElementById('productsGrid');
    
    grid.innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando productos...</span>
            </div>
        </div>
    `;
    
    try {
        const offset = (page - 1) * productsPerPage;
        const response = await fetch(`/api/controllers/products/list.php?limit=${productsPerPage}&offset=${offset}`, {
            credentials: 'include'
        });
        const result = await response.json();
        
        if (result.success && result.data.products.length > 0) {
            renderProducts(result.data.products);
            renderPagination(result.data.total, page);
        } else {
            grid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> No hay productos disponibles en este momento.
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading products:', error);
        grid.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle"></i> Error al cargar productos
                </div>
            </div>
        `;
    }
}

function renderProducts(products) {
    const grid = document.getElementById('productsGrid');
    
    grid.innerHTML = products.map(product => `
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card product-card h-100 shadow-sm">
                <img src="/api/${product.primary_image || 'assets/no-image.jpg'}" 
                     class="card-img-top product-image" 
                     alt="${product.name}"
                     onerror="this.src='/api/assets/no-image.jpg'">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title product-title">${product.name}</h5>
                    <p class="card-text text-muted small flex-grow-1">${product.short_description || ''}</p>
                    <div class="product-footer mt-auto">
                        <p class="product-price mb-2">€${parseFloat(product.price).toFixed(2)}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-box"></i> Stock: ${product.stock}
                            </small>
                            ${product.featured ? '<span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Destacado</span>' : ''}
                        </div>
                        <button class="btn btn-primary w-100 mt-2" onclick="viewProductDetails(${product.id})">
                            <i class="bi bi-eye"></i> Ver detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function renderPagination(total, currentPage) {
    const totalPages = Math.ceil(total / productsPerPage);
    const pagination = document.getElementById('productsPagination');
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadHomeProducts(${currentPage - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadHomeProducts(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadHomeProducts(${currentPage + 1}); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    pagination.innerHTML = html;
}

function viewProductDetails(productId) {
    showModal('Próximamente', 'La vista de detalles del producto estará disponible próximamente.', 'info');
}

function showHome() {
    // Hide all other sections
    document.getElementById('loginContainer')?.classList.add('d-none');
    document.getElementById('registerContainer')?.classList.add('d-none');
    document.getElementById('sellerDashboard')?.classList.add('d-none');
    document.getElementById('buyerDashboard')?.classList.add('d-none');
    document.getElementById('adminDashboard')?.classList.add('d-none');
    
    // Show home
    document.getElementById('homeContainer')?.classList.remove('d-none');
    
    // Load products
    loadHomeProducts(1);
}
