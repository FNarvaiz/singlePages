import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-tarifas-grid',
  imports: [],
  templateUrl: './tarifas-grid.component.html',
  styleUrl: './tarifas-grid.component.css'
})
export class TarifasGridComponent {
  @Input() titulo: string = "Tarifas"
  @Input() headers: string[]=[]
  @Input() tarifas: {
    titulo: string,
    Desde: string,
    Hasta: string,
    Servicios: string,
    Info: string,
    Imagenes: string[],
    promos: {
      nombre: string,
      precios: number[][]
    }[]
  }[] = [];


  private convertPrecio(precio: number): string {
    return precio.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }
  public splitServicios(servicios: string): string[] {
    return servicios.split(". ");
  }
  public textoPrecio(precio: number, ite: number|undefined = undefined): string {
    var textoPrecio = this.convertPrecio(precio)
    
    if(ite!==undefined){
      var texto = ite == 0 ? "Sin Aire" : "Con Aire"
      return `${texto} $${textoPrecio}`
    }
    return `$${textoPrecio}`
  }
}
