import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-formas-pago',
  standalone: true,
  imports: [],
  templateUrl: './formas-pago.component.html',
  styleUrl: './formas-pago.component.css'
})
export class FormasPagoComponent {
  @Input() temporadas: {
    nombre: string;
    promos: { icono: string; titulo: string; descripcion: string }[];
  }[] = [];
}
