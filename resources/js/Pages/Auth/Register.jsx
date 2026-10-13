import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '../../Components/AuthLayout';
import AuthField from '../../Components/AuthField';

export default function Register() {
    const { urls } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({ name: '', email: '', password: '', password_confirmation: '' });
    function submit(event) {
        event.preventDefault();
        post(urls.register, { onFinish: () => reset('password', 'password_confirmation') });
    }
    return <AuthLayout title="Create an account"><p className="text-muted">Verify your email with a six-digit code to finish registration. Customer accounts can place orders, reserve tables, and view their own history.</p><form onSubmit={submit}>
        <AuthField name="name" label="Name" autoComplete="name" maxLength={255} value={data.name} onChange={e => setData('name', e.target.value)} error={errors.name} />
        <AuthField name="email" label="Email" type="email" autoComplete="username" maxLength={255} value={data.email} onChange={e => setData('email', e.target.value)} error={errors.email} />
        <AuthField name="password" label="Password (at least 8 characters)" type="password" autoComplete="new-password" minLength={8} value={data.password} onChange={e => setData('password', e.target.value)} error={errors.password} />
        <AuthField name="password_confirmation" label="Confirm password" type="password" autoComplete="new-password" minLength={8} value={data.password_confirmation} onChange={e => setData('password_confirmation', e.target.value)} error={errors.password_confirmation} />
        <button className="btn btn-success w-100" disabled={processing}>{processing ? 'Creating account...' : 'Register'}</button>
        <p className="mt-3 mb-0">Already registered? <Link href={urls.login}>Sign in</Link></p>
    </form></AuthLayout>;
}
