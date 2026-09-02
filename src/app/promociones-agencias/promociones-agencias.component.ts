import { Component, Input } from '@angular/core';

export interface AgenciaPromo {
  imagen: string;
  nombre: string;
  descripcion: string;
  link?: string;
}

@Component({
  selector: 'app-promociones-agencias',
  standalone: true,
  imports: [],
  templateUrl: './promociones-agencias.component.html',
  styleUrl: './promociones-agencias.component.css'
})
export class PromocionesAgenciasComponent {
  @Input() agencias: AgenciaPromo[] = [];
  @Input() titulo = 'Promociones de Agencias';
}
