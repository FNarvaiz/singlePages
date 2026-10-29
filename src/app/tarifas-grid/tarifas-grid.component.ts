import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';

@Component({
  selector: 'app-tarifas-grid',
  imports: [],
  templateUrl: './tarifas-grid.component.html',
  styleUrl: './tarifas-grid.component.css'
})
export class TarifasGridComponent implements OnChanges {
  @Input() titulo: string = "Tarifas"
  @Input() headers: string[]=[]
  @Input() tarifas: {
    titulo: string,
    Desde: string,
    Hasta: string,
    Servicios: string,
    Info: string,
    Imagenes: string[],
    subHeaders?: string[],
    precioBase?: { doble: number; triple: number },
    promos: {
      nombre: string,
      precios: number[][]
    }[]
  }[] = [];
  tarifasOrdenadas: TarifasGridComponent['tarifas'] = [];

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['tarifas']) {
      this.tarifasOrdenadas = [...(this.tarifas ?? [])].sort((a, b) =>
        a.Desde.localeCompare(b.Desde) || a.Hasta.localeCompare(b.Hasta)
      );
    }
  }

  private convertPrecio(precio: number): string {
    return precio.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }
  public splitServicios(servicios: string): string[] {
    return servicios.split(". ");
  }
  public textoPrecio(precio: number, ite: number|undefined = undefined, subHeaders?: string[]): string {
    const textoPrecio = this.convertPrecio(precio)
    
    if(ite !== undefined){
      if(subHeaders && subHeaders[ite]){
        return `${subHeaders[ite]} $ ${textoPrecio}`
      }
      const texto = ite == 0 ? "Sin Aire" : "Con Aire"
      return `${texto} $ ${textoPrecio}`
    }
    return `$ ${textoPrecio}`
  }

  public formatoPrecioBase(precio: number): string {
    return this.convertPrecio(precio);
  }

  public srcImagen(img: string): string {
    return /^https?:\/\//i.test(img) ? img : `/assets/ambos/${img}`;
  }
}
