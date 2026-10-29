import { Component, ElementRef, HostListener, OnInit, ViewChild, inject } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { JsonService } from '../../services/json.service';
import { faLocationDot, faBed, faBath, faImages } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { TarifasGridComponent } from '../../tarifas-grid/tarifas-grid.component'
import { FormasPagoComponent } from '../../formas-pago/formas-pago.component'
import { PoliticasComponent } from '../politicas/politicas.component';
import { CondicionesComponent } from '../condiciones/condiciones.component';
import { ComoReservarComponent } from '../como-reservar/como-reservar.component';
import { DomSanitizer, Meta, SafeResourceUrl, Title } from '@angular/platform-browser';


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
  localizacion?: { x: string; y: string; direccion?: string } | null;
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
  private route = inject(ActivatedRoute);
  private jsonService = inject(JsonService);
  private title = inject(Title);
  private meta = inject(Meta);
  private sanitizer = inject(DomSanitizer);

  unidad: Unidad = {
    nombre: '',
    descripcion: '',
    imagen1: '',
    venta: false,
    imagen2: '',
    imagenes: [],
    direccion: '',
    localizacion: null,
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
  formasPago: { nombre: string; promos: { icono: string; titulo: string; descripcion: string }[] }[] = [];

  get imgActual(): { ruta: string; alt: string } | undefined {
    return this.unidad.imagenes[this.lightboxIndex];
  }

  private get direccionCompleta(): string {
    const direccion = (this.unidad.direccion || '').trim();
    if (!direccion) return '';
    return `${direccion}, Miramar, Buenos Aires, Argentina`;
  }

  private get coordenadasMapa(): string {
    const loc = this.unidad.localizacion;
    if (!loc?.x || !loc?.y) return '';
    const lat = Number(loc.x);
    const lng = Number(loc.y);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return '';
    return `${lat},${lng}`;
  }

  get mapsEmbedUrl(): SafeResourceUrl | null {
    const query = this.coordenadasMapa || this.direccionCompleta;
    if (!query) return null;
    return this.sanitizer.bypassSecurityTrustResourceUrl(
      `https://maps.google.com/maps?q=${encodeURIComponent(query)}&z=16&output=embed`
    );
  }

  get mapsLink(): string {
    const query = this.coordenadasMapa || this.direccionCompleta;
    return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(query)}`;
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
    this.jsonService.readFromPanel<{ nombre: string; promos: { icono: string; titulo: string; descripcion: string }[] }[]>('formas-pago.php?sitio=inmobiliaria', '/assets/formas-pago-inmo.json').subscribe(data => {
      this.formasPago = data;
    });
    const params = this.route.snapshot.params;
    const carpeta = '/assets/inmo/' + params['tipo'] + '/' + params['nombre'] + '/';
    const carpetaParam = params['tipo'] + '/' + params['nombre'];
    this.jsonService.readFromPanel<Unidad>('unidad.php?carpeta=' + encodeURIComponent(carpetaParam), carpeta + 'info_unidad.json').subscribe(data => {
      this.unidad = {
        ...data,
        imagen1: data.imagen1 ?? '',
        imagen2: data.imagen2 ?? '',
        imagenes: data.imagenes ?? [],
        tarifas: data.tarifas ?? [],
        direccion: data.direccion ?? '',
        localizacion: data.localizacion ?? null,
      };
      this.unidad.imagen1 = this.rutaImagen(carpeta, this.unidad.imagen1);
      this.unidad.imagen2 = this.rutaImagen(carpeta, this.unidad.imagen2);
      for (const img of this.unidad.imagenes)
        img.ruta = this.rutaImagen(carpeta, img.ruta);

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

  private rutaImagen(carpeta: string, ruta: string): string {
    return /^https?:\/\//i.test(ruta) ? ruta : carpeta + ruta;
  }
  
}
