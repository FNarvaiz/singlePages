// Panel Lugra — editor de unidad (galería de fotos + tarifas con promos)
(function () {
    'use strict';

    const datos = JSON.parse(document.getElementById('datos-editor-unidad').textContent);
    const contenedorImagenes = document.getElementById('contenedor-imagenes');
    const contenedorTarifas = document.getElementById('contenedor-tarifas');
    const inputTarifasJson = document.getElementById('tarifas_json');
    const inputImagenesJson = document.getElementById('imagenes_json');
    const form = document.getElementById('form-unidad');
    const inputCarpeta = form ? form.querySelector('input[name="carpeta"]') : null;
    const carpetaOriginal = inputCarpeta ? inputCarpeta.dataset.original : '';

    if (form && inputCarpeta && carpetaOriginal) {
        form.addEventListener('submit', function (event) {
            const carpetaNueva = inputCarpeta.value.trim();
            if (carpetaNueva === carpetaOriginal) {
                return;
            }
            if (!confirm(
                'Vas a cambiar la carpeta de "' + carpetaOriginal + '" a "' + carpetaNueva + '".\n\n' +
                'Se actualizarán la unidad y todos los inmuebles que usan esa carpeta. ' +
                'No se renombran los archivos del sitio: después tenés que renombrar ' +
                'la carpeta en src/assets/inmo, actualizar el prerender/sitemap y redeployar.\n\n¿Continuar?'
            )) {
                event.preventDefault();
            }
        });
    }

    if (!contenedorImagenes || !contenedorTarifas || !form) {
        return;
    }

    let imagenes = (datos.imagenes || []).map(function (i) {
        return { ruta: String(i.ruta || ''), alt: String(i.alt || '') };
    });
    let tarifas = (datos.tarifas || []).map(function (t) {
        return {
            titulo: String(t.titulo || ''),
            desde: String(t.desde || ''),
            hasta: String(t.hasta || ''),
            servicios: String(t.servicios || ''),
            info: String(t.info || ''),
            promos: (t.promos || []).map(function (p) {
                return { nombre: String(p.nombre || ''), precio: String(p.precio ?? '') };
            }),
        };
    });

    function escapeHtml(texto) {
        return String(texto)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function pintarImagenes() {
        contenedorImagenes.innerHTML = imagenes.map(function (img, i) {
            return '<div class="panel-card" style="padding: 14px; margin-bottom: 10px;">' +
                '<div class="fila-form">' +
                '<label>Foto (archivo o URL)<input type="text" class="imagen-ruta" data-i="' + i +
                    '" value="' + escapeHtml(img.ruta) + '"></label>' +
                '<label>Texto alternativo<input type="text" class="imagen-alt" data-i="' + i +
                    '" value="' + escapeHtml(img.alt) + '"></label>' +
                '</div>' +
                '<button type="button" class="btn-inline peligro" data-quitar-imagen="' + i + '">Quitar foto</button>' +
                '</div>';
        }).join('');

        contenedorImagenes.querySelectorAll('.imagen-ruta').forEach(function (input) {
            input.addEventListener('input', function () {
                imagenes[Number(input.dataset.i)].ruta = input.value;
            });
        });
        contenedorImagenes.querySelectorAll('.imagen-alt').forEach(function (input) {
            input.addEventListener('input', function () {
                imagenes[Number(input.dataset.i)].alt = input.value;
            });
        });
        contenedorImagenes.querySelectorAll('[data-quitar-imagen]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                imagenes.splice(Number(btn.getAttribute('data-quitar-imagen')), 1);
                pintarImagenes();
            });
        });
    }

    function pintarTarifas() {
        contenedorTarifas.innerHTML = tarifas.map(function (t, ti) {
            const promosHtml = t.promos.map(function (p, pi) {
                return '<div class="fila-form fila-promo" style="align-items: end;">' +
                    '<label>Promo<input type="text" class="promo-nombre" data-t="' + ti + '" data-p="' + pi +
                        '" value="' + escapeHtml(p.nombre) + '" placeholder="Ej: 7 Días"></label>' +
                    '<label>Precio ($)<input type="number" min="0" class="promo-precio" data-t="' + ti + '" data-p="' + pi +
                        '" value="' + escapeHtml(p.precio) + '"></label>' +
                    '<button type="button" class="btn-inline peligro" data-quitar-promo="' + ti + '" data-pi="' + pi +
                        '">Quitar</button>' +
                    '</div>';
            }).join('');

            return '<div class="panel-card" style="padding: 14px; margin-bottom: 12px; border-left: 4px solid #d4a853;">' +
                '<div class="fila-form">' +
                '<label>Título de temporada<input type="text" class="tarifa-titulo" data-t="' + ti +
                    '" value="' + escapeHtml(t.titulo) + '" placeholder="Ej: TEMPORADA ALTA"></label>' +
                '<label>Info (opcional)<input type="text" class="tarifa-info" data-t="' + ti +
                    '" value="' + escapeHtml(t.info) + '"></label>' +
                '</div>' +
                '<div class="fila-form">' +
                '<label>Desde<input type="date" class="tarifa-desde" data-t="' + ti +
                    '" value="' + escapeHtml(t.desde) + '"></label>' +
                '<label>Hasta<input type="date" class="tarifa-hasta" data-t="' + ti +
                    '" value="' + escapeHtml(t.hasta) + '"></label>' +
                '</div>' +
                '<label>Servicios (opcional)<input type="text" class="tarifa-servicios" data-t="' + ti +
                    '" value="' + escapeHtml(t.servicios) + '"></label>' +
                '<p class="ayuda" style="margin: 8px 0;">Promos:</p>' +
                promosHtml +
                '<div class="fila-botones">' +
                '<button type="button" class="btn btn-secundario btn-chico" data-agregar-promo="' + ti + '">+ Agregar promo</button>' +
                '<button type="button" class="btn btn-peligro btn-chico" data-quitar-tarifa="' + ti + '">Quitar temporada</button>' +
                '</div>' +
                '</div>';
        }).join('');

        contenedorTarifas.querySelectorAll('.tarifa-titulo').forEach(function (input) {
            input.addEventListener('input', function () {
                tarifas[Number(input.dataset.t)].titulo = input.value;
            });
        });
        contenedorTarifas.querySelectorAll('.tarifa-info').forEach(function (input) {
            input.addEventListener('input', function () {
                tarifas[Number(input.dataset.t)].info = input.value;
            });
        });
        contenedorTarifas.querySelectorAll('.tarifa-desde').forEach(function (input) {
            input.addEventListener('input', function () {
                tarifas[Number(input.dataset.t)].desde = input.value;
            });
        });
        contenedorTarifas.querySelectorAll('.tarifa-hasta').forEach(function (input) {
            input.addEventListener('input', function () {
                tarifas[Number(input.dataset.t)].hasta = input.value;
            });
        });
        contenedorTarifas.querySelectorAll('.tarifa-servicios').forEach(function (input) {
            input.addEventListener('input', function () {
                tarifas[Number(input.dataset.t)].servicios = input.value;
            });
        });
        contenedorTarifas.querySelectorAll('.promo-nombre').forEach(function (input) {
            input.addEventListener('input', function () {
                tarifas[Number(input.dataset.t)].promos[Number(input.dataset.p)].nombre = input.value;
            });
        });
        contenedorTarifas.querySelectorAll('.promo-precio').forEach(function (input) {
            input.addEventListener('input', function () {
                tarifas[Number(input.dataset.t)].promos[Number(input.dataset.p)].precio = input.value;
            });
        });
        contenedorTarifas.querySelectorAll('[data-quitar-promo]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tarifas[Number(btn.getAttribute('data-quitar-promo'))].promos.splice(Number(btn.getAttribute('data-pi')), 1);
                pintarTarifas();
            });
        });
        contenedorTarifas.querySelectorAll('[data-agregar-promo]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tarifas[Number(btn.getAttribute('data-agregar-promo'))].promos.push({ nombre: '', precio: '' });
                pintarTarifas();
            });
        });
        contenedorTarifas.querySelectorAll('[data-quitar-tarifa]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tarifas.splice(Number(btn.getAttribute('data-quitar-tarifa')), 1);
                pintarTarifas();
            });
        });
    }

    function serializar() {
        // Leer estado fresco desde el DOM
        const filasImagenes = Array.from(contenedorImagenes.querySelectorAll('.panel-card')).map(function (card) {
            return {
                ruta: card.querySelector('.imagen-ruta').value.trim(),
                alt: card.querySelector('.imagen-alt').value.trim(),
            };
        }).filter(function (i) { return i.ruta !== ''; });

        const filasTarifas = Array.from(contenedorTarifas.querySelectorAll('.panel-card')).map(function (card) {
            const promos = Array.from(card.querySelectorAll('.fila-promo')).map(function (fila) {
                const precio = Number(fila.querySelector('.promo-precio').value);
                return {
                    nombre: fila.querySelector('.promo-nombre').value.trim(),
                    precio: Number.isFinite(precio) && precio >= 0 ? precio : null,
                };
            });
            return {
                titulo: card.querySelector('.tarifa-titulo').value.trim(),
                desde: card.querySelector('.tarifa-desde').value,
                hasta: card.querySelector('.tarifa-hasta').value,
                servicios: card.querySelector('.tarifa-servicios').value.trim(),
                info: card.querySelector('.tarifa-info').value.trim(),
                promos: promos,
            };
        });

        return { imagenes: filasImagenes, tarifas: filasTarifas };
    }

    form.addEventListener('submit', function () {
        const datos = serializar();
        inputImagenesJson.value = JSON.stringify(datos.imagenes);
        inputTarifasJson.value = JSON.stringify(datos.tarifas);
    });

    document.getElementById('btn-agregar-imagen').addEventListener('click', function () {
        imagenes.push({ ruta: '', alt: '' });
        pintarImagenes();
    });
    document.getElementById('btn-agregar-tarifa').addEventListener('click', function () {
        tarifas.push({
            titulo: '',
            desde: '',
            hasta: '',
            servicios: '',
            info: '',
            promos: [{ nombre: '', precio: '' }],
        });
        pintarTarifas();
    });

    pintarImagenes();
    pintarTarifas();
})();
