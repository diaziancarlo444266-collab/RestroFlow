import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '../../Components/AuthLayout';
import AuthField from '../../Components/AuthField';
export default function ForgotPassword() {
    const { urls, status } = usePage().props;
    const form = useForm({ email: '' });
    return <AuthLayout title="Reset your password">
        <p>Enter your account email to receive a password reset link.</p>
        {status && <div role="status" className="alert alert-success">{status}</div>}
        <form onSubmit={e => { e.preventDefault(); form.post(urls.forgotPassword); }}>
            <AuthField name="email" label="Email" type="email" autoComplete="email" value={form.data.email} onChange={e => form.setData('email', e.target.value)} error={form.errors.email} />
            <button className="btn btn-success w-100" disabled={form.processing}>Send reset link</button>
        </form><Link href={urls.login}>Back to sign in</Link>
    </AuthLayout>;
}
