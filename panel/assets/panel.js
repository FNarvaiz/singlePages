// Panel Lugra — editor de tarifas (grilla de precios dinámica)
(function () {
    'use strict';

    const datos = JSON.parse(document.getElementById('datos-editor').textContent);
    const habitaciones = datos.habitaciones || [];
    const promosIniciales = datos.promos || [];
    const subHeadersIniciales = datos.subHeaders || [];

    const contenedorFilas = document.getElementById('grilla-filas');
    const contenedorSubHeaders = document.getElementById('subheaders');
    const botonAgregarSubHeader = document.getElementById('btn-agregar-subheader');
    const botonAgregarFila = document.getElementById('btn-agregar-fila');
    const inputPromosJson = document.getElementById('promos_json');

    if (!contenedorFilas || !inputPromosJson) {
        return;
    }

    let subHeaders = subHeadersIniciales.slice(0, 2).map(String);

    // Estructura de fila: { nombre: string, precios: string[][] }
    let filas = promosIniciales.map(function (p) {
        return {
            nombre: String(p.nombre || ''),
            precios: (p.precios || []).map(function (celda) {
                return (celda || []).map(function (v) { return String(v ?? ''); });
            }),
        };
    });

    function filaVacia() {
        return {
            nombre: '',
            precios: habitaciones.map(function () {
                return Array(Math.max(subHeaders.length, 1)).fill('');
            }),
        };
    }

    function escapeHtml(texto) {
        return String(texto)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function pintarSubHeaders() {
        contenedorSubHeaders.innerHTML = subHeaders.map(function (nombre, i) {
            return '<div class="celda-precio">' +
                '<input type="text" class="subheader-input" value="' + escapeHtml(nombre) + '" placeholder="Sub-columna ' + (i + 1) + '">' +
                '<button type="button" class="btn-inline peligro" data-quitar-subheader="' + i + '">&times; quitar</button>' +
                '</div>';
        }).join('');
        botonAgregarSubHeader.style.display = subHeaders.length >= 2 ? 'none' : '';

        contenedorSubHeaders.querySelectorAll('[data-quitar-subheader]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                subHeaders.splice(Number(btn.getAttribute('data-quitar-subheader')), 1);
                filas.forEach(function (fila) {
                    fila.precios = fila.precios.map(function () {
                        return Array(Math.max(subHeaders.length, 1)).fill('');
                    });
                });
                pintarSubHeaders();
                pintarGrilla();
            });
        });

        contenedorSubHeaders.querySelectorAll('.subheader-input').forEach(function (input) {
            input.addEventListener('input', function () {
                const i = Array.prototype.indexOf.call(
                    contenedorSubHeaders.querySelectorAll('.subheader-input'), input
                );
                subHeaders[i] = input.value;
                pintarGrilla();
            });
        });
    }

    function pintarGrilla() {
        const cabecera = document.getElementById('grilla-cabecera');
        const cantidad = Math.max(subHeaders.length, 1);

        cabecera.innerHTML = '<tr><th>Promo</th>' + habitaciones.map(function (h) {
            return '<th>' + escapeHtml(h) +
                (subHeaders.length >= 2
                    ? '<br><span class="subheader-tag">' +
                      subHeaders.map(escapeHtml).join(' / ') + '</span>'
                    : '') +
                '</th>';
        }).join('') + '<th></th></tr>';

        contenedorFilas.innerHTML = filas.map(function (fila, filaIndex) {
            let celdas = habitaciones.map(function (_, col) {
                const valores = fila.precios[col] || Array(cantidad).fill('');
                const inputs = Array.from({ length: cantidad }, function (_, k) {
                    return '<input type="number" min="0" class="precio-input" data-fila="' + filaIndex +
                        '" data-col="' + col + '" data-sub="' + k + '" value="' +
                        escapeHtml(String(valores[k] ?? '')) + '">';
                }).join('');
                return '<td><div class="celda-precio">' + inputs + '</div></td>';
            }).join('');

            return '<tr class="grilla-fila">' +
                '<td><input type="text" class="nombre-promo" data-fila="' + filaIndex +
                    '" value="' + escapeHtml(fila.nombre) + '" placeholder="Ej: 2 Noches"></td>' +
                celdas +
                '<td><button type="button" class="eliminar-fila" data-quitar-fila="' + filaIndex +
                    '" title="Quitar promo">&times;</button></td>' +
                '</tr>';
        }).join('');

        // Lectura de inputs hacia el estado (sin perder el foco)
        contenedorFilas.querySelectorAll('.nombre-promo').forEach(function (input) {
            input.addEventListener('input', function () {
                filas[Number(input.dataset.fila)].nombre = input.value;
            });
        });
        contenedorFilas.querySelectorAll('.precio-input').forEach(function (input) {
            input.addEventListener('input', function () {
                const fila = filas[Number(input.dataset.fila)];
                if (!fila.precios[Number(input.dataset.col)]) {
                    fila.precios[Number(input.dataset.col)] = Array(cantidad).fill('');
                }
                fila.precios[Number(input.dataset.col)][Number(input.dataset.sub)] = input.value;
            });
        });
        contenedorFilas.querySelectorAll('[data-quitar-fila]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                filas.splice(Number(btn.getAttribute('data-quitar-fila')), 1);
                pintarGrilla();
            });
        });
    }

    function serializar() {
        const cantidad = Math.max(subHeaders.length, 1);

        // Leer subheaders definitivos desde los inputs
        const inputsSub = Array.from(contenedorSubHeaders.querySelectorAll('.subheader-input'));
        const subHeadersFinales = inputsSub.map(function (i) { return i.value.trim(); })
            .filter(function (v) { return v !== ''; });

        // Leer filas desde el DOM (estado fresco)
        const filasFinales = Array.from(contenedorFilas.querySelectorAll('.grilla-fila')).map(function (tr) {
            const nombre = tr.querySelector('.nombre-promo').value.trim();
            const precios = habitaciones.map(function (_, col) {
                const valores = Array.from(tr.querySelectorAll(
                    '.precio-input[data-col="' + col + '"]'
                )).map(function (i) {
                    const n = Number(i.value);
                    return Number.isFinite(n) && n >= 0 ? n : null;
                });
                return valores;
            });
            return { nombre: nombre, precios: precios };
        });

        return { subHeaders: subHeadersFinales, filas: filasFinales };
    }

    document.getElementById('form-tarifas').addEventListener('submit', function () {
        const datos = serializar();
        inputPromosJson.value = JSON.stringify(datos.filas);
        const contenedorSub = document.getElementById('subheaders_json');
        contenedorSub.innerHTML = datos.subHeaders.map(function (s) {
            return '<input type="hidden" name="sub_headers[]" value="' + escapeHtml(s) + '">';
        }).join('');
    });

    botonAgregarSubHeader.addEventListener('click', function () {
        if (subHeaders.length >= 2) return;
        subHeaders.push('');
        filas.forEach(function (fila) {
            fila.precios = fila.precios.map(function (celda) {
                const nueva = celda.slice();
                nueva.push('');
                return nueva;
            });
        });
        pintarSubHeaders();
        pintarGrilla();
    });

    botonAgregarFila.addEventListener('click', function () {
        filas.push(filaVacia());
        pintarGrilla();
    });

    pintarSubHeaders();
    pintarGrilla();
})();
