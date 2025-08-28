import { Component, Input, OnInit } from '@angular/core';
import { Lightbox, LightboxModule } from 'ngx-lightbox';
import { MatChipsModule } from '@angular/material/chips';
import { JsonService } from '../services/json.service';

@Component({
    selector: 'app-gallery-grid',
    imports: [LightboxModule, MatChipsModule],
    templateUrl: './gallery-grid.component.html',
    styleUrl: './gallery-grid.component.css'
})
export class GalleryGridComponent {
  filtros: Array<{ nombre: string }> = [];
  fotos: Array<{
    caption: string;
    src: string;
    thumb: string;
    categoria: number;
  }> = [];

  //el ideal para mostrar es 18 imagenes
  // falta hacerlo customizable
  constructor(private jsonService: JsonService, private lightbox: Lightbox) {}
  miArreglo: Array<any>=[]
  // miArreglo: Array<any> = Array.from({ length: 18 }, (_, i) => ({
  //   src: `https://picsum.photos/600/600/?image=${511 + i}`,
  //   caption: 511 + i,
  //   thumb: `https://picsum.photos/600/600/?image=${511 + i}`
  // }));

  open(index: number): void {
    this.lightbox.open(this.miArreglo, index, {
      containerElementResolver: () =>
        document.getElementById('gallery') as HTMLElement
    });
  }

  close(): void {
    this.lightbox.close();
  }
  change(id_categoria: number) {
    if (id_categoria == 0) {
      const fotosDesordenadas = [...this.fotos].sort(() => Math.random() - 0.5);
      this.miArreglo = fotosDesordenadas.slice(0, 18);
    }
    else {
      this.miArreglo = this.fotos
        .filter(x => x.categoria == id_categoria)
        .slice(0, 18);
    }
  }

  ngOnInit() {
    // LAS CATEGORIAS VAN DE 1 EN ADELANTE.
    // LA GALERIA MANEJA DE 18 IMAGENES POR MUESTRA
    this.jsonService.readJson('./assets/galeria.json').subscribe(data => {
      this.filtros = data['categorias'];
      this.fotos = data['fotos'];
      this.change(0);
    });
  }
}
