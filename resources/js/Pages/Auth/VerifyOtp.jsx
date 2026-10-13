import { useForm, usePage } from '@inertiajs/react';
import AuthLayout from '../../Components/AuthLayout';
import AuthField from '../../Components/AuthField';

export default function VerifyOtp() {
    const { verifyUrl, resendUrl, status, urls } = usePage().props;
    const form = useForm({ code: '' });
    const resend = useForm({});
    return <AuthLayout title="Check your email">
        <p>Enter the six-digit code sent to your email. The code expires in 10 minutes.</p>
        {status && <div className="alert alert-success" role="status">{status}</div>}
        <form onSubmit={event => { event.preventDefault(); form.post(verifyUrl, { onFinish: () => form.reset('code') }); }}>
            <AuthField name="code" label="Verification code" inputMode="numeric" autoComplete="one-time-code" pattern="[0-9]{6}" maxLength={6} autoFocus value={form.data.code} onChange={event => form.setData('code', event.target.value.replace(/\D/g, ''))} error={form.errors.code || resend.errors.code} />
            <button className="btn btn-success w-100" disabled={form.processing || resend.processing}>{form.processing ? 'Verifying...' : 'Verify and sign in'}</button>
        </form>
        <button className="btn btn-link mt-3" disabled={resend.processing || form.processing} onClick={() => { form.clearErrors(); resend.post(resendUrl); }}>Resend code</button>
        <button className="btn btn-link mt-3" disabled={resend.processing || form.processing} onClick={() => resend.post(urls.cancelOtp)}>Cancel</button>
    </AuthLayout>;
}
