import { Component, OnInit } from '@angular/core';
import {MatExpansionModule} from '@angular/material/expansion'
import { JsonService } from '../../services/json.service';
import {MatTableModule} from '@angular/material/table'
import {MatCardModule} from '@angular/material/card';
@Component({
  selector: 'app-tarifas',
  standalone: true,
  imports: [MatExpansionModule,MatTableModule, MatCardModule],
  templateUrl: './tarifas.component.html',
  styleUrl: './tarifas.component.css'
})
export class TarifasComponent implements OnInit {
  constructor(private jsonService: JsonService) {
  }
  habitaciones: string[]=[]
  tarifas: {
    titulo: string,
    Desde: string,
    Hasta: string,
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
  }

}
