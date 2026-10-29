// Panel Lugra — subida de imagen para promociones de agencias
(function () {
    'use strict';

    const form = document.getElementById('form-subir-icono');
    if (!form) {
        return;
    }

    const contenedorAviso = document.getElementById('aviso-subida');
    const inputArchivo = form.querySelector('input[type="file"]');
    const boton = form.querySelector('button[type="submit"]');
    const inputImagen = document.querySelector('input[name="imagen"]');

    function mostrarAviso(mensaje, tipo) {
        if (!contenedorAviso) return;
        contenedorAviso.innerHTML = '';
        const div = document.createElement('div');
        div.className = 'flash flash-' + tipo;
        div.textContent = mensaje;
        contenedorAviso.appendChild(div);
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const datos = new FormData(form);
        datos.append('ajax', '1');
        boton.disabled = true;
        boton.textContent = 'Subiendo...';

        fetch(form.action, { method: 'POST', body: datos })
            .then(function (respuesta) {
                return respuesta.json().catch(function () {
                    throw new Error('La sesión pudo haber vencido. Recargá la página y reintentá.');
                });
            })
            .then(function (data) {
                if (!data.ok) {
                    throw new Error(data.mensaje || 'No se pudo subir la imagen.');
                }
                if (inputImagen && data.url) {
                    inputImagen.value = data.url;
                }
                inputArchivo.value = '';
                mostrarAviso(data.mensaje || 'Imagen subida.', 'ok');
            })
            .catch(function (error) {
                mostrarAviso(error.message || 'No se pudo subir la imagen.', 'error');
            })
            .finally(function () {
                boton.disabled = false;
                boton.textContent = 'Subir';
            });
    });
})();
