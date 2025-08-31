import { Component, ElementRef, AfterViewInit, Renderer2 } from '@angular/core';

@Component({
    selector: 'app-gallery-horizontal-paralax',
    standalone: true,
    imports: [],
    templateUrl: './gallery-horizontal-paralax.component.html',
    styleUrl: './gallery-horizontal-paralax.component.css'
})
export class GalleryHorizontalParalaxComponent implements AfterViewInit {
  misPhotos: { clases: string, descripcion: string, img: string }[] = [
    {
      clases: "slower",
      descripcion: "Stylish Parisian cafe terrace",
      img: "./assets/galeria/Historia/Historia1.jpg"
    },
    {
      clases: "faster",
      descripcion: "Vintage typewriter on the table",
      img: "./assets/galeria/Historia/Historia2.jpg"
    },
    {
      clases: "slower vertical",
      descripcion: "Old camera on a wooden table",
      img: "./assets/galeria/Historia/Historia3.jpg"
    },
    {
      clases: "slower slower-down",
      descripcion: "Classic car in the street",
      img: "./assets/galeria/Historia/Historia4.jpg"
    },
    {
      clases: "slower",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia5.jpg"
    },
    {
      clases: "faster",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia6.jpg"
    },
    {
      clases: "faster1",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia7.jpg"
    },
    {
      clases: "slower slower2",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia8.jpg"
    },
    {
      clases: "",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia9.jpg"
    },
    {
      clases: "slower",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia10.jpg"
    },
    {
      clases: "faster",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia11.jpg"
    },
    {
      clases: "slower",
      descripcion: "Stylish Parisian cafe terrace",
      img: "./assets/galeria/Historia/Historia12.jpg"
    },
    {
      clases: "faster",
      descripcion: "Vintage typewriter on the table",
      img: "./assets/galeria/Historia/Historia13.jpg"
    },
    {
      clases: "slower vertical",
      descripcion: "Old camera on a wooden table",
      img: "./assets/galeria/Historia/Historia14.jpg"
    },
    {
      clases: "slower slower-down",
      descripcion: "Classic car in the street",
      img: "./assets/galeria/Historia/Historia15.jpg"
    },
    {
      clases: "slower",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia16.jpg"
    },
    {
      clases: "faster",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia17.jpg"
    },
    {
      clases: "faster1 last",
      descripcion: "Books on a shelf",
      img: "./assets/galeria/Historia/Historia18.jpg"
    }
  ];

  private lastTouchY: number | null = null;

  constructor(private el: ElementRef, private renderer: Renderer2) {}

  ngAfterViewInit() {
    const wrapper = this.el.nativeElement.querySelector('.horizontal-scroll-wrapper');

    // Evento touchstart: guarda la posición inicial del dedo
    wrapper.addEventListener('touchstart', (e: TouchEvent) => {
      if (e.touches.length === 1) {
        this.lastTouchY = e.touches[0].clientY;
      }
    });

    // Evento touchmove: calcula el delta y ajusta el scroll
    wrapper.addEventListener('touchmove', (e: TouchEvent) => {
      if (e.touches.length === 1 && this.lastTouchY !== null) {
        const currentY = e.touches[0].clientY;
        const deltaY = this.lastTouchY - currentY;

        // Guarda el scroll antes de moverlo
        const prevScrollTop = wrapper.scrollTop;
        // Intenta mover el scroll
        wrapper.scrollTop += deltaY;

        // ¿Se movió realmente el scroll?
        const scrolled = wrapper.scrollTop !== prevScrollTop;

        // Si NO se movió (está en el tope), deja que el evento siga para que la página haga scroll
        if (scrolled) {
          e.preventDefault(); // Solo previene el scroll de la página si la galería puede desplazarse
        }

        this.lastTouchY = currentY;
      }
    });

    // Evento touchend: resetea la posición
    wrapper.addEventListener('touchend', () => {
      this.lastTouchY = null;
    });
  }
}
