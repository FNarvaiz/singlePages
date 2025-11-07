import { Component, input, Input, OnInit } from '@angular/core';
import { SwiperOptions } from 'swiper/types';
import Swiper from 'swiper';
import { register } from 'swiper/element/bundle';

@Component({
    selector: 'app-swiper-cabecera',
    standalone: true,
    imports: [],
    templateUrl: './swiper-cabecera.component.html',
    styleUrl: './swiper-cabecera.component.css'
})
export class SwiperCabeceraComponent  implements OnInit {

  constructor() {}
  @Input() addClassImg :  string ="";

  @Input() imagenesEscritorio: { ruta: string, alt?: string, url?: string }[] = [
  ];

  @Input() imagenesMovil: { ruta: string, alt?: string, url?: string }[] = [
  ];

  swiperParams: SwiperOptions = {
    slidesPerView: 1,
    autoplay: {
      delay: 8000,
    },
    autoHeight: true,
    spaceBetween: 0,
    navigation: {
      enabled: false,
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    zoom: {
      maxRatio: 5,
    },
    breakpoints: {
      768: {
        navigation: {
          enabled: true,
        },
      },
    }
  };

  swiperEscritorio!: Swiper;
  swiperMovil!: Swiper;

  ngOnInit(): void {
    this.inicializarSwiperSegunPantalla();
    window.addEventListener('resize', this.reinicializarSwiperSegunPantalla.bind(this));
  }

  inicializarSwiperSegunPantalla() {
    if (window.innerWidth >= 768) {
      if (!this.swiperEscritorio) {
        register();
        this.swiperEscritorio = new Swiper('.mySwiperCabeceraEscritorio', this.swiperParams);
      }
    } else {
      if (!this.swiperMovil) {
        register();
        this.swiperMovil = new Swiper('.mySwiperCabeceraMovil', this.swiperParams);
      }
    }
  }
  reinicializarSwiperSegunPantalla() {
    // Destruye los Swipers existentes si existen
    if (this.swiperEscritorio) {
      this.swiperEscritorio.destroy(true, true);
      this.swiperEscritorio = undefined as any;
    }
    if (this.swiperMovil) {
      this.swiperMovil.destroy(true, true);
      this.swiperMovil = undefined as any;
    }
    // Inicializa el Swiper correcto
    this.inicializarSwiperSegunPantalla();
  }
}