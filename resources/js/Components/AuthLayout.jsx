import { Head } from '@inertiajs/react';

export default function AuthLayout({ title, children }) {
    return <main className="container py-5" style={{ maxWidth: 480 }}>
        <Head title={title} />
        <div className="text-center mb-4"><i aria-hidden="true" className="bi bi-cup-hot-fill fs-1 text-success" /><h1>RestroFlow</h1><p>Restaurant management workspace</p></div>
        <section className="card shadow-sm"><div className="card-body p-4"><h2 className="h4 mb-4">{title}</h2>{children}</div></section>
    </main>;
}
