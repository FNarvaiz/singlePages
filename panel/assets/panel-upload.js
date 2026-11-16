// Panel Lugra — subida de imágenes por AJAX para todos los ABM
(function () {
    'use strict';

    const MAX_BYTES = 5 * 1024 * 1024;
    const EXTENSIONES = /\.(png|jpe?g|webp)$/i;

    function limpiarAviso(contenedor) {
        if (contenedor) {
            contenedor.innerHTML = '';
        }
    }

    function mostrarAviso(contenedor, mensaje, tipo) {
        if (!contenedor) {
            return;
        }
        limpiarAviso(contenedor);
        const div = document.createElement('div');
        div.className = 'flash flash-' + tipo;
        div.textContent = mensaje;
        contenedor.appendChild(div);
    }

    function mostrarPreview(contenedor, url) {
        if (!contenedor) {
            return;
        }
        contenedor.innerHTML = '';

        const enlace = document.createElement('a');
        enlace.href = url;
        enlace.target = '_blank';
        enlace.rel = 'noopener';

        const imagen = document.createElement('img');
        imagen.src = url;
        imagen.alt = 'Imagen subida';
        enlace.appendChild(imagen);

        const codigo = document.createElement('code');
        codigo.className = 'url-subida';
        codigo.textContent = url;

        contenedor.appendChild(enlace);
        contenedor.appendChild(codigo);
    }

    function marcarIcono(lista, url) {
        if (!lista) {
            return;
        }

        const vacio = lista.querySelector('[data-lista-vacia]');
        if (vacio) {
            vacio.remove();
        }

        const etiqueta = document.createElement('label');
        etiqueta.className = 'icono-check';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'imagenes[]';
        checkbox.value = url;
        checkbox.checked = true;

        const imagen = document.createElement('img');
        imagen.src = url;
        imagen.alt = '';

        etiqueta.appendChild(checkbox);
        etiqueta.appendChild(imagen);
        lista.appendChild(etiqueta);
    }

    function buscarContenedor(contenedor, atributo, clase) {
        const selector = contenedor.getAttribute(atributo);
        if (selector) {
            return document.querySelector(selector);
        }
        return contenedor.querySelector(clase);
    }

    function iniciar(contenedor) {
        const esForm = contenedor.tagName === 'FORM';
        const inputArchivo = contenedor.querySelector('input[type="file"]');
        const boton = contenedor.querySelector('[data-subir]')
            || (esForm ? contenedor.querySelector('button[type="submit"]') : null);

        if (!inputArchivo || !boton) {
            return;
        }

        const aviso = buscarContenedor(contenedor, 'data-aviso', '[data-aviso-subida]');
        const preview = buscarContenedor(contenedor, 'data-preview', '[data-preview-subida]');
        const destino = contenedor.getAttribute('data-destino');
        const listaIconos = contenedor.getAttribute('data-lista-iconos');
        const esGaleria = contenedor.getAttribute('data-galeria') === '1';
        const formPadre = esForm ? contenedor : contenedor.closest('form');
        const tokenInput = formPadre ? formPadre.querySelector('input[name="csrf_token"]') : null;
        const token = contenedor.getAttribute('data-csrf') || (tokenInput ? tokenInput.value : '');
        const retornoInput = esForm ? contenedor.querySelector('input[name="retorno"]') : null;
        const retorno = contenedor.getAttribute('data-retorno') || (retornoInput ? retornoInput.value : '');
        const textoBoton = boton.textContent;
        const accion = contenedor.getAttribute('action') || 'subir-icono.php';

        function subir() {
            const archivo = inputArchivo.files && inputArchivo.files[0];
            if (!archivo) {
                mostrarAviso(aviso, 'Elegí un archivo antes de subir.', 'error');
                return;
            }
            if (archivo.size > MAX_BYTES) {
                mostrarAviso(aviso, 'La imagen supera el tamaño máximo de 5 MB.', 'error');
                return;
            }
            if (!EXTENSIONES.test(archivo.name)) {
                mostrarAviso(aviso, 'Formato no permitido: solo PNG, JPG o WebP.', 'error');
                return;
            }

            let datos;
            if (esForm) {
                datos = new FormData(contenedor);
            } else {
                datos = new FormData();
                datos.append('icono', archivo);
                datos.append('csrf_token', token);
                if (retorno) {
                    datos.append('retorno', retorno);
                }
            }
            datos.append('ajax', '1');

            boton.disabled = true;
            boton.textContent = 'Subiendo...';
            limpiarAviso(aviso);

            fetch(accion, {
                method: 'POST',
                body: datos,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (respuesta) {
                    return respuesta.json().catch(function () {
                        throw new Error('La sesión pudo haber vencido. Recargá la página y reintentá.');
                    });
                })
                .then(function (data) {
                    if (!data.ok) {
                        throw new Error(data.mensaje || 'No se pudo subir la imagen.');
                    }

                    if (destino) {
                        const campo = document.querySelector(destino);
                        if (campo) {
                            campo.value = data.url;
                            campo.dispatchEvent(new Event('input', { bubbles: true }));
                            campo.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }

                    if (listaIconos) {
                        marcarIcono(document.querySelector(listaIconos), data.url);
                    }

                    if (esGaleria && typeof window.panelUnidadAgregarImagen === 'function') {
                        window.panelUnidadAgregarImagen(data.url);
                    }

                    mostrarPreview(preview, data.url);
                    mostrarAviso(aviso, data.mensaje || 'Imagen subida.', 'ok');
                    inputArchivo.value = '';
                })
                .catch(function (error) {
                    mostrarAviso(aviso, error.message || 'No se pudo subir la imagen.', 'error');
                })
                .finally(function () {
                    boton.disabled = false;
                    boton.textContent = textoBoton;
                });
        }

        if (esForm) {
            contenedor.addEventListener('submit', function (event) {
                event.preventDefault();
                subir();
            });
        } else {
            boton.addEventListener('click', function (event) {
                event.preventDefault();
                subir();
            });
        }
    }

    document.querySelectorAll('[data-subida]').forEach(iniciar);
})();
