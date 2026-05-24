// POS Cart Sidebar - Sidebar Cart Management

// Extend POSSystem untuk render cart di sidebar
if (typeof POSSystem !== 'undefined') {
    const originalRenderCart = POSSystem.prototype.renderCart;

    POSSystem.prototype.renderCart = function() {
        // Render di sidebar
        this.renderCartSidebar();

        // Juga render di step 2 jika ada
        if (originalRenderCart) {
            originalRenderCart.call(this);
        }
    };

    POSSystem.prototype.renderCartSidebar = function() {
        const cartItems = document.getElementById('cart-items-sidebar');
        const cartSubtotal = document.getElementById('cart-subtotal');
        const cartTotal = document.getElementById('cart-total-sidebar');
        const cartCount = document.getElementById('cart-count-badge');
        const checkoutBtn = document.getElementById('checkout-btn');
        const mainLayout = document.querySelector('.pos-main-layout');

        if (!cartItems) return;

        // Tampilkan/sembunyikan sidebar berdasarkan ada tidaknya item di cart
        if (this.cart.length === 0) {
            // Sembunyikan sidebar jika cart kosong
            if (mainLayout) {
                mainLayout.classList.remove('has-cart');
            }
            cartItems.innerHTML = '<div class="empty-cart-sidebar">' +
                '<div class="empty-icon">🛒</div>' +
                '<p>Keranjang masih kosong</p>' +
                '<p class="empty-hint">Klik produk untuk menambahkannya</p>' +
                '</div>';
            if (cartSubtotal) cartSubtotal.textContent = 'Rp 0';
            if (cartTotal) cartTotal.textContent = 'Rp 0';
            if (cartCount) cartCount.textContent = '0';
            if (checkoutBtn) checkoutBtn.disabled = true;
            return;
        }

        // Tampilkan sidebar jika ada item di cart
        if (mainLayout) {
            mainLayout.classList.add('has-cart');
        }

        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

        // Get product images from productsGrid
        const productImages = {};
        if (window.productsGrid && window.productsGrid.products) {
            window.productsGrid.products.forEach(product => {
                if (product.image_url) {
                    productImages[product.id] = product.image_url;
                }
            });
        }

        cartItems.innerHTML = this.cart.map((item, index) => {
            const subtotalItem = item.price * item.qty;
            const imageUrl = productImages[item.id] || null;

            const imageHtml = imageUrl
                ? '<img src="' + this.escapeHtml(imageUrl) + '" alt="' + this.escapeHtml(item.name) + '" class="cart-item-image" onerror="this.onerror=null; this.parentElement.innerHTML=\'<div class=&quot;cart-item-image-placeholder&quot;>📦</div>\';">'
                : '<div class="cart-item-image-placeholder">📦</div>';

            return '<div class="cart-item-sidebar">' +
                imageHtml +
                '<div class="cart-item-details">' +
                '<div class="cart-item-name-sidebar">' + this.escapeHtml(item.name) + '</div>' +
                '<div class="cart-item-price-sidebar">' + this.formatRupiah(item.price) + '</div>' +
                '</div>' +
                '<div class="cart-item-controls">' +
                '<div class="cart-item-qty-sidebar">' +
                '<button class="qty-btn-sidebar" onclick="posSystem.updateCartQty(' + index + ', -1)">−</button>' +
                '<input type="number" class="qty-input-sidebar" value="' + item.qty + '" min="1" onchange="posSystem.setCartQty(' + index + ', this.value)">' +
                '<button class="qty-btn-sidebar" onclick="posSystem.updateCartQty(' + index + ', 1)">+</button>' +
                '</div>' +
                '<div class="cart-item-subtotal">' + this.formatRupiah(subtotalItem) + '</div>' +
                '<button class="cart-item-remove-sidebar" onclick="posSystem.removeFromCart(' + index + ')" title="Hapus">×</button>' +
                '</div>' +
                '</div>';
        }).join('');

        this.updateCartSummary();
    };

    POSSystem.prototype.updateCartSummary = function() {
        const cartSubtotal = document.getElementById('cart-subtotal');
        const cartTotal = document.getElementById('cart-total-sidebar');
        const cartDiscount = document.getElementById('cart-discount');
        const cartDiscountAmount = document.getElementById('cart-discount-amount');
        const cartCount = document.getElementById('cart-count-badge');
        const checkoutBtn = document.getElementById('checkout-btn');
        const mainLayout = document.querySelector('.pos-main-layout');

        if (this.cart.length === 0) {
            // Sembunyikan sidebar jika cart kosong
            if (mainLayout) {
                mainLayout.classList.remove('has-cart');
            }
            if (cartSubtotal) cartSubtotal.textContent = 'Rp 0';
            if (cartTotal) cartTotal.textContent = 'Rp 0';
            if (cartDiscountAmount) cartDiscountAmount.textContent = 'Rp 0';
            if (cartCount) cartCount.textContent = '0';
            if (checkoutBtn) checkoutBtn.disabled = true;
            return;
        }

        // Tampilkan sidebar jika ada item di cart
        if (mainLayout) {
            mainLayout.classList.add('has-cart');
        }

        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const discountInput = parseFloat(cartDiscount ? cartDiscount.value : 0) || 0;
        const discountAmount = Math.min(subtotal, Math.max(0, discountInput));
        const total = Math.max(0, subtotal - discountAmount);
        const totalItems = this.cart.reduce((sum, item) => sum + item.qty, 0);

        if (cartSubtotal) cartSubtotal.textContent = this.formatRupiah(subtotal);
        if (cartTotal) cartTotal.textContent = this.formatRupiah(total);
        if (cartDiscountAmount) cartDiscountAmount.textContent = '- ' + this.formatRupiah(discountAmount);
        if (cartCount) cartCount.textContent = totalItems.toString();
        if (checkoutBtn) checkoutBtn.disabled = false;

        // Update discount di payment form juga
        const paymentDiscount = document.getElementById('discount');
        if (paymentDiscount && cartDiscount) {
            paymentDiscount.value = cartDiscount.value;
        }
    };

    POSSystem.prototype.escapeHtml = function(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    };
}
