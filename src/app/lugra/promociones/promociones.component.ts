import { Component, OnInit, inject } from '@angular/core';
import { JsonService } from '../../services/json.service';
import { PromocionesAgenciasComponent, AgenciaPromo } from '../../promociones-agencias/promociones-agencias.component';

@Component({
  selector: 'app-promociones',
  standalone: true,
  imports: [PromocionesAgenciasComponent],
  templateUrl: './promociones.component.html',
  styleUrl: './promociones.component.css'
})
export class PromocionesComponent implements OnInit {
  private jsonService = inject(JsonService);
  agenciasPromo: AgenciaPromo[] = [];

  ngOnInit() {
    this.jsonService.readFromPanel<AgenciaPromo[]>('promos-agencias.php', '/assets/promociones-agencias.json').subscribe(data => {
      this.agenciasPromo = data;
    });
  }
}
