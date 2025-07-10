# Configuración de Google reCAPTCHA

## Pasos para configurar reCAPTCHA en tu formulario

### 1. Obtener las claves de reCAPTCHA

1. Ve a [Google reCAPTCHA](https://www.google.com/recaptcha/admin)
2. Inicia sesión con tu cuenta de Google
3. Haz clic en "Crear" para crear un nuevo sitio
4. Selecciona "reCAPTCHA v2" y "Casilla de verificación"
5. Agrega tu dominio (ej: `localhost` para desarrollo, `lugrahotel.com.ar` para producción)
6. Acepta los términos y haz clic en "Enviar"
7. Copia la **Clave del sitio** y la **Clave secreta**

### 2. Configurar las claves en tu aplicación

#### Para desarrollo:
Edita el archivo `src/environments/environment.ts`:
```typescript
export const environment = {
  production: false,
  recaptcha: {
    siteKey: 'TU_CLAVE_DEL_SITIO_AQUI'
  }
};
```

#### Para producción:
Edita el archivo `src/environments/environment.prod.ts`:
```typescript
export const environment = {
  production: true,
  recaptcha: {
    siteKey: 'TU_CLAVE_DEL_SITIO_PRODUCCION_AQUI'
  }
};
```

### 3. Configurar el backend (PHP)

En tu archivo `enviar.php`, necesitas verificar el token del reCAPTCHA:

```php
<?php
// Obtener el token del reCAPTCHA
$recaptchaToken = $_POST['recaptchaToken'];

// Verificar el token con Google
$secretKey = 'TU_CLAVE_SECRETA_AQUI';
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$recaptchaToken);
$responseKeys = json_decode($response, true);

// Verificar si la verificación fue exitosa
if (!$responseKeys["success"]) {
    http_response_code(400);
    echo json_encode(['error' => 'Verificación reCAPTCHA fallida']);
    exit;
}

// Si la verificación es exitosa, continuar con el envío del email
// ... resto de tu código de envío de email
?>
```

### 4. Probar la implementación

1. Ejecuta `ng serve` para iniciar el servidor de desarrollo
2. Abre tu formulario en el navegador
3. Completa el formulario y verifica que el reCAPTCHA aparezca
4. Marca la casilla del reCAPTCHA y envía el formulario
5. Verifica que el token se envíe correctamente al backend

### 5. Consideraciones de seguridad

- **Nunca** expongas la clave secreta en el frontend
- Usa HTTPS en producción
- Considera implementar rate limiting en tu backend
- Valida siempre el token en el servidor antes de procesar el formulario

### 6. Solución de problemas

- **reCAPTCHA no aparece**: Verifica que la clave del sitio sea correcta
- **Error de dominio**: Asegúrate de que tu dominio esté registrado en la consola de reCAPTCHA
- **Token inválido**: Verifica que la clave secreta sea correcta en el backend

### 7. Personalización

Puedes personalizar la apariencia del reCAPTCHA agregando estilos CSS en `formulario.component.css`:

```css
.recaptcha-container {
  /* Tus estilos personalizados */
}
```

## Notas importantes

- La clave del sitio es pública y puede estar en el frontend
- La clave secreta debe estar solo en el backend
- Para desarrollo local, usa `localhost` como dominio en la consola de reCAPTCHA
- Para producción, usa tu dominio real 