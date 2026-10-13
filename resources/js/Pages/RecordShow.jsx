import { Head, Link, usePage } from '@inertiajs/react';
import DataTable from '../Components/DataTable';
import { singular } from '../lib/restaurant';
export default function RecordShow({ module, record }) {
    const { urls } = usePage().props;
    return <main className="container py-4"><Head title={singular[module]} /><h1>{singular[module]} #{record.id}</h1><DataTable module={module} rows={[record]} /><Link href={urls.home}>Back to dashboard</Link></main>;
}
