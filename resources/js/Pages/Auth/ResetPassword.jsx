import { useForm, usePage } from '@inertiajs/react';
import AuthLayout from '../../Components/AuthLayout';
import AuthField from '../../Components/AuthField';
export default function ResetPassword() {
    const { urls, token, email } = usePage().props;
    const form = useForm({ token, email, password: '', password_confirmation: '' });
    return <AuthLayout title="Choose a new password"><form onSubmit={e => { e.preventDefault(); form.post(urls.resetPassword, { onFinish: () => form.reset('password', 'password_confirmation') }); }}>
        <AuthField name="email" label="Email" type="email" value={form.data.email} onChange={e => form.setData('email', e.target.value)} error={form.errors.email} />
        <AuthField name="password" label="New password" type="password" autoComplete="new-password" minLength={8} value={form.data.password} onChange={e => form.setData('password', e.target.value)} error={form.errors.password} />
        <AuthField name="password_confirmation" label="Confirm password" type="password" autoComplete="new-password" minLength={8} value={form.data.password_confirmation} onChange={e => form.setData('password_confirmation', e.target.value)} error={form.errors.password_confirmation} />
        <button className="btn btn-success w-100" disabled={form.processing}>Reset password</button>
    </form></AuthLayout>;
}
