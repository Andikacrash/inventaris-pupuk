@extends('layouts.app')

@push('styles')
    <style>
        /*
         * Manajemen hutang — tabel padat, kontras lembut untuk penggunaan lama di layar
         * (hindari putih menyilaukan; teks tidak full #fff pada area besar).
         */
        .debt-premium-page {
            /* Selaraskan dengan panel / kartu dashboard (--ps-surface hijau gelap) */
            --debt-fg: var(--ps-text, #e8ede9);
            --debt-fg-soft: rgba(243, 248, 245, 0.88);
            --debt-muted: var(--ps-muted, rgba(255, 255, 255, 0.7));
            --debt-surface-2: var(--ps-surface, #1a3327);
            --debt-surface-head: #162e22;
            --debt-border: var(--ps-border, #2a4a37);
            font-family: var(--font-sans);
            color: var(--debt-fg);
        }

        .debt-premium-shell {
            background: var(--ps-surface);
            border: 1px solid var(--ps-border);
            border-radius: 16px;
            padding: 20px;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        body.debts-page .product-list-page.debt-premium-page {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            margin: 0;
            min-width: 0;
        }

        body.debts-page .ps-content {
            min-width: 0;
            overflow-x: hidden;
        }

        .debt-search-row {
            width: 100%;
        }

        .debt-search-wrap {
            flex: 1 1 280px;
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }

        .debt-premium-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .debt-premium-title {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            color: var(--debt-fg);
        }

        .debt-premium-subtitle {
            margin: 6px 0 0;
            color: var(--debt-muted);
            font-size: 1rem;
            line-height: 1.55;
            font-weight: 500;
            max-width: 42rem;
        }

        .debt-filter-pills {
            display: inline-flex;
            background: var(--debt-surface-2);
            border: 1px solid var(--debt-border);
            border-radius: 999px;
            padding: 4px;
            gap: 4px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .debt-filter-pill {
            border: 0;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 999px;
            font-size: 0.95rem;
            color: var(--debt-muted);
            transition: background 0.15s ease, color 0.15s ease;
            line-height: 1.2;
        }

        .debt-filter-pill:hover {
            color: var(--debt-fg);
        }

        .debt-filter-pill.active {
            background: var(--debt-accent-soft, rgba(64, 145, 108, 0.28));
            color: var(--debt-accent-fg, #dcfce7);
            font-weight: 700;
        }

        .debt-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid var(--debt-border);
            background: var(--debt-surface-head);
            margin-bottom: 12px;
        }

        .debt-toolbar-left,
        .debt-toolbar-right {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .debt-toolbar-label {
            font-size: 0.95rem;
            color: var(--debt-fg);
            font-weight: 700;
            white-space: nowrap;
        }

        .debt-toolbar-note {
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--debt-muted);
            font-weight: 500;
        }

        .debt-toolbar-note strong {
            color: var(--debt-fg);
            font-weight: 700;
        }

        /* Kontrol: gaya elegan (aksen hijau tipis, tidak ngejreng) */
        .debt-view-toggle,
        .debt-filter-pills {
            /* Grup tombol: netral (bukan hijau) */
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.10);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .debt-view-toggle a,
        .debt-filter-pill {
            position: relative;
            padding: 10px 16px;
            font-size: 0.95rem;
            color: var(--debt-muted);
        }

        .debt-view-toggle a.active,
        .debt-filter-pill.active {
            background: var(--debt-accent-soft, rgba(255, 255, 255, 0.06));
            color: var(--debt-accent-fg, #ffffff);
        }

        .debt-view-toggle a.active::after,
        .debt-filter-pill.active::after {
            content: '';
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 6px;
            height: 2px;
            border-radius: 999px;
            background: rgba(64, 145, 108, 0.75);
            box-shadow: 0 0 0 1px rgba(64, 145, 108, 0.18);
        }

        .debt-view-toggle a:hover,
        .debt-filter-pill:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        /* Fokus keyboard/touch: elegan tapi jelas */
        .debt-view-toggle a:focus-visible,
        .debt-filter-pill:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.16);
        }

        .debt-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 16px;
            border-radius: 12px;
            background: var(--debt-surface-2);
            border: 1px solid var(--debt-border);
        }

        .debt-kpi-item {
            padding: 12px 14px;
        }

        .debt-kpi-item:not(:last-child) {
            border-right: 1px solid var(--debt-border);
        }

        .debt-kpi-label {
            font-size: 0.9rem;
            color: var(--debt-muted);
            margin-bottom: 6px;
            font-weight: 600;
            line-height: 1.35;
        }

        .debt-kpi-value {
            font-family: var(--font-sans);
            font-variant-numeric: tabular-nums;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--debt-fg);
            line-height: 1.3;
        }

        .debt-kpi-hint {
            display: block;
            margin-top: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--debt-muted);
        }

        .debt-duplicate-warning {
            border: 1px solid rgba(245, 158, 11, 0.45);
            color: #fcd34d;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.9rem;
            line-height: 1.45;
            margin-bottom: 14px;
        }

        .debt-view-toggle {
            display: inline-flex;
            gap: 4px;
            background: var(--debt-surface-2);
            border: 1px solid var(--debt-border);
            border-radius: 999px;
            padding: 4px;
            margin-bottom: 12px;
        }

        .debt-view-toggle a {
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.85rem;
            text-decoration: none;
            color: var(--debt-muted);
        }

        .debt-view-toggle a.active {
            background: rgba(64, 145, 108, 0.22);
            color: #b8e6c8;
            font-weight: 600;
        }

        .debt-table-wrap {
            border: 1px solid var(--debt-border);
            border-radius: 12px;
            /* jangan hidden: bisa memotong kolom kanan (tombol aksi) setelah layout settle */
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            background: var(--debt-surface-2);
        }

        /*
         * Bootstrap 5 mengisi sel dari --bs-body-bg (putih). Timpa penuh agar sama dengan tema gelap app.
         */
        .debt-data-table.table {
            margin-bottom: 0;
            font-size: 0.95rem;
            --bs-table-bg: var(--debt-surface-2);
            --bs-table-color: var(--debt-fg);
            --bs-table-border-color: var(--debt-border);
            --bs-table-striped-bg: rgba(64, 145, 108, 0.05);
            --bs-table-hover-bg: rgba(64, 145, 108, 0.1);
            --bs-table-active-bg: rgba(64, 145, 108, 0.12);
            background-color: var(--debt-surface-2) !important;
            color: var(--debt-fg) !important;
            border-color: var(--debt-border) !important;
        }

        .debt-data-table.table > :not(caption) > * > * {
            padding: 12px 14px;
            vertical-align: middle;
            background-color: var(--debt-surface-2) !important;
            color: var(--debt-fg) !important;
            border-bottom-color: var(--debt-border) !important;
            box-shadow: none !important;
        }

        .debt-data-table thead th {
            background-color: var(--debt-surface-head) !important;
            color: var(--debt-fg) !important;
            font-weight: 700;
            font-size: 0.92rem;
            text-transform: none;
            letter-spacing: 0;
            white-space: nowrap;
            border-color: var(--debt-border) !important;
        }

        .debt-data-table tbody tr:last-child > * {
            border-bottom-color: transparent !important;
        }

        .debt-data-table tbody tr:hover > * {
            background-color: rgba(64, 145, 108, 0.1) !important;
        }

        .debt-cell-strong {
            font-weight: 600;
            color: var(--debt-fg);
        }

        .debt-status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
            border: 1px solid transparent;
            white-space: nowrap;
            line-height: 1.25;
        }

        /* Warna gelap di atas latar terang — kontras WCAG di tema warm */
        .debt-status-badge.unpaid {
            color: #991b1b !important;
            background: #fee2e2 !important;
            border-color: #f87171 !important;
        }

        .debt-status-badge.partial {
            color: #92400e !important;
            background: #fef3c7 !important;
            border-color: #f59e0b !important;
        }

        .debt-status-badge.paid {
            color: #166534 !important;
            background: #dcfce7 !important;
            border-color: #4ade80 !important;
        }

        .debt-btn-detail {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid rgba(64, 145, 108, 0.45);
            background: rgba(64, 145, 108, 0.12);
            color: #b8e6c8;
            text-decoration: none;
            white-space: nowrap;
        }

        .debt-btn-detail:hover {
            color: #dcfce7;
            border-color: #40916c;
            background: rgba(64, 145, 108, 0.2);
        }

        button.debt-btn-expand {
            appearance: none;
            -webkit-appearance: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            border: 1.5px solid var(--debt-accent, #1a5c42);
            background: var(--debt-accent, #1a5c42);
            color: var(--debt-btn-fg, #f8faf8) !important;
            -webkit-text-fill-color: var(--debt-btn-fg, #f8faf8);
            text-shadow: none;
            opacity: 1 !important;
            visibility: visible !important;
            transition: background 0.15s ease, border-color 0.15s ease;
            position: relative;
            z-index: 2;
            cursor: pointer;
            white-space: nowrap;
        }

        .debt-data-table td {
            opacity: 1 !important;
        }

        .debt-data-table td .debt-btn-expand {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .debt-group-collapse.is-open {
            display: block !important;
        }

        button.debt-btn-expand:hover {
            background: var(--debt-accent-hover, #144a35);
            border-color: var(--debt-accent-hover, #144a35);
            color: var(--debt-btn-fg, #f8faf8) !important;
            -webkit-text-fill-color: var(--debt-btn-fg, #f8faf8);
        }

        button.debt-btn-expand:focus,
        button.debt-btn-expand:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.22);
        }

        .debt-group-expand-cell {
            padding: 0 !important;
            border-bottom: 1px solid var(--debt-border) !important;
            background: var(--debt-surface-head) !important;
            position: relative;
            z-index: 1;
        }

        .debt-group-expand-inner {
            padding: 16px 18px;
            border-top: 1px solid var(--debt-border);
            background: var(--debt-surface-head);
            color: var(--debt-fg) !important;
        }

        .debt-group-expand-inner * {
            color: inherit;
        }

        /* Pastikan tombol aksi tidak ketutup row collapse */
        .debt-action-cell {
            position: relative;
            z-index: 3;
            background-color: var(--debt-surface-2) !important;
        }

        .debt-action-cell button.debt-btn-expand {
            position: relative;
            z-index: 4;
        }

        .debt-group-invoices {
            border-radius: 10px;
            border: 1px solid var(--debt-border);
            overflow: hidden;
            font-size: 0.88rem;
            background: var(--debt-surface-2);
        }

        .debt-group-invoices table {
            width: 100%;
            border-collapse: collapse;
            color: var(--debt-fg);
            background: var(--debt-surface-2);
        }

        .debt-group-invoices th,
        .debt-group-invoices td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid var(--debt-border);
            background-color: var(--debt-surface-2) !important;
            color: var(--debt-fg) !important;
        }

        .debt-group-invoices th {
            background-color: var(--debt-surface-head) !important;
            color: var(--debt-muted) !important;
            font-weight: 600;
        }

        .debt-group-invoices tr:last-child td {
            border-bottom: 0;
        }

        .debt-group-meta {
            font-size: 0.88rem;
            color: var(--debt-muted);
            margin-bottom: 10px;
        }

        .debt-group-hint {
            font-size: 0.95rem;
            color: var(--debt-muted);
            line-height: 1.55;
            margin-top: 8px;
            font-weight: 500;
        }

        .debt-group-hint strong {
            color: var(--debt-fg);
            font-weight: 700;
        }

        .debt-bulk-form {
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--debt-border);
            display: grid;
            gap: 10px;
        }

        .debt-bulk-form label {
            font-size: 0.8rem;
            color: var(--debt-muted);
            display: block;
            margin-bottom: 4px;
        }

        .debt-bulk-form input,
        .debt-bulk-form select {
            width: 100%;
            max-width: 280px;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--debt-border);
            background: var(--debt-surface-head);
            color: var(--debt-fg);
            font-size: 0.9rem;
        }

        .debt-bulk-submit {
            padding: 8px 16px;
            border-radius: 999px;
            border: none;
            background: #40916c;
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            width: fit-content;
        }

        .debt-bulk-submit:hover {
            background: #2f6f52;
        }

        .debt-empty-cell {
            text-align: center;
            padding: 2.5rem 1rem !important;
            color: var(--debt-muted);
        }

        .debt-warn-text {
            color: var(--debt-warn, #ba7517);
            font-weight: 600;
        }

        /* Search */
        .debt-search {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1.5px solid var(--debt-border);
            background: var(--debt-surface-head);
            min-height: 48px;
        }

        .debt-search-ico {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--debt-accent-soft, rgba(64, 145, 108, 0.12));
            border: 1px solid var(--debt-border);
            color: var(--debt-muted);
            flex: 0 0 auto;
        }

        .debt-search input[type='search'] {
            border: 0 !important;
            background: transparent !important;
            color: var(--debt-fg) !important;
            box-shadow: none !important;
            padding: 0 !important;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 0.01em;
        }

        .debt-search input[type='search']::placeholder {
            color: var(--debt-muted);
            opacity: 0.9;
        }

        .debt-search-clear {
            border: 1px solid var(--debt-border);
            background: var(--debt-surface-2);
            color: var(--debt-fg);
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            cursor: pointer;
        }

        .debt-search-clear:hover {
            border-color: var(--debt-accent, #40916c);
            color: var(--debt-accent, #40916c);
        }

        .debt-search:focus-within {
            border-color: var(--debt-accent, #40916c);
            box-shadow: 0 0 0 3px rgba(64, 145, 108, 0.18);
        }

        /* Pagination (khusus halaman hutang) */
        .debt-premium-page .product-pagination .pagination {
            gap: 6px;
            margin: 0;
        }

        .debt-premium-page .product-pagination .page-link {
            background: var(--debt-surface-head) !important;
            border: 1px solid var(--debt-border) !important;
            color: var(--debt-fg) !important;
            border-radius: 10px !important;
            min-width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: none !important;
        }

        .debt-premium-page .product-pagination .page-link:hover {
            background: var(--debt-surface-2) !important;
            border-color: rgba(64, 145, 108, 0.55) !important;
            color: var(--debt-fg) !important;
        }

        .debt-premium-page .product-pagination .page-item.active .page-link {
            background: rgba(64, 145, 108, 0.22) !important;
            border-color: rgba(64, 145, 108, 0.75) !important;
            color: #dcfce7 !important;
            font-weight: 700;
        }

        .debt-premium-page .product-pagination .page-item.disabled .page-link {
            opacity: 0.45;
        }

        @media (max-width: 991.98px) {
            body.debts-page .ps-content {
                padding: 12px !important;
            }

            .debt-premium-shell {
                padding: 14px 12px;
                border-radius: 12px;
            }

            body.debts-page .product-list-page.debt-premium-page {
                padding: 12px;
                border-radius: 12px;
            }

            .debt-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .debt-kpi-item:nth-child(2n) {
                border-right: 0;
            }

            .debt-kpi-item:nth-child(1),
            .debt-kpi-item:nth-child(2) {
                border-bottom: 1px solid var(--debt-border);
            }

            .debt-toolbar-right {
                flex-wrap: wrap;
            }

            .debt-filter-pills,
            .debt-view-toggle {
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                flex-wrap: nowrap;
                justify-content: flex-start;
            }
        }

        @media (max-width: 767.98px) {
            .debt-premium-title {
                font-size: 1.35rem;
            }

            .debt-premium-subtitle {
                font-size: 0.92rem;
            }

            .debt-premium-header {
                flex-direction: column;
                align-items: stretch;
            }

            .debt-filter-pills {
                justify-content: flex-start;
            }

            .debt-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
            }

            .debt-toolbar-left,
            .debt-toolbar-right {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
                gap: 8px;
            }

            .debt-toolbar-label {
                white-space: normal;
            }

            .debt-view-toggle,
            .debt-filter-pills {
                width: 100%;
            }

            .debt-search-row {
                flex-direction: column;
                align-items: stretch !important;
            }

            .debt-search-wrap {
                width: 100% !important;
                max-width: 100% !important;
            }

            .debt-kpi-value {
                font-size: 1.05rem;
            }

            .debt-pagination-bar {
                flex-direction: column;
                align-items: stretch !important;
                text-align: center;
            }

            .debt-pagination-bar .product-pagination {
                display: flex;
                justify-content: center;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Tabel → kartu per baris di HP */
            .debt-table-wrap--responsive {
                overflow: visible;
                border: none;
                background: transparent;
            }

            .debt-table-wrap--responsive .debt-data-table {
                min-width: 0 !important;
            }

            .debt-table-wrap--responsive .debt-data-table thead {
                display: none;
            }

            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='txn'],
            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='group'] {
                display: block;
                margin-bottom: 12px;
                border: 1.5px solid var(--debt-border);
                border-radius: 12px;
                overflow: hidden;
                background: var(--debt-surface-2) !important;
                box-shadow: 0 1px 4px rgba(60, 48, 32, 0.06);
            }

            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='group-detail'] {
                display: block;
                margin: -4px 0 12px;
                border: none;
                background: transparent !important;
            }

            .debt-table-wrap--responsive .debt-data-table tbody tr:has(.debt-empty-cell) {
                display: block;
                border: 1.5px dashed var(--debt-border);
                border-radius: 12px;
            }

            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='txn'] > td,
            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='group'] > td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                padding: 10px 14px !important;
                border-bottom: 1px solid var(--debt-border) !important;
                text-align: right !important;
                background: var(--debt-surface-2) !important;
            }

            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='txn'] > td:last-child,
            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='group'] > td:last-child {
                border-bottom: none !important;
            }

            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='txn'] > td::before,
            .debt-table-wrap--responsive .debt-data-table tbody tr[data-debt-row='group'] > td::before {
                content: attr(data-label);
                font-weight: 700;
                font-size: 0.7rem;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: var(--debt-muted) !important;
                text-align: left;
                flex: 1 1 auto;
                padding-right: 8px;
            }

            .debt-table-wrap--responsive .debt-group-expand-cell {
                display: block !important;
                padding: 0 !important;
            }

            .debt-table-wrap--responsive .debt-group-expand-cell::before {
                display: none !important;
            }

            .debt-table-wrap--responsive .debt-empty-cell {
                display: block !important;
                text-align: center !important;
                padding: 2rem 1rem !important;
            }

            .debt-table-wrap--responsive .debt-empty-cell::before {
                display: none !important;
            }

            .debt-btn-expand,
            .debt-btn-detail {
                width: 100%;
                justify-content: center;
            }

            .debt-bulk-form input,
            .debt-bulk-form select {
                max-width: 100%;
            }
        }

        /* Tablet: tabel geser horizontal, tetap memakai lebar penuh */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .debt-table-wrap--responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border: 1px solid var(--debt-border);
                border-radius: 12px;
            }

            .debt-table-wrap--responsive .debt-data-table {
                min-width: 880px;
            }
        }

        @media (max-width: 575.98px) {
            body.debts-page .ps-content {
                padding: 8px !important;
            }

            .debt-premium-shell {
                padding: 12px 10px;
            }

            body.debts-page .product-list-page.debt-premium-page {
                padding: 10px 8px;
            }

            .debt-kpi-grid {
                grid-template-columns: 1fr;
            }

            .debt-kpi-item {
                border-right: 0 !important;
                border-bottom: 1px solid var(--debt-border);
            }

            .debt-kpi-item:last-child {
                border-bottom: 0;
            }

            .debt-filter-pill,
            .debt-view-toggle a {
                padding: 8px 12px;
                font-size: 0.85rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $activeStatus = request('status', '');
        $statusTabs = [
            '' => 'Semua',
            'unpaid' => 'Belum lunas',
            'partial' => 'Bayar sebagian',
            'paid' => 'Sudah lunas',
        ];
        $currentView = $view ?? 'transactions';

        $duplicateNames = collect();
        if (($debts ?? null) && $debts->count()) {
            $normalizedNames = $debts
                ->pluck('customer_name')
                ->filter()
                ->map(fn($name) => strtolower(trim($name)));
            $duplicateNames = $normalizedNames->duplicates()->unique()->values();
        }
    @endphp
    <div class="product-list-page debt-premium-page">
        <div class="debt-premium-shell">
            <div class="debt-premium-header">
                <div>
                    <h2 class="debt-premium-title">Hutang Pelanggan</h2>
                    <p class="debt-premium-subtitle">Kelola piutang pelanggan: cari nama atau nomor HP, lihat sisa hutang per pelanggan atau per nota, lalu catat pembayaran.</p>
                </div>
            </div>

            <div class="debt-toolbar" aria-label="Kontrol tampilan dan filter hutang">
                <div class="debt-toolbar-left">
                    <span class="debt-toolbar-label">Tampilan</span>
                    <div class="debt-view-toggle mb-0">
                        <a href="{{ route('debts.index', array_filter(['view' => 'grouped', 'customer' => request('customer'), 'overdue' => request('overdue')], fn($v) => $v !== null && $v !== '')) }}"
                            class="{{ $currentView === 'grouped' ? 'active' : '' }}">Per pelanggan</a>
                        <a href="{{ route('debts.index', array_filter(['view' => 'transactions', 'status' => $activeStatus, 'customer' => request('customer'), 'overdue' => request('overdue')], fn($v) => $v !== null && $v !== '')) }}"
                            class="{{ $currentView === 'transactions' ? 'active' : '' }}">Per nota</a>
                    </div>
                </div>
                <div class="debt-toolbar-right">
                    @if ($currentView === 'transactions')
                        <span class="debt-toolbar-label">Tampilkan</span>
                        <div class="debt-filter-pills" role="tablist" aria-label="Tampilkan status hutang">
                            @foreach ($statusTabs as $statusValue => $statusLabel)
                                <a href="{{ route('debts.index', array_filter(['status' => $statusValue, 'customer' => request('customer'), 'view' => 'transactions'], fn($val) => $val !== null && $val !== '')) }}"
                                    class="debt-filter-pill {{ $activeStatus === $statusValue ? 'active' : '' }}">
                                    {{ $statusLabel }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="debt-toolbar-note mb-0">
                            Menampilkan hutang <strong>aktif</strong> (belum lunas atau bayar sebagian).
                        </p>
                    @endif
                </div>
            </div>

            <div class="debt-kpi-grid">
                <div class="debt-kpi-item">
                    <div class="debt-kpi-label">Total piutang aktif</div>
                    <div class="debt-kpi-value">Rp {{ number_format($summary['total_debt'], 0, ',', '.') }}</div>
                    <span class="debt-kpi-hint">Jumlah sisa hutang belum lunas</span>
                </div>
                <div class="debt-kpi-item">
                    <div class="debt-kpi-label">Belum lunas</div>
                    <div class="debt-kpi-value">{{ $summary['unpaid_count'] }}</div>
                    <span class="debt-kpi-hint">Pelanggan belum pernah bayar</span>
                </div>
                <div class="debt-kpi-item">
                    <div class="debt-kpi-label">Bayar sebagian</div>
                    <div class="debt-kpi-value">{{ $summary['partial_count'] }}</div>
                    <span class="debt-kpi-hint">Sudah bayar, masih ada sisa</span>
                </div>
                <div class="debt-kpi-item">
                    <div class="debt-kpi-label">Sudah lunas</div>
                    <div class="debt-kpi-value">{{ $summary['paid_count'] }}</div>
                    <span class="debt-kpi-hint">Tidak ada sisa hutang</span>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            @if ($duplicateNames->isNotEmpty())
                <div class="debt-duplicate-warning">
                    Nama pelanggan terdeteksi duplikat: {{ $duplicateNames->map(fn($name) => ucwords($name))->join(', ') }}.
                    Pastikan data identitas tidak tertukar — utamakan isi <strong>nomor HP sama</strong> di kasir agar sistem
                    menggabungkan piutang satu orang.
                </div>
            @endif

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 debt-search-row">
                <p class="debt-group-hint mb-0 flex-grow-1" style="max-width: 720px;">
                    Klik tombol <strong>Lihat &amp; Catat Bayar</strong> pada baris pelanggan untuk mencatat pembayaran.
                </p>
                <div class="debt-search-wrap">
                    <div class="debt-search" role="search">
                        <span class="debt-search-ico" aria-hidden="true">
                            <i class="fas fa-magnifying-glass"></i>
                        </span>
                        <input id="debt-search" type="search" class="form-control"
                            placeholder="Cari nama pelanggan, nomor HP, atau nomor nota" autocomplete="off" />
                        <button type="button" class="debt-search-clear" id="debt-search-clear" aria-label="Hapus pencarian">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            @if ($currentView === 'grouped' && $customerGroups !== null)
                <p class="debt-group-hint mb-3">
                    <strong>Cara mengelompokkan pelanggan:</strong> sistem menggabungkan data berdasarkan <em>nama + nomor HP</em>.
                    Jika HP kosong, hanya nama yang dipakai. Pembayaran akan mengurangi sisa hutang mulai dari nota yang paling mendesak.
                </p>
            @endif

            @if ($currentView === 'grouped' && $customerGroups !== null)
                <div class="table-responsive debt-table-wrap debt-table-wrap--responsive">
                    <table class="table debt-data-table">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>No. HP</th>
                                <th class="text-center">Nota aktif</th>
                                <th class="text-end">Total sisa</th>
                                <th class="text-end">Progres lunas</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customerGroups as $group)
                                @php
                                    $gPayPct =
                                        $group['total_remaining'] > 0 && $group['debts']->sum('total_amount') > 0
                                            ? min(
                                                100,
                                                (1 - $group['total_remaining'] / $group['debts']->sum('total_amount')) * 100,
                                            )
                                            : 0;

                                    $invoiceHaystack = $group['debts']
                                        ->map(fn($d) => $d->sale->invoice_number ?? ('#'.$d->id))
                                        ->filter()
                                        ->implode(' ');
                                    $rowHaystack = mb_strtolower(
                                        trim(($group['customer_name'] ?? '').' '.($group['customer_phone'] ?? '').' '.$invoiceHaystack),
                                        'UTF-8',
                                    );
                                @endphp
                                <tr data-debt-row="group" data-search="{{ $rowHaystack }}">
                                    <td class="debt-cell-strong" data-label="Pelanggan">{{ $group['customer_name'] }}</td>
                                    <td data-label="No. HP">
                                        @if ($group['customer_phone'])
                                            {{ $group['customer_phone'] }}
                                        @else
                                            <span class="debt-warn-text">— isi HP di transaksi berikutnya</span>
                                        @endif
                                    </td>
                                    <td class="text-center" data-label="Nota aktif">{{ $group['invoice_count'] }}</td>
                                    <td class="text-end debt-cell-strong font-monospace" data-label="Total sisa">
                                        Rp {{ number_format($group['total_remaining'], 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace" data-label="Progres">{{ number_format($gPayPct, 0) }}%</td>
                                    <td class="text-end debt-action-cell" data-label="Aksi">
                                        <button type="button" class="debt-btn-expand" data-debt-toggle="#debt-grp-{{ $loop->index }}"
                                            aria-expanded="false" aria-controls="debt-grp-{{ $loop->index }}">Lihat &amp; Catat Bayar</button>
                                    </td>
                                </tr>
                                <tr data-debt-row="group-detail">
                                    <td colspan="6" class="debt-group-expand-cell">
                                        <div class="debt-group-collapse" id="debt-grp-{{ $loop->index }}" style="display:none;">
                                            <div class="debt-group-expand-inner">
                                                <p class="debt-group-meta mb-2">
                                                    Total sisa gabungan:
                                                    <strong class="debt-cell-strong">Rp
                                                        {{ number_format($group['total_remaining'], 0, ',', '.') }}</strong>
                                                </p>
                                                <div class="debt-group-invoices">
                                                    <table>
                                                        <thead>
                                                            <tr>
                                                                <th>Nota</th>
                                                                <th>Jatuh tempo</th>
                                                                <th class="text-end">Sisa</th>
                                                                <th class="text-end"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($group['debts'] as $d)
                                                                <tr>
                                                                    <td>{{ $d->sale->invoice_number ?? '—' }}</td>
                                                                    <td>{{ $d->due_date ? $d->due_date->format('d/m/Y') : '—' }}
                                                                    </td>
                                                                    <td class="text-end font-monospace">Rp
                                                                        {{ number_format($d->remaining_amount, 0, ',', '.') }}
                                                                    </td>
                                                                    <td class="text-end"><a
                                                                            href="{{ route('debts.show', $d) }}"
                                                                            class="debt-btn-detail">Lihat</a></td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <form class="debt-bulk-form" method="POST"
                                                    action="{{ route('debts.bulk-payment') }}">
                                                    @csrf
                                                    @foreach ($group['debt_ids'] as $did)
                                                        <input type="hidden" name="debt_ids[]" value="{{ $did }}">
                                                    @endforeach
                                                    <div>
                                                        <label for="bulk-amount-{{ $loop->index }}">Nominal
                                                            cicilan (Rp)</label>
                                                        <input id="bulk-amount-{{ $loop->index }}" name="amount"
                                                            type="number" step="0.01" min="0.01"
                                                            max="{{ $group['total_remaining'] }}" required
                                                            placeholder="Contoh: 500000">
                                                    </div>
                                                    <div>
                                                        <label for="bulk-date-{{ $loop->index }}">Tanggal
                                                            bayar</label>
                                                        <input id="bulk-date-{{ $loop->index }}"
                                                            name="payment_date" type="date"
                                                            value="{{ now()->format('Y-m-d') }}" required>
                                                    </div>
                                                    <div>
                                                        <label for="bulk-method-{{ $loop->index }}">Metode</label>
                                                        <select id="bulk-method-{{ $loop->index }}"
                                                            name="payment_method" required>
                                                            <option value="cash">Tunai</option>
                                                            <option value="transfer">Transfer</option>
                                                            <option value="card">Kartu</option>
                                                        </select>
                                                    </div>
                                                    <div style="max-width: 400px;">
                                                        <label for="bulk-notes-{{ $loop->index }}">Catatan
                                                            (opsional)</label>
                                                        <input id="bulk-notes-{{ $loop->index }}" name="notes"
                                                            type="text" placeholder="Mis. transfer dari BCA">
                                                    </div>
                                                    <button type="submit" class="debt-bulk-submit">Catat pembayaran
                                                        gabungan</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="debt-empty-cell">
                                        <i class="fas fa-inbox fa-2x mb-3 opacity-50 d-block"></i>
                                        Tidak ada hutang aktif untuk ditampilkan per pelanggan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @php
                    $isPaged = is_object($customerGroups) && method_exists($customerGroups, 'total');
                    $totalCustomers = $isPaged ? (int) $customerGroups->total() : (is_countable($customerGroups) ? count($customerGroups) : 0);
                    $from = $isPaged ? (int) $customerGroups->firstItem() : 0;
                    $to = $isPaged ? (int) $customerGroups->lastItem() : 0;
                @endphp
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 debt-pagination-bar">
                    <div class="small" style="color: var(--debt-muted);">
                        @if ($isPaged && $totalCustomers > 0)
                            Menampilkan <strong style="color: var(--debt-fg-soft);">{{ $from }}</strong>–<strong
                                style="color: var(--debt-fg-soft);">{{ $to }}</strong> dari <strong
                                style="color: var(--debt-fg-soft);">{{ $totalCustomers }}</strong> pelanggan
                        @elseif ($totalCustomers > 0)
                            Menampilkan <strong style="color: var(--debt-fg-soft);">{{ $totalCustomers }}</strong> pelanggan
                        @endif
                    </div>
                    @if ($isPaged)
                        <div class="product-pagination pt-0">
                            {{ $customerGroups->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            @else
                <div class="table-responsive debt-table-wrap debt-table-wrap--responsive">
                    <table class="table debt-data-table">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>No. HP</th>
                                <th>Nota</th>
                                <th>Jatuh tempo</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Dibayar</th>
                                <th class="text-end">Sisa</th>
                                <th class="text-end">Progres</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($debts as $debt)
                                @php
                                    $payPct = $debt->total_amount > 0 ? min(100, ($debt->paid_amount / $debt->total_amount) * 100) : 0;
                                    $statusClass = match ($debt->status) {
                                        'paid' => 'paid',
                                        'partial' => 'partial',
                                        default => 'unpaid',
                                    };
                                    $statusLabel = match ($debt->status) {
                                        'paid' => 'Lunas',
                                        'partial' => 'Bayar sebagian',
                                        default => 'Belum Bayar',
                                    };

                                    $invoiceNo = $debt->sale->invoice_number ?? ('#'.$debt->id);
                                    $rowHaystack = mb_strtolower(
                                        trim(($debt->customer_name ?? '').' '.($debt->customer_phone ?? '').' '.$invoiceNo),
                                        'UTF-8',
                                    );
                                @endphp
                                <tr data-debt-row="txn" data-search="{{ $rowHaystack }}">
                                    <td class="debt-cell-strong" data-label="Pelanggan">{{ $debt->customer_name }}</td>
                                    <td data-label="No. HP">{{ $debt->customer_phone ?: '—' }}</td>
                                    <td class="font-monospace" data-label="Nota">{{ $invoiceNo }}</td>
                                    <td class="font-monospace" data-label="Jatuh tempo">
                                        {{ $debt->due_date ? $debt->due_date->format('d/m/Y') : '—' }}</td>
                                    <td data-label="Status"><span class="debt-status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td class="text-end font-monospace" data-label="Total">Rp
                                        {{ number_format($debt->total_amount, 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace" data-label="Dibayar">Rp
                                        {{ number_format($debt->paid_amount, 0, ',', '.') }}</td>
                                    <td class="text-end debt-cell-strong font-monospace" data-label="Sisa">Rp
                                        {{ number_format($debt->remaining_amount, 0, ',', '.') }}</td>
                                    <td class="text-end font-monospace" data-label="Progres">{{ number_format($payPct, 0) }}%</td>
                                    <td class="text-end" data-label="Aksi">
                                        <a href="{{ route('debts.show', $debt) }}" class="debt-btn-detail">Lihat</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="debt-empty-cell">
                                        <i class="fas fa-inbox fa-2x mb-3 opacity-50 d-block"></i>
                                        Tidak ada data hutang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($currentView === 'transactions' && $debts->hasPages())
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 debt-pagination-bar">
                    <div class="small" style="color: var(--debt-muted);">
                        Menampilkan <strong style="color: var(--debt-fg-soft);">{{ $debts->firstItem() }}</strong>–<strong
                            style="color: var(--debt-fg-soft);">{{ $debts->lastItem() }}</strong> dari <strong
                            style="color: var(--debt-fg-soft);">{{ $debts->total() }}</strong> faktur
                    </div>
                    <div class="product-pagination pt-0">
                        {{ $debts->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-debt-toggle]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const sel = btn.getAttribute('data-debt-toggle') || '';
                    const target = sel ? document.querySelector(sel) : null;
                    if (!target) return;

                    const isOpen = target.classList.contains('is-open');
                    target.classList.toggle('is-open', !isOpen);
                    btn.setAttribute('aria-expanded', String(!isOpen));
                });
            });

            const searchInput = document.getElementById('debt-search');
            if (searchInput) {
                const clearBtn = document.getElementById('debt-search-clear');
                if (clearBtn) clearBtn.style.display = 'none';

                const applyFilter = () => {
                    const q = (searchInput.value || '').trim().toLowerCase();
                    if (!q) {
                        document.querySelectorAll('[data-debt-row]').forEach((row) => {
                            row.style.display = '';
                        });
                        if (clearBtn) clearBtn.style.display = 'none';
                        return;
                    }
                    if (clearBtn) clearBtn.style.display = '';

                    // Grouped view: hide/show pair rows (main + detail)
                    const groupRows = Array.from(document.querySelectorAll('tr[data-debt-row=\"group\"]'));
                    if (groupRows.length) {
                        groupRows.forEach((row) => {
                            const hay = (row.getAttribute('data-search') || '').toLowerCase();
                            const show = hay.includes(q);
                            row.style.display = show ? '' : 'none';
                            const detailRow = row.nextElementSibling;
                            if (detailRow && detailRow.getAttribute('data-debt-row') === 'group-detail') {
                                detailRow.style.display = show ? '' : 'none';
                            }
                        });
                        return;
                    }

                    // Transactions view: filter rows on current page
                    document.querySelectorAll('tr[data-debt-row=\"txn\"]').forEach((row) => {
                        const hay = (row.getAttribute('data-search') || '').toLowerCase();
                        row.style.display = hay.includes(q) ? '' : 'none';
                    });
                };

                searchInput.addEventListener('input', applyFilter);
                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        searchInput.value = '';
                        searchInput.focus();
                        applyFilter();
                    });
                }
                applyFilter();
            }
        });
    </script>
@endpush
