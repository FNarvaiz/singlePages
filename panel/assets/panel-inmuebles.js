(function () {
    'use strict';

    const form = document.querySelector('form[action="inmuebles-guardar.php"]');
    const input = form ? form.querySelector('input[name="carpeta"]') : null;
    const original = input ? input.dataset.original : '';

    if (!form || !input || !original) {
        return;
    }

    form.addEventListener('submit', function (event) {
        const nueva = input.value.trim();
        if (nueva === original) {
            return;
        }
        if (!confirm(
            'Vas a cambiar la carpeta de "' + original + '" a "' + nueva + '".\n\n' +
            'Se actualizarán la unidad y todos los inmuebles que usan esa carpeta. ' +
            'No se renombran los archivos del sitio: después tenés que renombrar ' +
            'la carpeta en src/assets/inmo, actualizar el prerender/sitemap y redeployar.\n\n¿Continuar?'
        )) {
            event.preventDefault();
        }
    });
})();
