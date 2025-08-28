import { Component, OnInit } from '@angular/core';
import { JsonService } from '../../services/json.service';
import {MatCardModule} from '@angular/material/card';
@Component({
    selector: 'app-tarifas',
    imports: [MatCardModule],
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
    Servicios: string,
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
  private convertPrecio(precio: number): string {
    return precio.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  public splitServicios(servicios: string): string[] {
    return servicios.split(". ");
  }
  public textoPrecio(precio: number, ite: number|undefined = undefined): string {
    var textoPrecio = this.convertPrecio(precio)
    
    console.log(ite)
    if(ite!==undefined){
      var texto = ite == 0 ? "S/A.A" : "C/A.A"
      return ` ${texto} $${textoPrecio}`
    }
    return `$${textoPrecio}`
  }

}
