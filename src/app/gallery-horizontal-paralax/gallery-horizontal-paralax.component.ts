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
      img: "./assets/pagina_vieja/2.jpg"
    },
    {
      clases: "faster",
      descripcion: "Vintage typewriter on the table",
      img: "./assets/pagina_vieja/alquileres.jpg"
    },
    {
      clases: "slower vertical",
      descripcion: "Old camera on a wooden table",
      img: "./assets/pagina_vieja/alquilerverano.jpg"
    },
    {
      clases: "slower slower-down",
      descripcion: "Classic car in the street",
      img: "./assets/pagina_vieja/galeriaMiramar.png"
    },
    {
      clases: "slower",
      descripcion: "Books on a shelf",
      img: "./assets/pagina_vieja/equipoLugraHotel2.png"
    },
    {
      clases: "faster",
      descripcion: "Books on a shelf",
      img: "./assets/pagina_vieja/logo_hotel.png"
    },
    {
      clases: "faster1",
      descripcion: "Books on a shelf",
      img: "./assets/pagina_vieja/isoInmoColor.png"
    },
    {
      clases: "slower slower2",
      descripcion: "Books on a shelf",
      img: "./assets/pagina_vieja/fondoweblugra2.jpg"
    },
    {
      clases: "",
      descripcion: "Books on a shelf",
      img: "./assets/pagina_vieja/fondoweblugra3.jpg"
    },
    {
      clases: "slower",
      descripcion: "Books on a shelf",
      img: "./assets/pagina_vieja/galeriaHotel.png"
    },
    {
      clases: "faster1 last",
      descripcion: "Books on a shelf",
      img: "./assets/pagina_vieja/galeriaMiramar.png"
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
