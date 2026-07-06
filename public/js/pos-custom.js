// POS Custom JavaScript - Modern & Intuitive System

class POSSystem {
    constructor() {
        this.cart = [];
        this.currentStep = 1;
        this.products = [];
        this.pendingSalePayload = null;
        this.pendingSidebarPayload = null;
        this.isSubmittingSale = false;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateStep(1);
    }

    setupEventListeners() {
        // Product search
        const productSearch = document.getElementById('product-search');
        if (productSearch) {
            productSearch.addEventListener('input', (e) => this.handleProductSearch(e));
            productSearch.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addToCart();
                }
            });
        }

        // Quantity input
        const qtyInput = document.getElementById('qty');
        if (qtyInput) {
            qtyInput.addEventListener('change', () => this.validateQty());
        }

        // Delivery method
        const deliveryRadios = document.querySelectorAll('input[name="delivery_method"]');
        deliveryRadios.forEach(radio => {
            radio.addEventListener('change', () => this.handleDeliveryMethodChange());
        });

        // Discount and payment
        const discountInput = document.getElementById('discount');
        const paymentInput = document.getElementById('payment');
        
        if (discountInput) {
            discountInput.addEventListener('input', () => this.updatePricing());
        }
        
        if (paymentInput) {
            this.bindAmountInput(paymentInput, () => this.updatePricing());
            paymentInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('checkout-form').dispatchEvent(new Event('submit'));
                }
            });
        }

        // Checkout form
        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', (e) => this.handleCheckout(e));
        }

        // Sidebar payment amount live info
        const sidebarAmount = document.getElementById('sidebar-payment-amount');
        if (sidebarAmount) {
            this.bindAmountInput(sidebarAmount, () => this.updateSidebarPaymentInfo());
        }

        // Toggle metode pengantaran di panel kanan
        const deliveryButtons = document.querySelectorAll('.sidebar-delivery-btn');
        if (deliveryButtons.length) {
            deliveryButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    this.setSidebarDeliveryMethod(btn.dataset.method || 'pickup');
                });
            });
        }

        const deliveryDistanceInput = document.getElementById('sidebar-delivery-distance');
        const deliveryLevelInput = document.getElementById('sidebar-delivery-level');
        const deliveryFeeEnabled = document.getElementById('sidebar-delivery-fee-enabled');
        const deliveryFeeInput = document.getElementById('sidebar-delivery-fee-input');
        if (deliveryDistanceInput) {
            deliveryDistanceInput.addEventListener('input', () => this.updateSidebarPaymentInfo());
        }
        if (deliveryLevelInput) {
            deliveryLevelInput.addEventListener('change', () => this.updateSidebarPaymentInfo());
        }
        if (deliveryFeeEnabled) {
            deliveryFeeEnabled.addEventListener('change', () => this.updateSidebarPaymentInfo());
        }
        if (deliveryFeeInput) {
            deliveryFeeInput.addEventListener('input', () => this.updateSidebarPaymentInfo());
        }

        // Metode pembayaran di sidebar (tunai / transfer)
        const methodButtons = document.querySelectorAll('.sidebar-method-btn');
        if (methodButtons.length) {
            methodButtons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    methodButtons.forEach((b) => b.classList.remove('active'));
                    btn.classList.add('active');
                });
            });
        }
    }

    // Step Management
    updateStep(step) {
        this.currentStep = step;
        
        // Update step indicators
        document.querySelectorAll('.step').forEach((stepEl, index) => {
            if (index + 1 <= step) {
                stepEl.classList.add('active');
            } else {
                stepEl.classList.remove('active');
            }
        });

        // Show/hide sections
        for (let i = 1; i <= 4; i++) {
            const section = document.getElementById(`step-${i}-section`);
            if (section) {
                section.style.display = i === step ? 'block' : 'none';
            }
        }
        
        // Show/hide main layout
        const mainLayout = document.querySelector('.pos-main-layout');
        if (mainLayout) {
            if (step === 3 || step === 4) {
                mainLayout.style.display = 'none';
            } else {
                mainLayout.style.display = 'grid';
            }
        }

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Product Search
    async handleProductSearch(e) {
        const query = e.target.value.trim();
        const productList = document.getElementById('product-list');
        
        if (query.length < 2) {
            productList.innerHTML = '';
            productList.style.display = 'none';
            return;
        }

        try {
            const res = await fetch(`/api/products?search=${encodeURIComponent(query)}`);
            const data = await res.json();
            this.products = data.data || [];
            
            if (this.products.length === 0) {
                productList.innerHTML = '<div class="product-item">Produk tidak ditemukan</div>';
                productList.style.display = 'block';
                return;
            }

            productList.innerHTML = this.products.map((product, index) => `
                <div class="product-item" onclick="posSystem.selectProduct(${index})">
                    <div>
                        <div class="product-item-name">${product.name}</div>
                        <div style="font-size: 0.9rem; color: #64748b;">${product.code || '-'}</div>
                    </div>
                    <div class="product-item-price">${this.formatRupiah(product.price)}</div>
                </div>
            `).join('');
            
            productList.style.display = 'block';
        } catch (error) {
            console.error('Error searching products:', error);
        }
    }

    selectProduct(index) {
        const product = this.products[index];
        const productSearch = document.getElementById('product-search');
        const productList = document.getElementById('product-list');
        
        productSearch.value = `${product.name} (${product.code || '-'})`;
        productSearch.dataset.selected = JSON.stringify(product);
        productList.style.display = 'none';
        
        // Focus on quantity
        document.getElementById('qty').focus();
    }

    // Quantity Management
    changeQty(delta) {
        const qtyInput = document.getElementById('qty');
        const currentQty = parseInt(qtyInput.value) || 1;
        const newQty = Math.max(1, currentQty + delta);
        qtyInput.value = newQty;
    }

    validateQty() {
        const qtyInput = document.getElementById('qty');
        const value = parseInt(qtyInput.value) || 1;
        qtyInput.value = Math.max(1, value);
    }

    // Add to Cart
    addToCart() {
        const productSearch = document.getElementById('product-search');
        const qtyInput = document.getElementById('qty');
        
        let product;
        try {
            product = JSON.parse(productSearch.dataset.selected);
        } catch {
            this.showAlert('Pilih produk dari daftar terlebih dahulu!', 'warning');
            productSearch.focus();
            return;
        }

        const qty = parseInt(qtyInput.value) || 1;
        
        // Check if product already in cart
        const existingIndex = this.cart.findIndex(item => item.id === product.id);
        
        if (existingIndex >= 0) {
            this.cart[existingIndex].qty += qty;
            this.showAlert(`Jumlah ${product.name} ditambah ${qty}`, 'success');
        } else {
            this.cart.push({
                id: product.id,
                name: product.name,
                code: product.code || '-',
                price: parseFloat(product.price),
                qty: qty
            });
            this.showAlert(`${product.name} ditambahkan ke keranjang`, 'success');
        }

        this.renderCart();
        
        // Reset form
        productSearch.value = '';
        productSearch.dataset.selected = '';
        qtyInput.value = 1;
        document.getElementById('product-list').style.display = 'none';
        
        // Move to step 2
        this.updateStep(2);
    }

    // Render Cart
    renderCart() {
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const cartCount = document.getElementById('cart-count');
        const toPaymentBtn = document.getElementById('to-payment-btn');
        
        if (this.cart.length === 0) {
            cartItems.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-icon">🛒</div>
                    <p>Keranjang masih kosong</p>
                    <p class="empty-hint">Tambahkan produk terlebih dahulu</p>
                </div>
            `;
            cartTotal.textContent = 'Rp 0';
            cartCount.textContent = '0 item';
            if (toPaymentBtn) toPaymentBtn.disabled = true;
            return;
        }

        const total = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        
        cartItems.innerHTML = this.cart.map((item, index) => {
            const subtotal = item.price * item.qty;
            return `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-code">${item.code}</div>
                    </div>
                    <div class="cart-item-price">${this.formatRupiah(item.price)}</div>
                    <div class="cart-item-qty">
                        <button class="qty-btn minus" onclick="posSystem.updateCartQty(${index}, -1)">−</button>
                        <input type="number" value="${item.qty}" min="1" 
                               onchange="posSystem.setCartQty(${index}, this.value)">
                        <button class="qty-btn plus" onclick="posSystem.updateCartQty(${index}, 1)">+</button>
                    </div>
                    <div class="cart-item-price">${this.formatRupiah(subtotal)}</div>
                    <button class="cart-item-remove" onclick="posSystem.removeFromCart(${index})" title="Hapus">×</button>
                </div>
            `;
        }).join('');

        cartTotal.textContent = this.formatRupiah(total);
        cartCount.textContent = `${this.cart.length} ${this.cart.length === 1 ? 'item' : 'items'}`;
        if (toPaymentBtn) toPaymentBtn.disabled = false;
    }

    updateCartQty(index, delta) {
        const newQty = Math.max(1, this.cart[index].qty + delta);
        this.cart[index].qty = newQty;
        this.renderCart();
    }

    setCartQty(index, value) {
        this.cart[index].qty = Math.max(1, parseInt(value) || 1);
        this.renderCart();
    }

    removeFromCart(index) {
        const item = this.cart[index];
        this.cart.splice(index, 1);
        this.renderCart();
        this.showAlert(`${item.name} dihapus dari keranjang`, 'success');
        
        if (this.cart.length === 0) {
            this.updateStep(1);
        }
    }

    // Navigation
    goToPayment() {
        // Fungsi lama tidak lagi dipakai untuk navigasi penuh.
        // Pembayaran kini dilakukan via panel di sisi kanan.
        this.openSidebarPayment();
    }

    goBackToCart() {
        this.updateStep(2);
    }

    // Pricing
    updatePricing() {
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const discountInput = parseFloat(document.getElementById('discount').value) || 0;
        const payment = this.parseAmountInput(document.getElementById('payment')?.value);
        
        const discountAmount = Math.min(total, Math.max(0, discountInput));
        const afterDiscount = Math.max(0, total - discountAmount);
        const change = Math.max(0, payment - afterDiscount);
        const debt = Math.max(0, afterDiscount - payment);

        // Update displays
        document.getElementById('subtotal-display').textContent = this.formatRupiah(total);
        document.getElementById('discount-amount').textContent = this.formatRupiah(discountAmount);
        document.getElementById('total-display').textContent = this.formatRupiah(afterDiscount);
        
        // Change display
        const changeDisplay = document.getElementById('change-display');
        const changeAmount = document.getElementById('change-amount');
        if (change > 0) {
            changeDisplay.style.display = 'flex';
            changeAmount.textContent = this.formatRupiah(change);
        } else {
            changeDisplay.style.display = 'none';
        }

        // Debt display
        const debtDisplay = document.getElementById('debt-display');
        const debtAmount = document.getElementById('debt-amount');
        if (debt > 0) {
            debtDisplay.style.display = 'flex';
            debtAmount.textContent = this.formatRupiah(debt);
        } else {
            debtDisplay.style.display = 'none';
        }
    }

    setQuickPayment(percentage) {
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const discountInput = parseFloat(document.getElementById('discount').value) || 0;
        const discountAmount = Math.min(total, Math.max(0, discountInput));
        const afterDiscount = Math.max(0, total - discountAmount);
        const payment = Math.floor(afterDiscount * percentage);
        
        this.setAmountInputValue('payment', payment);
        this.updatePricing();
    }

    // Delivery Method
    handleDeliveryMethodChange() {
        const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;
        const deliveryInfo = document.getElementById('delivery-info');
        
        if (deliveryMethod === 'delivery') {
            deliveryInfo.style.display = 'block';
        } else {
            deliveryInfo.style.display = 'none';
        }
    }

    // Checkout
    async handleCheckout(e) {
        e.preventDefault();
        
        if (this.cart.length === 0) {
            this.showAlert('Keranjang kosong!', 'danger');
            return;
        }

        const total = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const discountInput = parseFloat(document.getElementById('discount').value) || 0;
        const payment = this.parseAmountInput(document.getElementById('payment')?.value);
        const discountAmount = Math.min(total, Math.max(0, discountInput));
        const afterDiscount = Math.max(0, total - discountAmount);
        const change = Math.max(0, payment - afterDiscount);
        const debt = afterDiscount - payment;

        const formCustomerName = document.getElementById('customer').value || '';
        const formCustomerPhone = document.getElementById('customer_phone').value || '';

        const payload = {
            customer_name: formCustomerName !== '' ? formCustomerName : 'Umum',
            customer_phone: formCustomerPhone !== '' ? formCustomerPhone : null,
            delivery_method: document.querySelector('input[name="delivery_method"]:checked').value,
            delivery_address: document.getElementById('delivery_address')?.value || null,
            delivery_phone: document.getElementById('delivery_phone')?.value || null,
            discount: discountAmount,
            total_amount: total,
            payment_method: 'cash',
            payment: payment,
            change: change,
            items: this.cart.map(item => ({
                product_id: item.id,
                quantity: item.qty,
                unit_price: item.price
            }))
        };

        // Check if payment is insufficient
        if (payment < afterDiscount) {
            this.pendingSalePayload = { ...payload, payment_method: 'credit' };
            this.showCreditModal();
            return;
        }

        // Send sale
        await this.sendSale(payload);
    }

    // Sidebar payment (panel di kanan)
    openSidebarPayment() {
        if (this.cart.length === 0) {
            this.showAlert('Keranjang kosong!', 'danger');
            return;
        }

        const total = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const cartDiscountInput = document.getElementById('cart-discount');
        const discountInput = parseFloat(cartDiscountInput ? cartDiscountInput.value : 0) || 0;
        const discountAmount = Math.min(total, Math.max(0, discountInput));
        const afterDiscount = Math.max(0, total - discountAmount);

        const totalEl = document.getElementById('sidebar-payment-total');
        const amountEl = document.getElementById('sidebar-payment-amount');
        const modal = document.getElementById('sidebar-payment-modal');

        if (totalEl) totalEl.textContent = this.formatRupiah(afterDiscount);
        if (amountEl) amountEl.value = this.formatAmountInput(afterDiscount);
        if (modal) modal.style.display = 'flex';

        this.updateSidebarPaymentInfo();

        // Sinkronkan metode pengantaran dari form utama (jika ada)
        const mainDelivery = document.querySelector('input[name="delivery_method"]:checked')?.value || 'pickup';
        this.setSidebarDeliveryMethod(mainDelivery);

        // Sinkronkan nama & telepon pelanggan dari form utama
        const mainName = document.getElementById('customer')?.value || '';
        const mainPhone = document.getElementById('customer_phone')?.value || '';
        const sidebarNameInput = document.getElementById('sidebar-customer-name');
        const sidebarPhoneInput = document.getElementById('sidebar-customer-phone');
        if (sidebarNameInput && !sidebarNameInput.value) {
            sidebarNameInput.value = mainName;
        }
        if (sidebarPhoneInput && !sidebarPhoneInput.value) {
            sidebarPhoneInput.value = mainPhone;
        }
    }

    setSidebarDeliveryMethod(method) {
        const buttons = document.querySelectorAll('.sidebar-delivery-btn');
        buttons.forEach(btn => {
            if (btn.dataset.method === method) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        const fields = document.getElementById('sidebar-delivery-fields');
        if (fields) {
            fields.style.display = method === 'delivery' ? 'flex' : 'none';
        }
        this.updateSidebarPaymentInfo();
    }

    closeSidebarPayment() {
        const modal = document.getElementById('sidebar-payment-modal');
        if (modal) modal.style.display = 'none';
    }

    setSidebarQuickPayment(amount) {
        this.setAmountInputValue('sidebar-payment-amount', amount);
        this.updateSidebarPaymentInfo();
    }

    updateSidebarPaymentInfo() {
        const infoEl = document.getElementById('sidebar-payment-info');
        if (!infoEl) return;

        const total = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const cartDiscountInput = document.getElementById('cart-discount');
        const discountInput = parseFloat(cartDiscountInput ? cartDiscountInput.value : 0) || 0;
        const amountInput = document.getElementById('sidebar-payment-amount');
        const payment = this.parseAmountInput(amountInput ? amountInput.value : '');
        const deliveryMethod = document.querySelector('.sidebar-delivery-btn.active')?.dataset.method || 'pickup';
        const shippingBase = this.getShippingFee(deliveryMethod);
        const feeEnabledEl = document.getElementById('sidebar-delivery-fee-enabled');
        const feeInputEl = document.getElementById('sidebar-delivery-fee-input');
        const feeSuggestEl = document.getElementById('sidebar-delivery-fee-suggest');
        const feeEnabled = deliveryMethod === 'delivery' ? (feeEnabledEl ? !!feeEnabledEl.checked : true) : false;
        const feeInputVal = parseFloat(feeInputEl ? feeInputEl.value : '') ;
        const feeHasUserValue = Number.isFinite(feeInputVal);
        const shippingFee = feeEnabled ? Math.max(0, Math.round(feeHasUserValue ? feeInputVal : shippingBase)) : 0;
        const shippingFeeEl = document.getElementById('sidebar-delivery-fee');
        if (shippingFeeEl) {
            shippingFeeEl.textContent = `Ongkir dipakai: ${this.formatRupiah(shippingFee)}`;
        }
        if (feeSuggestEl) {
            feeSuggestEl.textContent = `Saran: ${this.formatRupiah(shippingBase)}`;
        }

        // jika kasir belum isi ongkir, isi default saran sekali (biar "pas" lebih cepat)
        if (deliveryMethod === 'delivery' && feeEnabled && feeInputEl && !feeInputEl.value) {
            feeInputEl.value = String(Math.round(shippingBase));
        }

        const discountAmount = Math.min(total, Math.max(0, discountInput));
        const afterDiscount = (Math.max(0, total - discountAmount)) + shippingFee;
        const totalEl = document.getElementById('sidebar-payment-total');
        if (totalEl) totalEl.textContent = this.formatRupiah(afterDiscount);
        const change = Math.max(0, payment - afterDiscount);
        const debt = Math.max(0, afterDiscount - payment);

        infoEl.classList.remove('success', 'warning');

        if (!total || !payment) {
            infoEl.textContent = '';
            return;
        }

        if (change > 0) {
            infoEl.textContent = `Kembalian: ${this.formatRupiah(change)}`;
            infoEl.classList.add('success');
        } else if (debt > 0) {
            infoEl.textContent = `Kurang: ${this.formatRupiah(debt)} (akan dicatat sebagai piutang)`;
            infoEl.classList.add('warning');
        } else {
            infoEl.textContent = 'Pas, tidak ada kembalian.';
            infoEl.classList.add('success');
        }
    }

    async handleSidebarPayment() {
        if (this.cart.length === 0) {
            this.showAlert('Keranjang kosong!', 'danger');
            return;
        }

        const total = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const cartDiscountInput = document.getElementById('cart-discount');
        const discountInput = parseFloat(cartDiscountInput ? cartDiscountInput.value : 0) || 0;
        const amountInput = document.getElementById('sidebar-payment-amount');
        const payment = this.parseAmountInput(amountInput ? amountInput.value : '');

        // Metode pembayaran
        let method = 'cash';
        const activeMethod = document.querySelector('.sidebar-method-btn.active');
        if (activeMethod && activeMethod.dataset.method) {
            const map = { cash: 'cash', transfer: 'transfer' };
            method = map[activeMethod.dataset.method] || 'cash';
        }

        // Gunakan nama pelanggan dari form jika ada, jika tidak pakai default "Umum"
        // Nama & telepon dari panel kanan (lebih diutamakan), fallback ke form utama
        const sidebarCustomerName = document.getElementById('sidebar-customer-name')?.value || '';
        const sidebarCustomerPhone = document.getElementById('sidebar-customer-phone')?.value || '';
        const formCustomerNameMain = document.getElementById('customer')?.value || '';
        const formCustomerPhoneMain = document.getElementById('customer_phone')?.value || '';

        const formCustomerName = sidebarCustomerName || formCustomerNameMain;
        const formCustomerPhone = sidebarCustomerPhone || formCustomerPhoneMain;

        // Data pengantaran dari panel kanan
        const activeDelivery = document.querySelector('.sidebar-delivery-btn.active');
        const sidebarDeliveryMethod = activeDelivery?.dataset.method || 'pickup';
        const sidebarAddress = document.getElementById('sidebar-delivery-address')?.value || null;
        const sidebarPhone = document.getElementById('sidebar-delivery-phone')?.value || null;
        const deliveryLevel = document.getElementById('sidebar-delivery-level')?.value || 'reguler';
        const deliveryDistance = parseFloat(document.getElementById('sidebar-delivery-distance')?.value || 0) || 0;
        const shippingBase = this.getShippingFee(sidebarDeliveryMethod);
        const feeEnabledEl = document.getElementById('sidebar-delivery-fee-enabled');
        const feeInputEl = document.getElementById('sidebar-delivery-fee-input');
        const feeEnabled = sidebarDeliveryMethod === 'delivery' ? (feeEnabledEl ? !!feeEnabledEl.checked : true) : false;
        const feeInputVal = parseFloat(feeInputEl ? feeInputEl.value : '');
        const feeHasUserValue = Number.isFinite(feeInputVal);
        const shippingFee = feeEnabled ? Math.max(0, Math.round(feeHasUserValue ? feeInputVal : shippingBase)) : 0;

        const deliveryMethod = sidebarDeliveryMethod;
        const discountAmount = Math.min(total, Math.max(0, discountInput));
        const afterDiscount = (Math.max(0, total - discountAmount)) + shippingFee;
        const change = Math.max(0, payment - afterDiscount);
        const debt = afterDiscount - payment;

        const payload = {
            customer_name: formCustomerName !== '' ? formCustomerName : 'Umum',
            customer_phone: formCustomerPhone !== '' ? formCustomerPhone : null,
            delivery_method: deliveryMethod,
            delivery_address: deliveryMethod === 'delivery' ? sidebarAddress : null,
            delivery_phone: deliveryMethod === 'delivery' ? sidebarPhone : null,
            delivery_level: deliveryMethod === 'delivery' ? deliveryLevel : null,
            delivery_distance_km: deliveryMethod === 'delivery' ? deliveryDistance : null,
            shipping_fee: shippingFee,
            discount: discountAmount,
            total_amount: total,
            payment_method: method,
            payment: payment,
            change: change,
            notes: deliveryMethod === 'delivery' ?
                `Ongkir ${this.formatRupiah(shippingFee)} (saran ${this.formatRupiah(shippingBase)}${feeHasUserValue ? ', diubah kasir' : ''}) • Level ${deliveryLevel} • Jarak ${deliveryDistance} km` : null,
            items: this.cart.map(item => ({
                product_id: item.id,
                quantity: item.qty,
                unit_price: item.price
            }))
        };

        if (payment < afterDiscount) {
            payload.payment_method = 'credit';
            this.pendingSidebarPayload = payload;
            this.showDebtCustomerModal(formCustomerName, formCustomerPhone);
            return;
        }

        await this.sendSale(payload, true);
        this.closeSidebarPayment();
    }

    getShippingFee(deliveryMethod = 'pickup') {
        if (deliveryMethod !== 'delivery') return 0;
        const distanceKm = parseFloat(document.getElementById('sidebar-delivery-distance')?.value || 0) || 0;
        const level = document.getElementById('sidebar-delivery-level')?.value || 'reguler';
        const multiplierMap = {
            hemat: 1,
            reguler: 1.25,
            express: 1.6
        };
        const basePerKm = 3000;
        const minimum = level === 'express' ? 12000 : 8000;
        const fee = Math.max(minimum, Math.round(distanceKm * basePerKm * (multiplierMap[level] || 1)));
        return fee;
    }

    toLocalISODate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    showDebtCustomerModal(name = '', phone = '') {
        const modal = document.getElementById('debt-customer-modal');
        if (!modal) return;
        const nameInput = document.getElementById('debt-modal-customer-name');
        const phoneInput = document.getElementById('debt-modal-customer-phone');
        const dueInput = document.getElementById('debt-modal-due-date');
        if (nameInput) nameInput.value = name || '';
        if (phoneInput) phoneInput.value = phone || '';
        if (dueInput) {
            const today = new Date();
            dueInput.min = this.toLocalISODate(today);
            const def = new Date(today);
            def.setDate(def.getDate() + 30);
            dueInput.value = this.toLocalISODate(def);
        }
        modal.style.display = 'flex';
    }

    closeDebtCustomerModal() {
        const modal = document.getElementById('debt-customer-modal');
        if (modal) modal.style.display = 'none';
        this.pendingSidebarPayload = null;
    }

    async submitDebtCustomerModal() {
        if (!this.pendingSidebarPayload) return;
        const name = document.getElementById('debt-modal-customer-name')?.value?.trim() || '';
        const phone = document.getElementById('debt-modal-customer-phone')?.value?.trim() || '';
        const dueRaw = document.getElementById('debt-modal-due-date')?.value || '';
        if (!name) {
            this.showAlert('Nama pelanggan wajib diisi untuk transaksi piutang.', 'warning');
            return;
        }
        if (!dueRaw) {
            this.showAlert('Tanggal jatuh tempo wajib diisi.', 'warning');
            return;
        }
        const todayStr = this.toLocalISODate(new Date());
        if (dueRaw < todayStr) {
            this.showAlert('Jatuh tempo tidak boleh sebelum hari ini.', 'warning');
            return;
        }
        this.pendingSidebarPayload.customer_name = name;
        this.pendingSidebarPayload.customer_phone = phone || null;
        this.pendingSidebarPayload.due_date = dueRaw;
        await this.sendSale(this.pendingSidebarPayload, true);
        this.pendingSidebarPayload = null;
        const modal = document.getElementById('debt-customer-modal');
        if (modal) modal.style.display = 'none';
        this.closeSidebarPayment();
    }

    async sendSale(payload, fromSidebar = false) {
        if (this.isSubmittingSale) {
            return;
        }

        this.isSubmittingSale = true;
        const btnMain = document.getElementById('submit-btn');
        const btnSidebar = document.querySelector('.sidebar-payment-primary');
        if (btnMain) btnMain.disabled = true;
        if (btnSidebar) btnSidebar.disabled = true;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch('/api/pos/sales', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                // UI updates setelah transaksi sukses tidak boleh memunculkan toast "network error"
                try {
                    if (fromSidebar) {
                        // Alur pembayaran dari panel kanan: tampilkan popup sukses di tengah
                        const successModal = document.getElementById('sale-success-modal');
                        if (successModal) {
                            successModal.style.display = 'flex';
                            setTimeout(() => {
                                successModal.style.display = 'none';
                            }, 1800);
                        }

                        this.cart = [];
                        if (typeof this.renderCart === 'function') {
                            this.renderCart();
                        }
                        const checkoutForm = document.getElementById('checkout-form');
                        if (checkoutForm) checkoutForm.reset();
                    } else {
                        this.updateStep(4);
                        this.cart = [];
                        const checkoutForm = document.getElementById('checkout-form');
                        if (checkoutForm) checkoutForm.reset();
                    }
                } catch (uiErr) {
                    // Transaksi sudah tersimpan; jika UI error, cukup log untuk debugging.
                    // eslint-disable-next-line no-console
                    console.error('UI error after successful sale:', uiErr);
                }
            } else {
                const data = await res.json().catch(() => ({}));
                this.showAlert(data.message || 'Gagal menyimpan transaksi!', 'danger');
            }
        } catch (error) {
            console.error('Error saving sale:', error);
            this.showAlert('Gagal menyimpan transaksi (network error)', 'danger');
        } finally {
            this.isSubmittingSale = false;
            if (btnMain) btnMain.disabled = false;
            if (btnSidebar) btnSidebar.disabled = false;
        }
    }

    // Credit Modal
    showCreditModal() {
        document.getElementById('credit-modal').style.display = 'flex';
    }

    closeCreditModal() {
        document.getElementById('credit-modal').style.display = 'none';
    }

    confirmCredit() {
        this.closeCreditModal();
        if (!this.pendingSalePayload) return;
        const payload = { ...this.pendingSalePayload, payment_method: 'credit' };
        this.pendingSalePayload = null;
        this.pendingSidebarPayload = payload;
        const rawName = payload.customer_name || '';
        const name = rawName && rawName !== 'Umum' ? rawName : '';
        const phone = payload.customer_phone || '';
        this.showDebtCustomerModal(name, phone);
    }

    // New Transaction
    startNewTransaction() {
        this.cart = [];
        this.currentStep = 1;
        document.getElementById('checkout-form').reset();
        document.getElementById('product-search').value = '';
        document.getElementById('qty').value = 1;
        this.updateStep(1);
        this.showAlert('Transaksi baru dimulai', 'success');
    }

    // Utility
    parseAmountInput(value) {
        const digits = String(value ?? '').replace(/\D/g, '');
        return digits ? parseInt(digits, 10) : 0;
    }

    formatAmountInput(num) {
        const n = Math.max(0, Math.round(Number(num) || 0));
        return n.toLocaleString('id-ID');
    }

    setAmountInputValue(idOrEl, amount) {
        const el = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
        if (!el) return;
        el.value = this.formatAmountInput(amount);
    }

    bindAmountInput(input, onChange) {
        if (!input) return;
        const applyFormat = (allowEmpty) => {
            const digits = String(input.value ?? '').replace(/\D/g, '');
            if (allowEmpty && digits === '') {
                input.value = '';
                if (typeof onChange === 'function') onChange();
                return;
            }
            const parsed = digits ? parseInt(digits, 10) : 0;
            input.value = this.formatAmountInput(parsed);
            if (typeof onChange === 'function') onChange();
        };
        input.addEventListener('input', () => applyFormat(true));
        input.addEventListener('blur', () => applyFormat(false));
    }

    formatRupiah(num) {
        return 'Rp ' + num.toLocaleString('id-ID');
    }

    showAlert(message, type = 'info') {
        const alertBox = document.getElementById('alert-box');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        
        alertBox.appendChild(alert);
        
        setTimeout(() => {
            alert.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }
}

// Global functions for onclick handlers
function changeQty(delta) {
    posSystem.changeQty(delta);
}

function addToCart() {
    posSystem.addToCart();
}

function goToPayment() {
    posSystem.goToPayment();
}

function goBackToCart() {
    posSystem.goBackToCart();
}

function setQuickPayment(percentage) {
    posSystem.setQuickPayment(percentage);
}

function closeCreditModal() {
    posSystem.closeCreditModal();
}

function confirmCredit() {
    posSystem.confirmCredit();
}

function startNewTransaction() {
    posSystem.startNewTransaction();
}

// Sidebar payment globals
function openSidebarPayment() {
    posSystem.openSidebarPayment();
}

function closeSidebarPayment() {
    posSystem.closeSidebarPayment();
}

function setSidebarQuickPayment(amount) {
    posSystem.setSidebarQuickPayment(amount);
}

function submitSidebarPayment() {
    posSystem.handleSidebarPayment();
}

function closeDebtCustomerModal() {
    posSystem.closeDebtCustomerModal();
}

function submitDebtCustomerModal() {
    posSystem.submitDebtCustomerModal();
}

