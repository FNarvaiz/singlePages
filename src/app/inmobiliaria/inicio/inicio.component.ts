import { Component, OnInit } from '@angular/core';
import { SwiperCabeceraComponent } from '../../swiper-cabecera/swiper-cabecera.component';
import { JsonService } from '../../services/json.service';
import { InmueblesComponent } from "../inmuebles/inmuebles.component";
import { ServiciosComponent } from "../servicios/servicios.component";
import { UltimoMinutoComponent, UltimoMinutoTarjeta } from '../../ultimo-minuto/ultimo-minuto.component';
import { OpinionesComponent } from '../../opiniones/opiniones.component';

@Component({
  selector: 'app-inicio',
  standalone: true,
  imports: [SwiperCabeceraComponent, InmueblesComponent, ServiciosComponent, UltimoMinutoComponent, OpinionesComponent],
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css'
})
export class InicioComponent implements OnInit {
  constructor(private jsonService: JsonService) {}
  escritorio: { ruta: string; alt?: string; url?: string }[] = [];
  movil: { ruta: string; alt?: string; url?: string }[] = [];
  ultimoMinuto: UltimoMinutoTarjeta[] = [];

  ngOnInit() {
    this.jsonService.readFromPanel<{ escritorio: { ruta: string; alt?: string; url?: string }[]; movil: { ruta: string; alt?: string; url?: string }[] }>('banners.php?sitio=inmobiliaria', '/assets/banners_inmo.json').subscribe(data => {
      this.escritorio = data['escritorio'];
      this.movil = data['movil'];
    });
    this.jsonService.readFromPanel<UltimoMinutoTarjeta[]>('ultimo-minuto-inmobiliaria.php', '/assets/ultimo-minuto-inmobiliaria.json').subscribe(data => {
      this.ultimoMinuto = data || [];
    });
  }

}
