export async function api(path, options = {}) {
    const response = await fetch(`${document.querySelector('meta[name="api-base"]').content}/${path}`, {
        ...options,
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.substring(11) || '') },
    });
    const body = await response.json().catch(() => ({}));
    if (!response.ok) {
        const error = new Error(response.status === 419 ? 'Your session expired. Refresh the page and try again.' : body.message || 'Unable to complete the request. Please try again.');
        error.fields = body.errors || {};
        throw error;
    }
    return body;
}

