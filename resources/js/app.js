import './bootstrap';
import * as bootstrap from 'bootstrap';
// Expose bootstrap to global window so inline scripts can access bootstrap.Modal
window.bootstrap = bootstrap;
import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import SalesAnalysis from './components/SalesAnalysis.jsx';

function mountSalesAnalysis() {
    const el = document.getElementById('sales-analysis-root');
    if (!el) return;
    const initialMonth = el.dataset.initialMonth || '';
    const root = createRoot(el);
    root.render(React.createElement(SalesAnalysis, { initialMonth }));
}

document.addEventListener('DOMContentLoaded', () => {
    mountSalesAnalysis();
});
