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
      this.tarifasOrdenadas = this.normalizarTarifas(this.tarifas ?? []).sort((a, b) =>
        a.Desde.localeCompare(b.Desde) || a.Hasta.localeCompare(b.Hasta)
      );
    }
  }

  private normalizarTarifas(tarifas: TarifasGridComponent['tarifas']): TarifasGridComponent['tarifas'] {
    return (tarifas ?? []).map(tar => {
      const promos = (tar.promos ?? []).map(p => ({
        ...p,
        precios: (p.precios ?? []).map(pre => [...(pre ?? [])])
      }));

      const totalColumnas = promos.reduce((max, p) => Math.max(max, p.precios.length), 0);
      const columnasConPrecio: number[] = [];
      for (let col = 0; col < totalColumnas; col++) {
        const tienePrecio = promos.some(p =>
          (p.precios[col] ?? []).some(precio => Number(precio) > 0)
        );
        if (tienePrecio) columnasConPrecio.push(col);
      }

      return {
        ...tar,
        Servicios: tar.Servicios ?? '',
        Info: tar.Info ?? '',
        Imagenes: tar.Imagenes ?? [],
        promos: promos.map(p => ({
          ...p,
          precios: columnasConPrecio.map(col => p.precios[col] ?? [])
        }))
      };
    });
  }

  private convertPrecio(precio: number): string {
    return precio.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }
  public splitServicios(servicios: string): string[] {
    return servicios.split(". ");
  }
  public textoPrecio(precio: number | undefined, ite: number|undefined = undefined, subHeaders?: string[]): string {
    if (precio === undefined || precio === null) {
      return '';
    }
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
