import { Component, ElementRef, Input, OnChanges, SimpleChanges, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { environment } from '../../../environments/environment';

interface LatLngLiteral { lat: number; lng: number }

export interface MapaInmueble {
  nombre: string;
  direccion: string;
  x: string; // lat
  y: string; // lng
}
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
  selector: 'app-mapa-inmuebles',
  standalone: true,
  imports: [CommonModule],
  template: `
  <div #mapContainer class="map-container"></div>
  `,
  styles: [`
  .map-container {
    width: 100%;
    height: 360px;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    overflow: hidden;
  }
  @media (min-width: 768px) { .map-container { height: 440px; } }
  @media (min-width: 1024px) { .map-container { height: 520px; } }
  `]
})
export class MapaInmueblesComponent implements OnChanges {
  @Input() inmuebles: Inmueble[] = [];
  @ViewChild('mapContainer', { static: true }) mapContainer!: ElementRef<HTMLDivElement>;

  private map: any | null = null;
  private markers: any[] = [];

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['inmuebles']) {
      this.ensureMapsLoaded().then(() => this.renderMap());
    }
  }

  private async ensureMapsLoaded(): Promise<void> {
    const w = window as any;
    if (w.google && w.google.maps) return;
    const apiKey = environment.googleMaps?.apiKey || '';
    if (!apiKey) {
      console.warn('Google Maps API key no configurada en environment.googleMaps.apiKey');
      return;
    }
    await new Promise<void>((resolve, reject) => {
      const script = document.createElement('script');
      script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}`;
      script.async = true;
      script.defer = true;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error('Fallo carga Google Maps'));
      document.head.appendChild(script);
    });
  }

  private renderMap(): void {
    const w = window as any;
    if (!w.google || !w.google.maps) return;

    const center: LatLngLiteral = this.computeCenter();
    if (!this.map) {
      this.map = new w.google.maps.Map(this.mapContainer.nativeElement, {
        center,
        zoom: 10,
        mapTypeControl: false,
        streetViewControl: false,
      });
    } else {
      this.map.setCenter(center);
    }

    // Clear old markers
    this.markers.forEach(m => m.setMap(null));
    this.markers = [];

    // Add markers
    const bounds = new w.google.maps.LatLngBounds();
    this.inmuebles.forEach(item => {
      
      const lat = parseFloat(item.localizacion?.x ?? (item as any).x);
      const lng = parseFloat((parseFloat(item.localizacion?.y ?? (item as any).y)+0.002602).toFixed(7));
      if (isNaN(lat) || isNaN(lng)) return;
      const position = { lat, lng } as LatLngLiteral;
      const marker = new w.google.maps.Marker({
        position,
        map: this.map!,
        title: item.nombre,
      });
      const info = new w.google.maps.InfoWindow({
        content: `<strong>${item.nombre}</strong><br>${item.localizacion?.direccion || ''}`
      });
      marker.addListener('click', () => info.open({ map: this.map!, anchor: marker }));
      this.markers.push(marker);
      bounds.extend(position as any);
    });

    if (this.inmuebles.length > 1) {
      this.map.fitBounds(bounds);
    }
  }

  private computeCenter(): LatLngLiteral {
    const first = this.inmuebles?.[0];
    const lat = parseFloat(first?.localizacion?.x ?? (first as any)?.x ?? '-37.999');
    const lng =   parseFloat(( parseFloat(first?.localizacion?.y ?? (first as any)?.y ?? '-57.549')+0.002602).toFixed(7));
    return { lat: isNaN(lat) ? -37.999 : lat, lng: isNaN(lng) ? -57.549 : lng };
  }
}


