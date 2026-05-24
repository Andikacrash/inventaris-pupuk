/**
 * POS Products — grid/list, filter realtime, detail expand in-card.
 */
class ProductsGrid {
    constructor() {
        this.products = [];
        this.currentPage = 1;
        this.lastPage = 1;
        this.loading = false;
        /** @deprecated gunakan currentCategoryIds + posGroup */
        this.selectedCategoryId = '';
        /** Tab kasir: all | pupuk | alat | herbisida | pestisida */
        this.posGroup = 'all';
        /** ID kategori yang dikirim ke API (kosong = semua) */
        this.currentCategoryIds = [];
        this.categoryIdsByGroup = { pupuk: [], alat: [], herbisida: [], pestisida: [] };
        this.groupProductCounts = { all: 0, pupuk: 0, alat: 0, herbisida: 0, pestisida: 0 };
        this.currentSearchQuery = '';
        this.viewMode = 'grid';
        this.expandedProductId = null;
        this.panelQty = {};
        this._searchTimer = null;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadCategories();
    }

    static assignPosGroup(category) {
        const n = String(category?.name ?? '')
            .trim()
            .toLowerCase();
        if (!n) return null;
        if (n.includes('herbisida')) return 'herbisida';
        if (n.includes('pestisida')) return 'pestisida';
        if (n.includes('pupuk')) return 'pupuk';
        return null;
    }

