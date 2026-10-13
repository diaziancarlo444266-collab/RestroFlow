import { columns, money, singular, titles } from '../lib/restaurant';
export default function DataTable({ module, rows, onEdit, onDelete, busy }) {
    return <div className="table-responsive"><table className="table table-hover align-middle">
        <thead><tr>{columns[module].map(([key, label]) => <th key={key}>{label}</th>)}{(onEdit || onDelete) && <th>Action</th>}</tr></thead>
        <tbody>{rows.length ? rows.map(row => <tr key={row.id}>
            {columns[module].map(([key]) => <td key={key}>{key === 'total_amount' ? money(row[key]) : key === 'stock' ? <span className={Number(row.quantity) <= Number(row.reorder_level) ? 'low-stock' : ''}>{Number(row.quantity) <= Number(row.reorder_level) ? 'Low Stock' : 'Available'}</span> : key === 'status' ? <span className="badge text-bg-secondary">{row[key]}</span> : key === 'table_number' ? row[key] || 'Takeout' : key === 'reservation_time' ? row[key]?.slice(0, 5) : row[key]}</td>)}
            {(onEdit || onDelete) && <td className="text-nowrap">{onEdit && <button disabled={busy} className="btn btn-sm btn-primary action-button" aria-label={`Edit ${singular[module]}`} onClick={() => onEdit(row)}><i aria-hidden="true" className="bi bi-pencil" /></button>}{onDelete && <button disabled={busy} className="btn btn-sm btn-danger" aria-label={`Delete ${singular[module]}`} onClick={() => onDelete(row)}><i aria-hidden="true" className="bi bi-trash" /></button>}</td>}
        </tr>) : <tr><td className="empty-row" colSpan={columns[module].length + ((onEdit || onDelete) ? 1 : 0)}>No {titles[module].toLowerCase()} found.</td></tr>}</tbody>
    </table></div>;
}

