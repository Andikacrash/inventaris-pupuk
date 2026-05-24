@extends('layouts.app')

@section('content')
    <div class="product-form-container">
        <div class="product-form-header">
            <h1>➕ Tambah Produk Baru</h1>
            <p>Isi semua informasi produk di bawah ini</p>
        </div>

        @if ($errors->any())
            <div class="alert-error">
                <strong>⚠️ Terdapat kesalahan:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf
            <div class="product-form-grid">
                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-card">
                        <div class="card-header-custom">
                            <span class="card-icon">📋</span>
                            <h2>Informasi Dasar</h2>
                        </div>
                        <div class="card-body-custom">
                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">📦</span>
                                    Nama Produk <span class="required">*</span>
                                </label>
                                <input type="text" name="name" class="form-input" value="{{ old('name') }}"
                                    required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">🏷️</span>
                                    Brand <span class="required">*</span>
                                </label>
                                <input type="text" name="brand" class="form-input" value="{{ old('brand') }}"
                                    required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="label-icon">⚗️</span>
                                        Jenis <span class="required">*</span>
                                    </label>
                                    <select name="type" class="form-select" required>
                                        <option value="organik">🌱 Organik</option>
                                        <option value="kimia">🧪 Kimia</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="label-icon">📏</span>
                                        Satuan <span class="required">*</span>
                                    </label>
                                    <select name="unit" class="form-select" required>
                                        <option value="kg">Kg</option>
                                        <option value="liter">Liter</option>
                                        <option value="karung">Karung</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">💰</span>
                                    Harga <span class="required">*</span>
                                </label>
                                <div class="input-with-prefix">
                                    <span class="input-prefix">Rp</span>
                                    <input type="text" id="price-display" class="form-input" inputmode="numeric"
                                        autocomplete="off" value="{{ old('price') }}" placeholder="Contoh: 679.000"
                                        required>
                                    <input type="hidden" name="price" id="price-input" value="{{ old('price', 0) }}">
                                </div>
                                <small class="form-hint">Masukkan angka Rupiah tanpa desimal. Contoh: 679.000</small>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="label-icon">📊</span>
                                        Stok <span class="required">*</span>
                                    </label>
                                    <input type="number" name="stock_quantity" class="form-input"
                                        value="{{ old('stock_quantity') }}" min="0" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="label-icon">⚠️</span>
                                        Minimum Stok <span class="required">*</span>
                                    </label>
                                    <input type="number" name="minimum_stock" class="form-input"
                                        value="{{ old('minimum_stock') }}" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-card">
                        <div class="card-header-custom">
                            <span class="card-icon">🏢</span>
                            <h2>Kategori & Supplier</h2>
                        </div>
                        <div class="card-body-custom">
                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">📁</span>
                                    Kategori <span class="required">*</span>
                                </label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">- Pilih Kategori -</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">🏭</span>
                                    Supplier <small class="text-muted">(opsional)</small>
                                </label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">— Belum ditentukan —</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Supplier baru: <a href="{{ route('suppliers.index') }}" target="_blank" rel="noopener">halaman Supplier</a>.</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">🔢</span>
                                    Barcode (Opsional)
                                </label>
                                <input type="text" name="barcode" class="form-input" value="{{ old('barcode') }}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">📝</span>
                                    Deskripsi (Opsional)
                                </label>
                                <textarea name="description" class="form-textarea" rows="4" placeholder="Deskripsi produk...">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload Card -->
                    <div class="form-card">
                        <div class="card-header-custom">
                            <span class="card-icon">🖼️</span>
                            <h2>Gambar Produk</h2>
                        </div>
                        <div class="card-body-custom">
                            <div class="image-upload-area" id="image-upload-area">
                                <input type="file" name="image" id="image-input" class="image-input"
                                    accept="image/jpeg,image/png,image/jpg" onchange="previewImage(this)">
                                <div class="upload-placeholder" id="upload-placeholder">
                                    <div class="upload-icon">📷</div>
                                    <div class="upload-text">
                                        <strong>Klik untuk upload gambar</strong>
                                        <span>atau drag & drop di sini</span>
                                    </div>
                                    <div class="upload-hint">
                                        Format: JPG, PNG (Max: 2MB)
                                    </div>
                                </div>
                                <div class="image-preview" id="image-preview" style="display: none;">
                                    <img id="preview-img" src="" alt="Preview">
                                    <button type="button" class="remove-image-btn" onclick="removeImage()">✕
                                        Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
                <a href="{{ route('products.index') }}" class="btn-cancel">
                    ← Kembali
                </a>
                <button type="submit" class="btn-submit">
                    <span class="btn-icon">💾</span>
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ url('css/product-form-custom.css') }}?v={{ time() }}">
@endpush

@push('scripts')
    <script>
        function setupRupiahInput() {
            const priceDisplay = document.getElementById('price-display');
            const priceInput = document.getElementById('price-input');

            if (!priceDisplay || !priceInput) return;

            const formatRupiahDisplay = (value) => {
                const digitsOnly = String(value || '').replace(/[^\d]/g, '');
                if (!digitsOnly) return '';
                return Number(digitsOnly).toLocaleString('id-ID');
            };

            const syncPriceInput = () => {
                const digitsOnly = priceDisplay.value.replace(/[^\d]/g, '');
                priceInput.value = digitsOnly || '0';
            };

            priceDisplay.addEventListener('input', () => {
                priceDisplay.value = formatRupiahDisplay(priceDisplay.value);
                syncPriceInput();
            });

            priceDisplay.addEventListener('blur', () => {
                priceDisplay.value = formatRupiahDisplay(priceDisplay.value);
                syncPriceInput();
            });

            priceDisplay.value = formatRupiahDisplay(priceDisplay.value || priceInput.value);
            syncPriceInput();
        }

        function previewImage(input) {
            const file = input.files[0];
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            const img = document.getElementById('preview-img');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    placeholder.style.display = 'none';
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                placeholder.style.display = 'block';
                preview.style.display = 'none';
            }
        }

        function removeImage() {
            const input = document.getElementById('image-input');
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');

            input.value = '';
            placeholder.style.display = 'block';
            preview.style.display = 'none';
        }

        // Drag & Drop
        const uploadArea = document.getElementById('image-upload-area');
        const imageInput = document.getElementById('image-input');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');

            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                imageInput.files = files;
                previewImage(imageInput);
            }
        });

        uploadArea.addEventListener('click', () => {
            imageInput.click();
        });

        setupRupiahInput();
    </script>
@endpush
