import { Component, Input } from '@angular/core';
import { JsonService } from '../../services/json.service';
import { faLocationDot } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { Router } from '@angular/router';
import { MapaInmueblesComponent } from './mapa-inmuebles.component';
export class Inmueble {
    nombre: string = '';
    categoria: number = 0;
    tipos: number[] = [];
    camas: number = 0;
    banios: number = 0;
    carpeta: string = '';
    imagen: string = '';
    localizacion: { direccion: string; x: string; y: string } = { direccion: '', x: '', y: '' };
}
@Component({
    selector: 'app-inmuebles',
    standalone: true,
    imports: [FontAwesomeModule, MapaInmueblesComponent],
    templateUrl: './inmuebles.component.html',
    styleUrl: './inmuebles.component.css'
})
export class InmueblesComponent {
    @Input() venta: boolean = false;
    constructor(private jsonService: JsonService, private router: Router) {}
    faLocationDot =faLocationDot
    categorias: string[] = [];
    tipos: string[] = [];
    inmuebles: Inmueble[] = [];
    inmueblesFiltered: Inmueble[] = [];
    ngOnInit() {
      if(this.venta)
        this.jsonService.readJson('./assets/inmuebles_venta.json').subscribe(data => {
          this.categorias = data['categorias'];
          this.tipos = data['tipos'];
          this.inmuebles = data['inmuebles'];
          this.inmueblesFiltered = this.inmuebles;
        });
      else
        this.jsonService.readJson('./assets/inmuebles_alquiler.json').subscribe(data => {
        this.categorias = data['categorias'];
        this.tipos = data['tipos'];
        this.inmuebles = data['inmuebles'];
        this.inmueblesFiltered = this.inmuebles;
      });
    }
  
    public getPathImg(obj: Inmueble):string{
  
      return "./assets/inmo/"+obj.carpeta+"/"+obj.imagen;
    }
    public filtrar(categoriaId : number){
      const id = categoriaId +1
      this.inmueblesFiltered = this.inmuebles.filter(x => x.categoria==id);
    }
    public clickInmueble(obj: Inmueble){
      this.router.navigate(['/inmobiliaria/unidad', obj.carpeta]);
    }

}
