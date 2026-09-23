const visitorTracking = window.rupnoraInitialState?.visitorTracking;

let visitSent = false;

const sendVisit = async () => {
    if (visitSent || !visitorTracking?.endpoint) {
        return;
    }

    visitSent = true;

    try {
        await fetch(visitorTracking.endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                page_path: window.location.pathname,
                page_title: document.title,
                route_name: visitorTracking.routeName || null,
                product_id: visitorTracking.productId || null,
            }),
        });
    } catch (error) {
        // Analytics must never interrupt the visitor's shopping experience.
    }
};

if (visitorTracking?.endpoint) {
    if (document.readyState === 'complete') {
        window.setTimeout(sendVisit, 0);
    } else {
        window.addEventListener('load', sendVisit, { once: true });
    }
}
