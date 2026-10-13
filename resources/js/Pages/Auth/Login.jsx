import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '../../Components/AuthLayout';
import AuthField from '../../Components/AuthField';

export default function Login() {
    const { urls, status } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({ email: '', password: '', remember: false });
    function submit(event) {
        event.preventDefault();
        post(urls.login, { onFinish: () => reset('password') });
    }
    return <AuthLayout title="Sign in"><p className="text-muted">Sign in with your email or username and password. Email verification is only required when creating an account.</p>{status && <div className="alert alert-success" role="status">{status}</div>}<form onSubmit={submit}>
        <AuthField name="email" label="Email or username" type="text" autoComplete="username" maxLength={255} value={data.email} onChange={e => setData('email', e.target.value)} error={errors.email} />
        <AuthField name="password" label="Password" type="password" autoComplete="current-password" value={data.password} onChange={e => setData('password', e.target.value)} error={errors.password} />
        <label className="form-check mb-3"><input type="checkbox" className="form-check-input" checked={data.remember} onChange={e => setData('remember', e.target.checked)} /><span className="form-check-label">Remember me</span></label>
        <button className="btn btn-success w-100" disabled={processing}>{processing ? 'Signing in...' : 'Sign in'}</button>
        <p className="mt-3"><Link href={urls.forgotPassword}>Forgot password?</Link></p><p className="mt-3 mb-0">New here? <Link href={urls.register}>Create an account</Link></p>
    </form></AuthLayout>;
}
