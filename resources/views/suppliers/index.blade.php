@extends('layouts.app')

@section('content')
    <div class="product-modern-page">
        <div class="product-modern-header">
            <div>
                <h2 class="product-modern-title">Supplier</h2>
                <p class="product-modern-subtitle">Daftar sumber barang. Tambah supplier baru kapan saja — lalu pilih di form barang (opsional).</p>
            </div>
            <div class="product-modern-actions">
                <a href="{{ route('products.index') }}" class="btn btn-modern-outline">← Kembali ke barang</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="p-4 rounded-3 border" style="border-color: var(--ps-border) !important; background: var(--ps-surface);">
                    <h3 class="h6 fw-semibold mb-3" style="color: var(--ps-text);">Tambah supplier</h3>
                    <form method="POST" action="{{ route('suppliers.store') }}" class="d-flex flex-column gap-3">
                        @csrf
                        <div>
                            <label class="form-label small" style="color: var(--ps-muted);">Nama supplier <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255"
                                placeholder="Contoh: UD Sumber Tani">
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label small" style="color: var(--ps-muted);">Kontak (opsional)</label>
                            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}" maxlength="255"
                                placeholder="Nama PIC">
                        </div>
                        <div>
                            <label class="form-label small" style="color: var(--ps-muted);">Telepon (opsional)</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="50" placeholder="08…">
                        </div>
                        <div>
                            <label class="form-label small" style="color: var(--ps-muted);">Email (opsional)</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="255">
                        </div>
                        <div>
                            <label class="form-label small" style="color: var(--ps-muted);">Alamat (opsional)</label>
                            <textarea name="address" class="form-control" rows="2" maxlength="2000" placeholder="Alamat singkat">{{ old('address') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-modern-primary">Simpan supplier</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="table-responsive product-modern-table-wrap">
                    <table class="table align-middle product-modern-table mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kontak</th>
                                <th>Telepon</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $s)
                                <tr>
                                    <td class="fw-semibold" style="color: var(--ps-text);">{{ $s->name }}</td>
                                    <td style="color: var(--ps-muted);">{{ $s->contact_person ?? '—' }}</td>
                                    <td style="color: var(--ps-muted);">{{ $s->phone ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4" style="color: var(--ps-muted);">Belum ada supplier. Tambahkan di form kiri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
