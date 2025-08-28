import { Component, inject } from '@angular/core';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatSelectModule } from '@angular/material/select';
import { MatRadioModule } from '@angular/material/radio';
import { MatCardModule } from '@angular/material/card';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { HttpClient } from '@angular/common/http';
import { RecaptchaModule } from 'ng-recaptcha';
import { environment } from '../../environments/environment';
import { MAT_DATE_FORMATS } from '@angular/material/core';
import { DateAdapter, MAT_DATE_LOCALE } from '@angular/material/core';
import { MY_DATE_FORMATS } from './date-formats';
import { provideMomentDateAdapter } from '@angular/material-moment-adapter';
@Component({
    selector: 'app-formulario',
    templateUrl: './formulario.component.html',
    styleUrl: './formulario.component.css',
    providers: [
        provideMomentDateAdapter(),
        { provide: MAT_DATE_LOCALE, useValue: 'es-AR' },
        { provide: MAT_DATE_FORMATS, useValue: MY_DATE_FORMATS }
    ],
    imports: [
        MatDatepickerModule,
        MatInputModule,
        MatButtonModule,
        MatSelectModule,
        MatRadioModule,
        MatCardModule,
        ReactiveFormsModule,
        RecaptchaModule
    ]
})
export class FormularioComponent {
  constructor(private _adapter: DateAdapter<any>, private http: HttpClient) {
    this._adapter.setLocale('ar');
  }
  private fb = inject(FormBuilder);

  // Clave del sitio reCAPTCHA desde environment
  siteKey = environment.recaptcha.siteKey;

  contactForm = this.fb.group({
    nombre: [null, Validators.required],
    apellido: [null, Validators.required],
    email: [null, Validators.required],
    mensaje: [null, Validators.required],
    tipoConsulta: 'no',
    fechaDefinida: 'si',
    telefono: null,
    mes: null,
    noches: null,
    fechaInicio: null,
    fechaFin: null,
    recaptcha: [null as string | null, Validators.required] // Agregamos el campo reCAPTCHA
  });

  months = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Septiembre',
    'Octubre',
    'Noviembre',
    'Diciembre'
  ];
  noches = Array.from(
    { length: 30 },
    (_, i) => `${i + 1} Noche${i + 1 > 1 ? 's' : ''}`
  );

  mensajeEnviado: boolean = false;
  error: string = '';
  recaptchaResolved: boolean = false;

  // Método para manejar cuando se resuelve el reCAPTCHA
  onRecaptchaResolved(token: string): void {
    this.recaptchaResolved = true;
    this.contactForm.patchValue({ recaptcha: token as string | null });
  }

  // Método para manejar cuando expira el reCAPTCHA
  onRecaptchaExpired(): void {
    this.recaptchaResolved = false;
    this.contactForm.patchValue({ recaptcha: null });
  }
  convertirFecha(date: never): string{
    return new Date(date).toLocaleString('es-AR', { dateStyle: 'full', timeStyle: 'short' }) 
  }
  onSubmit(): void {
    if (this.contactForm.invalid) {
      this.error =
        'Por favor complete todos los campos obligatorios y verifique que no es un robot.';
      return;
    }

    if (!this.recaptchaResolved) {
      this.error =
        'Por favor complete el reCAPTCHA para verificar que no es un robot.';
      return;
    }

    const form = this.contactForm.value;

    // Validaciones adicionales según el tipo de consulta
    if (form.tipoConsulta === 'alojamiento') {
      if (!form.fechaInicio && form.fechaDefinida === 'si') {
        this.error = 'Debe seleccionar la fecha de inicio.';
        return;
      }
      if (!form.noches && form.fechaDefinida === 'si') {
        this.error = 'Debe seleccionar la cantidad de noches.';
        return;
      }
    }

    // Construir el mensaje con los datos adicionales
    let mensajeExtra = '';
    if (form.telefono) mensajeExtra += `Teléfono: ${form.telefono}\n`;
    if (form.tipoConsulta == 'no') {
      if (form.fechaDefinida)
        mensajeExtra += `¿Fecha definida?: ${form.fechaDefinida}\n`;
      if (form.fechaDefinida == 'si') {
        if (form.fechaInicio)
          mensajeExtra += `Check-IN: ${this.convertirFecha(form.fechaInicio)}\n`;
        if (form.fechaFin) 
          mensajeExtra += `Check-OUT: ${this.convertirFecha(form.fechaFin)}\n`;
      } else {
        if (form.mes) mensajeExtra += `Mes: ${form.mes}\n`;
        if (form.noches) mensajeExtra += `Noches: ${form.noches}\n`;
      }
    }

    const datos = {
      nombre: `${form.nombre} ${form.apellido}`,
      email: form.email,
      mensaje: `${form.mensaje}\n\n${mensajeExtra}`.trim(),
      recaptchaToken: form.recaptcha // Enviamos el token del reCAPTCHA
    };

    this.http.post(`${window.location.origin}/enviar.php`, datos).subscribe({
      next: () => {
        this.mensajeEnviado = true;
        this.error = '';
        this.contactForm.reset();
        this.recaptchaResolved = false;
      },
      error: err => {
        this.error = 'Ocurrió un error al enviar el mensaje.';
      }
    });
  }
}