    setupEventListeners() {
        const searchInput = document.getElementById('product-search');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(this._searchTimer);
                this._searchTimer = setTimeout(() => this.applyFilters(), 120);
            });
        }

        document.getElementById('btn-view-grid')?.addEventListener('click', () => this.setViewMode('grid'));
        document.getElementById('btn-view-list')?.addEventListener('click', () => this.setViewMode('list'));

        document.querySelectorAll('#pos-category-tabs .pos-cat-tab').forEach((btn) => {
            btn.addEventListener('click', () => {
                const g = btn.getAttribute('data-pos-group') || 'all';
                this.setPosGroup(g);
            });
        });
    }

    setPosGroup(group) {
        const g =
            group === 'pupuk' || group === 'herbisida' || group === 'pestisida' ? group : 'all';
        this.posGroup = g;
        this.selectedCategoryId = '';

        if (g === 'all') {
            this.currentCategoryIds = [];
        } else {
            this.currentCategoryIds = (this.categoryIdsByGroup[g] || []).slice();
        }

        document.querySelectorAll('#pos-category-tabs .pos-cat-tab').forEach((btn) => {
            const active = (btn.getAttribute('data-pos-group') || 'all') === g;
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        this.currentPage = 1;
        this.applyFilters();
    }

    setViewMode(mode) {
        this.viewMode = mode === 'list' ? 'list' : 'grid';
        const grid = document.getElementById('products-grid');
        const btnG = document.getElementById('btn-view-grid');
        const btnL = document.getElementById('btn-view-list');
        btnG?.classList.toggle('active', this.viewMode === 'grid');
        btnL?.classList.toggle('active', this.viewMode === 'list');
        if (btnG) btnG.setAttribute('aria-pressed', this.viewMode === 'grid' ? 'true' : 'false');
        if (btnL) btnL.setAttribute('aria-pressed', this.viewMode === 'list' ? 'true' : 'false');
        if (grid) {
            grid.classList.toggle('pos-products-view--grid', this.viewMode === 'grid');
            grid.classList.toggle('pos-products-view--list', this.viewMode === 'list');
        }
        this.expandedProductId = null;
        this.renderProducts();
    }

    getSearchTrimmed() {
        const el = document.getElementById('product-search');
        return (el?.value || '').trim();
    }

    applyFilters() {
        this.currentSearchQuery = this.getSearchTrimmed();
        this.currentPage = 1;
        this.loadProducts(1);
    }

    buildListUrl(page) {
        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', this.currentSearchQuery ? '500' : '60');
        if (this.currentCategoryIds && this.currentCategoryIds.length > 0) {
            params.set('category_ids', this.currentCategoryIds.join(','));
        } else if (this.posGroup !== 'all') {
            params.set('category_ids', '__none__');
        } else if (this.selectedCategoryId) {
            params.set('category_id', this.selectedCategoryId);
        }
        if (this.currentSearchQuery) {
            params.set('search', this.currentSearchQuery);
        }
        return `/api/products?${params.toString()}`;
    }

    async loadProducts(page = 1) {
        if (this.loading) return;

        this.loading = true;
        const grid = document.getElementById('products-grid');

        if (page === 1) {
            if (grid) {
                grid.innerHTML = '<div class="loading-products">Memuat produk...</div>';
            }
            this.products = [];
        }

        try {
            const response = await fetch(this.buildListUrl(page), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();

            if (data.data && data.data.length > 0) {
                if (page === 1) {
                    this.products = data.data;
                } else {
                    this.products = [...this.products, ...data.data];
                }
                const meta = data.meta || {};
                this.currentPage = meta.current_page ?? page;
                this.lastPage = meta.last_page ?? 1;
                this.renderProducts();
                this.updateLoadMoreButton();
            } else if (page === 1) {
                if (grid) {
                    grid.innerHTML = '<div class="loading-products">Tidak ada produk</div>';
                }
                this.updateFilterCount(0);
            }
        } catch (error) {
            console.error('Error loading products:', error);
            if (grid) {
                grid.innerHTML = '<div class="loading-products">Error memuat produk</div>';
            }
        } finally {
            this.loading = false;
        }
    }

    updateFilterCount(n) {
        const el = document.getElementById('products-filter-count');
        if (el) {
            el.textContent = `${n} produk ditemukan`;
        }
    }

    formatStockBadge(n) {
        const s = Number(n) || 0;
        if (s >= 1000) {
            return `${(s / 1000).toFixed(1).replace('.', ',')}rb`;
        }
        return String(s);
    }

    stockBadgeClass(stock) {
        const s = Number(stock) || 0;
        if (s > 10) return 'pos-pc-stock-badge--ok';
        if (s >= 5) return 'pos-pc-stock-badge--amber';
        return 'pos-pc-stock-badge--crit';
    }

    detailStockValueClass(stock) {
        const s = Number(stock) || 0;
        if (s > 10) return '';
        if (s >= 5) return ' pos-pc-detail-val--amber';
        return ' pos-pc-detail-val--crit';
    }

    listStockClass(stock) {
        const s = Number(stock) || 0;
        if (s > 10) return 'pos-pr-stock--ok';
        if (s >= 5) return 'pos-pr-stock--amber';
        return 'pos-pr-stock--crit';
    }

    formatPriceRounded(price) {
        const n = Math.round(Number(price) || 0);
        return `Rp ${n.toLocaleString('id-ID')}`;
    }

    escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, (m) => map[m]);
    }

    renderProducts() {
        const grid = document.getElementById('products-grid');
        if (!grid) return;

        if (this.products.length === 0) {
            grid.innerHTML = '<div class="loading-products">Tidak ada produk</div>';
            this.updateFilterCount(0);
            return;
        }

        this.updateFilterCount(this.products.length);

        if (this.viewMode === 'list') {
            grid.innerHTML = this.products.map((p) => this.renderListRow(p)).join('');
        } else {
            grid.innerHTML = this.products.map((p) => this.renderGridCard(p)).join('');
        }
    }

    renderGridCard(product) {
        const id = product.id;
        const stock = parseInt(product.stock_quantity, 10) || 0;
        const imageUrl = product.image_url || null;
        const escapedName = this.escapeHtml(product.name);
        const categoryName = product.category ? this.escapeHtml(product.category.name) : '';
        const out = stock <= 0;

        let imageHtml = '';
        if (imageUrl && String(imageUrl).trim() !== '') {
            const eu = this.escapeHtml(imageUrl);
            imageHtml = `<img src="${eu}" alt="${escapedName}" class="pos-pc-image" onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('div'),{className:'pos-pc-placeholder',textContent:'📦'}));">`;
        } else {
            imageHtml = '<div class="pos-pc-placeholder">📦</div>';
        }

        const stockLbl = this.formatStockBadge(stock);
        const stockCls = this.stockBadgeClass(stock);
        const price = this.formatPriceRounded(product.price);
        const unit = product.unit ? this.escapeHtml(String(product.unit)) : '-';
        const catLine = categoryName ? `<p class="pos-pc-cat">${categoryName}</p>` : '';

        const expanded = this.expandedProductId === id;
        const detailOpen = expanded ? '' : ' hidden';
        const detailArrow = expanded ? '▴' : '▾';
        const qty = this.panelQty[id] != null ? this.panelQty[id] : 1;
        const stokRowClass = this.detailStockValueClass(stock);

        const overlay = out ? '<div class="pos-pc-overlay">Stok habis</div>' : '';

        return (
            `<article class="pos-product-card${out ? ' pos-product-card--out' : ''}" data-product-id="${id}">` +
            `<div class="pos-pc-image-wrap">${imageHtml}<span class="pos-pc-stock-badge ${stockCls}">${stockLbl}</span></div>` +
            `<div class="pos-pc-body">` +
            `<h3 class="pos-pc-name" title="${escapedName}">${escapedName}</h3>${catLine}` +
            `<p class="pos-pc-price">${price}</p></div>` +
            `<div class="pos-pc-footer">` +
            `<button type="button" class="pos-pc-detail-btn" onclick="productsGrid.toggleDetail(${id})" ${out ? 'disabled' : ''}>Detail ${detailArrow}</button>` +
            `<button type="button" class="pos-pc-add-btn" onclick="productsGrid.quickAdd(${id})" aria-label="Tambah ke keranjang" ${out ? 'disabled' : ''}>+</button>` +
            `</div>` +
            `<div class="pos-pc-detail-panel"${detailOpen}>` +
            `<div class="pos-pc-detail-row"><span class="pos-pc-detail-k">Satuan</span><span class="pos-pc-detail-v">${unit}</span></div>` +
            `<div class="pos-pc-detail-row"><span class="pos-pc-detail-k">Kategori</span><span class="pos-pc-detail-v">${categoryName || '—'}</span></div>` +
            `<div class="pos-pc-detail-row"><span class="pos-pc-detail-k">Stok tersisa</span><span class="pos-pc-detail-v${stokRowClass}">${stock}</span></div>` +
            `<div class="pos-pc-qty-row">` +
            `<button type="button" class="pos-pc-qty-btn" onclick="productsGrid.changePanelQty(${id},-1)" ${out ? 'disabled' : ''}>−</button>` +
            `<span class="pos-pc-qty-val" id="panel-qty-${id}">${qty}</span>` +
            `<button type="button" class="pos-pc-qty-btn" onclick="productsGrid.changePanelQty(${id},1)" ${out ? 'disabled' : ''}>+</button>` +
            `</div>` +
            `<button type="button" class="pos-pc-cart-full" id="panel-cart-btn-${id}" onclick="productsGrid.addFromPanel(${id})" ${out ? 'disabled' : ''}>+ Keranjang</button>` +
            `</div>${overlay}</article>`
        );
    }

    renderListRow(product) {
        const id = product.id;
        const stock = parseInt(product.stock_quantity, 10) || 0;
        const imageUrl = product.image_url || null;
        const escapedName = this.escapeHtml(product.name);
        const categoryName = product.category ? this.escapeHtml(product.category.name) : '';
        const out = stock <= 0;
        const stockCls = this.listStockClass(stock);
        const stockLbl = this.formatStockBadge(stock);

        let thumb = '';
        if (imageUrl && String(imageUrl).trim() !== '') {
            const eu = this.escapeHtml(imageUrl);
            thumb = `<img src="${eu}" alt="" class="pos-pr-thumb-img" onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('div'),{className:'pos-pr-thumb-ph',textContent:'📦'}));">`;
        } else {
            thumb = '<div class="pos-pr-thumb-ph">📦</div>';
        }

        const catLine = categoryName ? `<div class="pos-pr-cat">${categoryName}</div>` : '';

        return (
            `<div class="pos-product-row${out ? ' pos-product-row--out' : ''}" data-product-id="${id}">` +
            `<div class="pos-pr-thumb">${thumb}</div>` +
            `<div class="pos-pr-main"><div class="pos-pr-name" title="${escapedName}">${escapedName}</div>${catLine}</div>` +
            `<div class="pos-pr-right">` +
            `<span class="pos-pr-stock ${stockCls}">Stok: ${stockLbl}</span>` +
            `<span class="pos-pr-price">${this.formatPriceRounded(product.price)}</span>` +
            `<button type="button" class="pos-pc-add-btn pos-pc-add-btn--row" onclick="productsGrid.quickAdd(${id})" aria-label="Tambah" ${out ? 'disabled' : ''}>+</button>` +
            `</div></div>`
        );
    }

    toggleDetail(productId) {
        const product = this.products.find((p) => p.id === productId);
        if (!product || (parseInt(product.stock_quantity, 10) || 0) <= 0) return;

        if (this.expandedProductId === productId) {
            this.expandedProductId = null;
            this.panelQty[productId] = 1;
        } else {
            this.expandedProductId = productId;
            this.panelQty[productId] = 1;
        }
        this.renderProducts();
    }

    changePanelQty(productId, delta) {
        const product = this.products.find((p) => p.id === productId);
        if (!product) return;
        const max = parseInt(product.stock_quantity, 10) || 0;
        let q = this.panelQty[productId] != null ? this.panelQty[productId] : 1;
        q = Math.max(1, Math.min(q + delta, max));
        this.panelQty[productId] = q;
        const el = document.getElementById(`panel-qty-${productId}`);
        if (el) el.textContent = String(q);
    }

    addToCartCore(product, qty) {
        if (!window.posSystem) return false;
        const stock = parseInt(product.stock_quantity, 10) || 0;
        if (stock <= 0 || qty < 1 || qty > stock) return false;
        const cartItem = {
            id: product.id,
            name: product.name,
            code: product.barcode || '-',
            price: parseFloat(product.price),
            qty,
        };
        const existingIndex = window.posSystem.cart.findIndex((item) => item.id === cartItem.id);
        if (existingIndex >= 0) {
            window.posSystem.cart[existingIndex].qty += qty;
        } else {
            window.posSystem.cart.push(cartItem);
        }
        window.posSystem.renderCart();
        return true;
    }

    addFromPanel(productId) {
        const product = this.products.find((p) => p.id === productId);
        if (!product) return;
        const qty = this.panelQty[productId] != null ? this.panelQty[productId] : 1;
        if (!this.addToCartCore(product, qty)) {
            this.showAlert('Jumlah tidak valid', 'warning');
            return;
        }
        const btn = document.getElementById(`panel-cart-btn-${productId}`);
        if (btn) {
            const prev = btn.textContent;
            btn.textContent = '✓ Ditambahkan';
            btn.disabled = true;
            setTimeout(() => {
                this.expandedProductId = null;
                this.panelQty[productId] = 1;
                this.renderProducts();
            }, 1200);
        } else {
            this.expandedProductId = null;
            this.panelQty[productId] = 1;
            this.renderProducts();
        }
    }

    quickAdd(productId) {
        const product = this.products.find((p) => p.id === productId);
        if (!product) return;
        const stock = parseInt(product.stock_quantity, 10) || 0;
        if (stock <= 0) {
            this.showAlert('Produk ini stok habis', 'warning');
            return;
        }
        if (!this.addToCartCore(product, 1)) return;

        const card = document.querySelector(`[data-product-id="${productId}"]`);
        const btns = card ? card.querySelectorAll('.pos-pc-add-btn') : [];
        btns.forEach((btn) => {
            btn.textContent = '✓';
            btn.classList.add('pos-pc-add-btn--flash');
            btn.disabled = true;
            setTimeout(() => {
                if (!btn.isConnected) return;
                btn.textContent = '+';
                btn.classList.remove('pos-pc-add-btn--flash');
                btn.disabled = stock <= 0;
            }, 1000);
        });
    }

    selectProduct(productId) {
        this.toggleDetail(productId);
    }

    openProductDetail(productId) {
        this.toggleDetail(productId);
    }

    closeProductDetail() {}

    showQuantityModal() {}

    closeQuantityModal() {}

    updateLoadMoreButton() {
        const wrapper = document.getElementById('load-more-wrapper');
        if (!wrapper) return;
        if (this.currentSearchQuery) {
            wrapper.style.display = 'none';
            return;
        }
        wrapper.style.display = this.currentPage < this.lastPage ? 'block' : 'none';
    }

    showAlert(message, type = 'info') {
        if (window.posSystem) {
            window.posSystem.showAlert(message, type);
        }
    }

    async loadCategories() {
        try {
            const response = await fetch('/api/categories', {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();

            this.categoryIdsByGroup = { pupuk: [], alat: [], herbisida: [], pestisida: [] };
            this.groupProductCounts = {
                all: Number(data.total_products) || 0,
                pupuk: 0,
                alat: 0,
                herbisida: 0,
                pestisida: 0,
            };

            if (data.data && data.data.length > 0) {
                const seen = new Set();
                data.data.forEach((category) => {
                    const nameKey = String(category.name ?? '').trim().toLowerCase();
                    if (!nameKey || seen.has(nameKey)) return;
                    seen.add(nameKey);
                    const g = ProductsGrid.assignPosGroup(category);
                    const cnt = Number(category.products_count) || 0;
                    if (g && this.categoryIdsByGroup[g]) {
                        this.categoryIdsByGroup[g].push(category.id);
                        this.groupProductCounts[g] += cnt;
                    }
                });
            }

            this.refreshTabBadges();

            if (this.posGroup !== 'all') {
                this.setPosGroup(this.posGroup);
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            this.applyFilters();
        }
    }

    refreshTabBadges() {
        const c = this.groupProductCounts || {};
        document.querySelectorAll('.pos-cat-tab__badge').forEach((el) => {
            const key = el.getAttribute('data-badge-for');
            if (!key) return;
            const n = c[key] != null ? c[key] : 0;
            el.textContent = String(n);
        });
    }
}

function filterByCategory(categoryId) {
    if (window.productsGrid) {
        window.productsGrid.posGroup = 'all';
        window.productsGrid.selectedCategoryId = categoryId || '';
        window.productsGrid.currentCategoryIds = categoryId ? [Number(categoryId)] : [];
        window.productsGrid.currentPage = 1;

        document.querySelectorAll('#pos-category-tabs .pos-cat-tab').forEach((btn) => {
            const active = (btn.getAttribute('data-pos-group') || 'all') === 'all';
            btn.classList.toggle('active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        window.productsGrid.applyFilters();
    }
}

function loadMoreProducts() {
    if (window.productsGrid) {
        window.productsGrid.currentPage++;
        window.productsGrid.loadProducts(window.productsGrid.currentPage);
    }
}
