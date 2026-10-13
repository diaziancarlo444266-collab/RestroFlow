import { useEffect, useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import DataTable from '../Components/DataTable';
import RecordForm from '../Components/RecordForm';
import { api } from '../lib/api';
import { titles, singular } from '../lib/restaurant';
export default function Restaurant() {
    const { auth, urls, initialPage = 'dashboard', initialEditor = null } = usePage().props;
    const isAdmin = auth.roles.includes('Admin');
    const isCustomer = auth.isCustomer;
    const can = action => isCustomer ? action === 'create' : auth.permissions.includes('records.' + action);
    const visibleTitles = Object.fromEntries(Object.entries(titles).filter(([key]) => key !== 'reservations' || isAdmin || isCustomer).filter(([key]) => key !== 'inventory' || !isCustomer));
    const [page, setPage] = useState(initialPage);
    const [menuOpen, setMenuOpen] = useState(false);
    const [search, setSearch] = useState('');
    const [rows, setRows] = useState([]);
    const [dashboard, setDashboard] = useState(null);
    const [lookups, setLookups] = useState(null);
    const [message, setMessage] = useState(null);
    const [loading, setLoading] = useState(true);
    const [loadError, setLoadError] = useState('');
    const [revision, setRevision] = useState(0);
    const [editor, setEditor] = useState(initialEditor);
    const [busy, setBusy] = useState(false);
    useEffect(() => {
        const controller = new AbortController();
        setLoading(true); setLoadError('');
        const timer = setTimeout(async () => {
            try {
                const options = { signal: controller.signal };
                if (page === 'dashboard') {
                    const [stats, orders, reservations] = await Promise.all([api('dashboard', options), api('orders', options), (isAdmin || isCustomer) ? api('reservations', options) : Promise.resolve({ data: [] })]);
                    setDashboard({ ...stats.data, recentOrders: orders.data.slice(0, 5), recentReservations: reservations.data.slice(0, 5) });
                } else { const result = await api(`${page}?search=${encodeURIComponent(search)}`, options); setRows(result.data); }
            } catch (error) { if (error.name !== 'AbortError') setLoadError(error.message); }
            finally { if (!controller.signal.aborted) setLoading(false); }
        }, search ? 250 : 0);
        return () => { clearTimeout(timer); controller.abort(); };
    }, [page, search, revision, isAdmin, isCustomer]);
    useEffect(() => {
        const controller = new AbortController();
        api('lookups', { signal: controller.signal }).then(result => setLookups(result.data)).catch(error => { if (error.name !== 'AbortError') setMessage({ error: true, text: 'Could not load menus and tables. Check the database, then refresh the page.' }); });
        return () => controller.abort();
    }, [revision]);
    function saved(text) { setMessage({ text }); setRevision(v => v + 1); }
    async function remove(record) {
        if (!window.confirm(`Delete this ${singular[page].toLowerCase()}?`)) return;
        setBusy(true);
        try { await api(`${page}/${record.id}`, { method: 'DELETE' }); saved(`${singular[page]} deleted successfully.`); }
        catch (error) { setMessage({ error: true, text: error.message }); }
        finally { setBusy(false); }
    }
    const add = module => setEditor({ module, record: null });
    return <><Head title={titles[page]} />
        <nav className="navbar navbar-dark navbar-expand-lg"><div className="container">
            <a className="navbar-brand" href="#" onClick={event => { event.preventDefault(); setPage('dashboard'); setSearch(''); }}><span className="brand-icon"><i aria-hidden="true" className="bi bi-cup-hot-fill" /></span><span>RestroFlow<small>Restaurant Manager</small></span></a>
            <button className="navbar-toggler" aria-label="Toggle navigation" aria-expanded={menuOpen} aria-controls="navbarMenu" onClick={() => setMenuOpen(!menuOpen)}><span className="navbar-toggler-icon" /></button>
            <div id="navbarMenu" className={`navbar-collapse collapse ${menuOpen ? 'show' : ''}`}><div className="navbar-nav ms-auto"><span className="nav-label">Workspace</span>{Object.entries(visibleTitles).map(([key, title]) => <button key={key} className={`nav-link ${page === key ? 'active' : ''}`} aria-current={page === key ? 'page' : undefined} onClick={() => { setPage(key); setSearch(''); setMenuOpen(false); }}>{!isAdmin && ['orders', 'reservations'].includes(key) ? `My ${title}` : title}</button>)}</div><div className="system-status"><span /><div><strong>{auth.user.name}</strong><small>{auth.roles.join(', ')}</small></div></div></div>
        <button className="btn btn-outline-light ms-3" onClick={() => router.post(urls.logout)}>Sign out</button></div></nav>
        <main className="container py-4">
            {message && <div role="alert" className={`alert alert-${message.error ? 'danger' : 'success'} alert-dismissible`}>{message.text}<button className="btn-close" aria-label="Dismiss message" onClick={() => setMessage(null)} /></div>}
            {page === 'dashboard' ? <div className="welcome-banner"><div><small>TODAY AT A GLANCE</small><h1>{isCustomer ? 'Place an order and track it here.' : 'Everything your restaurant needs.'}</h1><p>{isCustomer ? 'Order a meal, reserve a table, and track your requests.' : 'Track your restaurant workspace.'}</p></div>{can('create') && <button className="btn banner-button" disabled={!lookups} onClick={() => add('orders')}><i aria-hidden="true" className="bi bi-plus-lg" /> New Order</button>}</div> : <div className="page-heading d-flex justify-content-between align-items-center"><div><h2>{!isAdmin && ['orders', 'reservations'].includes(page) ? `My ${titles[page]}` : titles[page]}</h2><p>{page === 'inventory' ? 'Track the quantity of restaurant supplies.' : isCustomer ? `View your ${page} and add a new ${singular[page].toLowerCase()}.` : `Browse and manage ${page}.`}</p></div>{can('create') && <button className="btn btn-success" disabled={!lookups} onClick={() => add(page)}><i aria-hidden="true" className="bi bi-plus-lg" /> Add {singular[page]}</button>}</div>}
            {page !== 'dashboard' && <input className="form-control search-input mb-3" aria-label={`Search ${page}`} placeholder={page === 'orders' ? 'Search customer or menu item' : page === 'reservations' ? 'Search customer or phone' : 'Search item name'} maxLength={255} value={search} onChange={event => setSearch(event.target.value)} />}
            {loading ? <p role="status">Loading {titles[page].toLowerCase()}...</p> : loadError ? <div className="alert alert-danger" role="alert">{loadError} <button className="btn btn-sm btn-outline-danger" onClick={() => setRevision(v => v + 1)}>Retry</button></div> : page === 'dashboard' && dashboard ? <>
                <div className="row g-3 mb-4">{(isAdmin ? [['orders', 'Total Orders', 'receipt'], ['reservations', 'Reservations', 'calendar-check'], ['inventory', 'Inventory Items', 'box-seam'], ['low_stock', 'Low Stock', 'exclamation-triangle']] : isCustomer ? [['orders', 'My Orders', 'receipt'], ['reservations', 'My Reservations', 'calendar-check']] : [['orders', 'My Orders', 'receipt']]).map(([key, label, icon]) => <div key={key} className="col-sm-6 col-lg-3"><div className={`summary-card ${key === 'low_stock' ? 'warning' : ''}`}><i aria-hidden="true" className={`bi bi-${icon}`} /><span>{label}</span><strong>{dashboard[key]}</strong></div></div>)}</div>
                <div className="row g-3">{((isAdmin || isCustomer) ? [['orders', dashboard.recentOrders], ['reservations', dashboard.recentReservations]] : [['orders', dashboard.recentOrders]]).map(([module, records]) => <div key={module} className="col-12"><div className="card simple-card"><div className="card-body"><h5>{!isAdmin ? `My Recent ${titles[module]}` : `Recent ${titles[module]}`}</h5><DataTable module={module} rows={records} /></div></div></div>)}</div>
            </> : <div className="card simple-card"><div className="card-body"><DataTable module={page} rows={rows} busy={busy} onEdit={can('update') ? record => setEditor({ module: page, record }) : undefined} onDelete={can('delete') ? remove : undefined} /></div></div>}
        </main>
        <footer className="text-center py-4 text-muted"><small>Restaurant Management System - ITELEC 4100 Project</small></footer>
        {editor && lookups && <RecordForm editor={editor} lookups={lookups} onClose={() => setEditor(null)} onSaved={saved} />}
    </>;
}

