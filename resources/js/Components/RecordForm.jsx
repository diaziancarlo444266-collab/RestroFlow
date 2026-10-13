import { useEffect, useRef, useState } from 'react';
import { Modal } from 'bootstrap';
import { usePage } from '@inertiajs/react';
import { api } from '../lib/api';
import { money, singular } from '../lib/restaurant';
export default function RecordForm({ editor, lookups, onClose, onSaved }) {
    const { module, record } = editor;
    const { auth } = usePage().props;
    const customerRecord = auth.isCustomer && ['orders', 'reservations'].includes(module);
    const localDate = new Date().toLocaleDateString('en-CA');
    const [values, setValues] = useState({ customer_name: customerRecord ? auth.user.name : '', menu_item_id: '', table_id: '', quantity: module === 'orders' ? 1 : 0, status: 'Pending', phone: '', guests: 1, reservation_date: localDate, item_name: '', unit: '', reorder_level: 0, ...record, reservation_time: record?.reservation_time?.slice(0, 5) || '' });
    const [errors, setErrors] = useState({});
    const [error, setError] = useState('');
    const [saving, setSaving] = useState(false);
    const element = useRef(null);
    const modal = useRef(null);
    const close = useRef(onClose);
    close.current = onClose;
    useEffect(() => {
        modal.current = new Modal(element.current, { backdrop: 'static', keyboard: false });
        const node = element.current;
        const hidden = () => close.current();
        node.addEventListener('hidden.bs.modal', hidden);
        modal.current.show();
        return () => { node.removeEventListener('hidden.bs.modal', hidden); modal.current.dispose(); };
    }, []);
    function field(name, label, props = {}, choices) {
        const shared = { id: name, name, value: values[name] ?? '', onChange: event => setValues(v => ({ ...v, [name]: event.target.value })), required: true, ...props, 'aria-invalid': !!errors[name], 'aria-describedby': errors[name] ? `${name}-error` : undefined };
        return <div className="mb-3" key={name}><label className="form-label" htmlFor={name}>{label}</label>{choices ? <select className={`form-select ${errors[name] ? 'is-invalid' : ''}`} {...shared}>{choices.map(([value, text]) => <option key={value} value={value}>{text}</option>)}</select> : <input className={`form-control ${errors[name] ? 'is-invalid' : ''}`} {...shared} />}{errors[name] && <div id={`${name}-error`} className="invalid-feedback">{errors[name][0]}</div>}</div>;
    }
    async function save(event) {
        event.preventDefault(); setSaving(true); setError(''); setErrors({});
        try {
            await api(`${module}${record ? `/${record.id}` : ''}`, { method: record ? 'PUT' : 'POST', body: JSON.stringify(values) });
            onSaved(`${singular[module]} saved successfully.`);
            modal.current.hide();
        } catch (failure) { setError(failure.message); setErrors(failure.fields || {}); setSaving(false); }
    }
    const tables = [['', module === 'orders' ? 'No table / Takeout' : 'Select table'], ...lookups.tables.map(t => [t.id, `${t.table_number} (${t.capacity} seats)`])];
    const statuses = ['Pending', module === 'orders' ? 'Preparing' : 'Confirmed', 'Completed', 'Cancelled'].map(s => [s, s]);
    return <div className="modal fade" ref={element} tabIndex="-1" aria-labelledby="form-title"><div className="modal-dialog modal-dialog-centered"><div className="modal-content"><form onSubmit={save}>
        <div className="modal-header"><h5 className="modal-title" id="form-title">{record ? 'Edit' : 'Add'} {singular[module]}</h5><button type="button" className="btn-close" aria-label="Close" disabled={saving} onClick={() => modal.current.hide()} /></div>
        <div className="modal-body">{error && <div className="alert alert-danger" role="alert">{error}</div>}<fieldset disabled={saving}>
            {module !== 'inventory' && !customerRecord && field('customer_name', 'Customer Name', { maxLength: 100 })}
            {module === 'orders' && <>{field('menu_item_id', 'Menu Item', {}, [['', 'Select menu item'], ...lookups.menu.map(m => [m.id, `${m.name} - ${money(m.price)}`])])}{field('table_id', 'Table', { required: false }, tables)}{field('quantity', 'Quantity', { type: 'number', min: 1, max: 10000 })}</>}
            {module === 'reservations' && <>{field('phone', 'Phone Number', { type: 'tel', maxLength: 30 })}{field('table_id', 'Table', {}, tables)}{field('guests', 'Guests', { type: 'number', min: 1, max: 10000 })}{field('reservation_date', 'Date', { type: 'date' })}{field('reservation_time', 'Time', { type: 'time' })}</>}
            {module !== 'inventory' && !customerRecord && field('status', 'Status', {}, statuses)}
            {module === 'inventory' && <>{field('item_name', 'Item Name', { maxLength: 100 })}{field('quantity', 'Quantity', { type: 'number', min: 0, max: 999999.99, step: '0.01' })}{field('unit', 'Unit', { placeholder: 'kg / pcs', maxLength: 20 })}{field('reorder_level', 'Reorder At', { type: 'number', min: 0, max: 999999.99, step: '0.01' })}</>}
        </fieldset></div><div className="modal-footer"><button type="button" disabled={saving} className="btn btn-secondary" onClick={() => modal.current.hide()}>Cancel</button><button disabled={saving} className="btn btn-success">{saving ? 'Saving...' : `Save ${singular[module]}`}</button></div>
    </form></div></div></div>;
}

