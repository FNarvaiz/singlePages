import { Component, Input, OnInit } from '@angular/core';
import { SwiperOptions } from 'swiper/types';
import Swiper from 'swiper';
import { register } from 'swiper/element/bundle';
import { isPlatformBrowser } from '@angular/common';
import { Inject, PLATFORM_ID } from '@angular/core';

@Component({
  selector: 'app-swiper-cabecera',
  standalone: true,
  imports: [],
  templateUrl: './swiper-cabecera.component.html',
  styleUrl: './swiper-cabecera.component.css'
})
export class SwiperCabeceraComponent  implements OnInit {

  constructor(@Inject(PLATFORM_ID) private platformId: Object) {}


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
    if (isPlatformBrowser(this.platformId)) 
      this.inicializarSwiper();
  }

  inicializarSwiper() {
    register();
    this.swiperEscritorio = new Swiper('.mySwiperCabeceraEscritorio', this.swiperParams);
    this.swiperMovil = new Swiper('.mySwiperCabeceraMovil', this.swiperParams);
  }

}