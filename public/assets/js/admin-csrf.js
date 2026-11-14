function getAdminCsrfToken() {
    const match = document.cookie.match(/(?:^|;\s*)_csrf=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
    const token = getAdminCsrfToken();
    if (token) {
        jqXHR.setRequestHeader('X-CSRF-Token', token);
    }
});

htmx.on('htmx:configRequest', function (evt) {
    const token = getAdminCsrfToken();
    if (token) {
        evt.detail.headers['X-CSRF-Token'] = token;
    }
});
