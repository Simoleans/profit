// Helper simple para peticiones con headers automáticos
const getHeaders = () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    return {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token || ''
    };
};

// Función simple para hacer peticiones
export const apiRequest = async (method, url, data = null) => {
    const options = {
        method: method.toUpperCase(),
        headers: getHeaders(),
    };

    if (data && (method.toUpperCase() === 'POST' || method.toUpperCase() === 'PUT')) {
        options.body = JSON.stringify(data);
    }

    if (data && method.toUpperCase() === 'GET') {
        const params = new URLSearchParams(data).toString();
        url += params ? `?${params}` : '';
    }

    return fetch(url, options);
};

