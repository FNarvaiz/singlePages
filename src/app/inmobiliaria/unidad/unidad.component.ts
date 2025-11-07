import { Component , OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { JsonService } from '../../services/json.service';
import { SwiperCabeceraComponent } from '../../swiper-cabecera/swiper-cabecera.component';
import { faLocationDot } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { TarifasGridComponent } from '../../tarifas-grid/tarifas-grid.component'
import { PoliticasComponent } from '../politicas/politicas.component';
import { CondicionesComponent } from '../condiciones/condiciones.component';
import { ComoReservarComponent } from '../como-reservar/como-reservar.component';
import { Meta, Title } from '@angular/platform-browser';


interface Unidad {
  nombre: string;
  imagen1: string;
  imagen2: string;
  descripcion: string;
  venta: boolean,
  banios: number,
  categoria: string, 
  camas: number,
  imagenes: { ruta: string; alt: string }[];
  direccion: string;
  tarifas: {
    titulo: string;
    Desde: string;
    Hasta: string;
    Servicios: string;
    Info: string;
    Imagenes: string[];
    promos: {
      nombre: string;
      precios: number[][];
    }[];
  }[];
}


@Component({
  selector: 'app-unidad',
  imports: [FontAwesomeModule, SwiperCabeceraComponent, TarifasGridComponent, PoliticasComponent, CondicionesComponent, ComoReservarComponent],
  templateUrl: './unidad.component.html',
  styleUrl: './unidad.component.css'
})
export class UnidadComponent implements OnInit {
  constructor(
    private route: ActivatedRoute,
    private jsonService: JsonService,
    private title: Title,
    private meta: Meta,
  ) {}
  unidad: Unidad = {
    nombre: '',
    descripcion: '',
    imagen1: '',
    venta: false,
    imagen2: '',
    imagenes: [],
    direccion: '',
    categoria: '',
    camas:0,
    banios:0,
    tarifas: []
  };
  faLocationDot = faLocationDot
  ngOnInit() {
    const carpeta = './assets/inmo/'+this.route.snapshot.params['carpeta']+'/';
    this.jsonService.readJson(carpeta+'info_unidad.json').subscribe(data => {
      this.unidad = data;
      this.unidad.imagen1= carpeta+this.unidad.imagen1;
      this.unidad.imagen2= carpeta+this.unidad.imagen2;
      for(let img of this.unidad.imagenes)
        img.ruta = carpeta+img.ruta;

      // SEO dinámico por unidad
      const unitName = (this.unidad.nombre || '').toString().trim();
      if (unitName) {
        this.title.setTitle(`Inmobiliaria Luis Protti | ${unitName}`);
      }
      const baseDesc = (this.unidad.descripcion || '').toString().replace(/\s+/g, ' ').trim();
      const shortDesc = baseDesc.length > 160 ? baseDesc.slice(0, 157) + '...' : baseDesc;
      const finalDesc = unitName ? `${unitName} en Miramar. ${shortDesc}`.trim() : shortDesc;
      if (finalDesc) {
        this.meta.updateTag({ name: 'description', content: finalDesc });
      }
    });
  }
  
}
