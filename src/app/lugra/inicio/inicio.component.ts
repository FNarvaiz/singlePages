import { Component, OnInit } from '@angular/core';
import { SwiperCabeceraComponent } from '../../swiper-cabecera/swiper-cabecera.component';
import { OpinionesComponent } from '../../opiniones/opiniones.component';
import { UltimoMinutoComponent, UltimoMinutoTarjeta } from '../../ultimo-minuto/ultimo-minuto.component';
import { PromocionesAgenciasComponent, AgenciaPromo } from '../../promociones-agencias/promociones-agencias.component';
import { JsonService } from '../../services/json.service';
@Component({
  selector: 'app-inicio',
  standalone: true,
  imports: [SwiperCabeceraComponent, OpinionesComponent, UltimoMinutoComponent, PromocionesAgenciasComponent],
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css'
})
export class InicioComponent implements OnInit {
  constructor(private jsonService: JsonService) {}
  escritorio: { ruta: string; alt: string }[] = [];
  movil: { ruta: string; alt: string }[] = [];
  ultimoMinuto: UltimoMinutoTarjeta[] = [];
  agenciasPromo: AgenciaPromo[] = [];

  ngOnInit() {
    this.jsonService.readJson('./assets/banners_lugra.json').subscribe(data => {
      this.escritorio = data['escritorio'];
      this.movil = data['movil'];
    });
    this.jsonService.readJson('./assets/ultimo-minuto-hotel.json').subscribe(data => {
      this.ultimoMinuto = data || [];
    });
    this.jsonService.readJson('./assets/promociones-agencias.json').subscribe(data => {
      this.agenciasPromo = data;
    });
  }
  //redireccionar a /hotel/restaurant
  clickConoceMas() {
    // Redireccionar a /hotel/restaurant
    window.location.href = '/#/hotel/restaurant';
  }
  servicios: { img: string; titulo: string; descripcion: string }[] = [
    {
      img: 'assets/lugra/SVG/icono_sala_de_estar.svg',
      titulo: 'Sala de Estár',
      descripcion:
        'Un espacio en común para relajarte en cualquier momento del día.'
    },
    {
      img: 'assets/lugra/SVG/icono_buffet.svg',
      titulo: 'Buffet',
      descripcion:
        'Nuestro buffet incluye cafetería y bar con desayuno variado.'
    },
    {
      img: 'assets/lugra/SVG/icono_lavandería.svg',
      titulo: 'Lavandería',
      descripcion: 'Servicio de Tintorería y Lavandería.'
    },
    {
      img: 'assets/lugra/SVG/icono_comodidades.svg',
      titulo: 'Comodidades',
      descripcion:
        'Nuestras habitaciones incluyen TV-LED, Sommier, Frigobar, Ventilador de Techo y Cofre de seguridad.'
    },
    {
      img: 'assets/lugra/SVG/icono_wifi.svg',
      titulo: 'Wi-Fi',
      descripcion: 'Conexión inalámbrica para estar conectado en todo momento.'
    },
    {
      img: 'assets/lugra/SVG/icono_climatizacion.svg',
      titulo: 'Climatización',
      descripcion:
        'Aire acondicionado para disfrutar de máximo confort durante toda tu estadía. (consultar disponibilidad).'
    }
  ];
}
