import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { HttpClient } from '@angular/common/http';
import { RecaptchaModule } from 'ng-recaptcha';
import { environment } from '../../../environments/environment';
import { CommonModule } from '@angular/common';

@Component({
    selector: 'app-contacto',
    standalone: true,
    imports: [
        CommonModule,
        ReactiveFormsModule,
        MatFormFieldModule,
        MatInputModule,
        MatButtonModule,
        MatCardModule,
        MatIconModule,
        MatSnackBarModule,
        MatProgressSpinnerModule,
        RecaptchaModule
    ],
    templateUrl: './contacto.component.html',
    styleUrl: './contacto.component.css'
})
export class ContactoComponent {
    contactoForm: FormGroup;
    isLoading = false;
    recaptchaResolved = false;
    siteKey = environment.recaptcha.siteKey;

    constructor(
        private fb: FormBuilder,
        private snackBar: MatSnackBar, private http: HttpClient
    ) {
        this.contactoForm = this.fb.group({
            nombre: ['', [Validators.required, Validators.minLength(2)]],
            email: ['', [Validators.required, Validators.email]],
            telefono: ['', [Validators.required, Validators.pattern(/^[0-9+\-\s()]+$/)]],
            asunto: ['', [Validators.required, Validators.minLength(5)]],
            mensaje: ['', [Validators.required, Validators.minLength(10)]],
            recaptcha: [null as string | null, Validators.required]
        });
    }

    // Método para manejar cuando se resuelve el reCAPTCHA
    onRecaptchaResolved(token: string): void {
        this.recaptchaResolved = true;
        this.contactoForm.patchValue({ recaptcha: token as string | null });
    }

    // Método para manejar cuando expira el reCAPTCHA
    onRecaptchaExpired(): void {
        this.recaptchaResolved = false;
        this.contactoForm.patchValue({ recaptcha: null });
    }

    onSubmit() {
        if (this.contactoForm.valid && this.recaptchaResolved) {
            this.isLoading = true;
            let mensajeExtra = '';
            // Armamos el mensaje extra solo con los campos del formulario
            const form = this.contactoForm.value;
            mensajeExtra += `Teléfono: ${form.telefono}\n`;
            mensajeExtra += `Asunto: ${form.asunto}\n`;

            const datos = {
                nombre: form.nombre,
                email: form.email,
                mensaje: `${form.mensaje}\n\n${mensajeExtra}`.trim(),
                recaptchaToken: form.recaptcha // Enviamos el token del reCAPTCHA
            };

            this.http.post(`${window.location.origin}/enviar.php`, datos).subscribe({
                next: () => {
                    this.isLoading = false;
                    this.snackBar.open('¡Mensaje enviado correctamente!', 'Cerrar', {
                        duration: 5000,
                        horizontalPosition: 'center',
                        verticalPosition: 'top'
                    });
                    
                    this.recaptchaResolved = false;
                    this.contactoForm.reset();
                },
                error: err => {
                    this.isLoading = false;
                    this.snackBar.open('Ocurrió un error al enviar el mensaje', 'Cerrar', {
                        duration: 5000,
                        horizontalPosition: 'center',
                        verticalPosition: 'top'
                    });
                }
            });
        } else {
            this.marcarCamposComoTocados();
            if (!this.recaptchaResolved) {
                this.snackBar.open('Por favor, completa el reCAPTCHA para verificar que no es un robot', 'Cerrar', {
                    duration: 5000,
                    horizontalPosition: 'center',
                    verticalPosition: 'top'
                });
            } else {
                this.snackBar.open('Por favor, completa todos los campos correctamente', 'Cerrar', {
                    duration: 5000,
                    horizontalPosition: 'center',
                    verticalPosition: 'top'
                });
            }
        }
    }

    private marcarCamposComoTocados() {
        Object.keys(this.contactoForm.controls).forEach(key => {
            this.contactoForm.get(key)?.markAsTouched();
        });
    }

    getErrorMessage(campo: string): string {
        const control = this.contactoForm.get(campo);
        if (control?.hasError('required')) {
            return 'Este campo es requerido';
        }
        if (control?.hasError('email')) {
            return 'Ingresa un email válido';
        }
        if (control?.hasError('minlength')) {
            const requiredLength = control.errors?.['minlength'].requiredLength;
            return `Mínimo ${requiredLength} caracteres`;
        }
        if (control?.hasError('pattern')) {
            return 'Formato de teléfono inválido';
        }
        return '';
    }
}
