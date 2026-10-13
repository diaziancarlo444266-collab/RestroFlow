export default function AuthField({ name, label, error, ...props }) {
    return <div className="mb-3">
        <label htmlFor={name} className="form-label">{label}</label>
        <input id={name} name={name} required className={`form-control ${error ? 'is-invalid' : ''}`} aria-invalid={!!error} aria-describedby={error ? `${name}-error` : undefined} {...props} />
        {error && <div className="invalid-feedback" id={`${name}-error`} role="alert">{error}</div>}
    </div>;
}
