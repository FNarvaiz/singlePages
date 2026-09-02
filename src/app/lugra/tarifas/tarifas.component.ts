import { Component, OnInit } from '@angular/core';
import { JsonService } from '../../services/json.service';
import {MatCardModule} from '@angular/material/card';
import { TarifasGridComponent } from '../../tarifas-grid/tarifas-grid.component'
import { FormasPagoComponent } from '../../formas-pago/formas-pago.component'
@Component({
    selector: 'app-tarifas',
    standalone: true,
    imports: [MatCardModule, TarifasGridComponent, FormasPagoComponent],
    templateUrl: './tarifas.component.html',
    styleUrl: './tarifas.component.css'
})
export class TarifasComponent implements OnInit {
  constructor(private jsonService: JsonService) {
  }
  habitaciones: string[]=[]
  temporadas: any[] = []
  tarifas: {
    titulo: string,
    Desde: string,
    Hasta: string,
    Servicios: string,
    Imagenes: string[],
    Info: string,
    promos: {
      nombre: string,
      precios: number[][]
    }[]
  }[] = [];
  
  ngOnInit() {
    this.jsonService.readJson('./assets/tarifas.json').subscribe(data => {
      this.habitaciones = data["habitaciones"]
      this.tarifas = data["cronograma"];
      var hoy = new Date()
      this.tarifas = this.tarifas.filter((promo) => {  
        const fecha = new Date(promo.Hasta);
        return hoy < fecha;
      });
    });
    this.jsonService.readJson('./assets/formas-pago.json').subscribe(data => {
      this.temporadas = data;
    });
  }
}
