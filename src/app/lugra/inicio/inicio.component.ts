import { Component } from '@angular/core';
import { CarouselComponent } from '../../carousel/carousel.component';
import { ICarouselItem } from '../../carousel/carousel-item';
import { SwiperCabeceraComponent } from '../../swiper-cabecera/swiper-cabecera.component';
@Component({
  selector: 'app-inicio',
  standalone: true,
  imports: [SwiperCabeceraComponent, CarouselComponent],
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css'
})
export class InicioComponent {

  escritorio: {ruta:string,alt:string}[] = [
    {
      ruta: 'assets/lugra/inicio/cerrado-por-vacaciones.png',
      alt: "Vaciones del hotel"
    },
    {
      ruta: 'assets/lugra/inicio/agosto.jpg',
      alt: "Promocion Pre Temporada"
    }
  ]

}
