import { Component, OnInit, HostListener, ViewChild, ElementRef } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { JsonService } from '../../services/json.service';
import { SwiperCabeceraComponent } from '../../swiper-cabecera/swiper-cabecera.component';
import { faLocationDot, faBed, faBath, faImages } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { TarifasGridComponent } from '../../tarifas-grid/tarifas-grid.component'
import { FormasPagoComponent } from '../../formas-pago/formas-pago.component'
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
  imports: [FontAwesomeModule, TarifasGridComponent, FormasPagoComponent, PoliticasComponent, CondicionesComponent, ComoReservarComponent],
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
  faLocationDot = faLocationDot;
  faBed = faBed;
  faBath = faBath;
  faImages = faImages;
  lightboxIndex = 0;
  formasPago: any[] = [];

  get imgActual(): { ruta: string; alt: string } | undefined {
    return this.unidad.imagenes[this.lightboxIndex];
  }
  @ViewChild('lb') lbRef!: ElementRef<HTMLDialogElement>;

  openLightbox(index: number) {
    this.lightboxIndex = index;
    this.lbRef.nativeElement.showModal();
    document.body.style.overflow = 'hidden';
  }

  closeLightbox() {
    this.lbRef?.nativeElement?.close();
  }

  onDialogClick(event: MouseEvent) {
    if (event.target === this.lbRef.nativeElement) this.lbRef.nativeElement.close();
  }

  onDialogClose() {
    document.body.style.overflow = '';
  }

  prevImg() {
    this.lightboxIndex = (this.lightboxIndex - 1 + this.unidad.imagenes.length) % this.unidad.imagenes.length;
  }

  nextImg() {
    this.lightboxIndex = (this.lightboxIndex + 1) % this.unidad.imagenes.length;
  }

  @HostListener('document:keydown', ['$event'])
  handleKeydown(event: KeyboardEvent) {
    if (!this.lbRef?.nativeElement?.open) return;
    if (event.key === 'ArrowLeft') this.prevImg();
    if (event.key === 'ArrowRight') this.nextImg();
  }
  ngOnInit() {
    this.jsonService.readJson('/assets/formas-pago-inmo.json').subscribe(data => {
      this.formasPago = data;
    });
    const params = this.route.snapshot.params;
    const carpeta = '/assets/inmo/' + params['tipo'] + '/' + params['nombre'] + '/';
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
