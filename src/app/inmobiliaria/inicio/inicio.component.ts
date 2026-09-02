import { Component, OnInit } from '@angular/core';
import { SwiperCabeceraComponent } from '../../swiper-cabecera/swiper-cabecera.component';
import { JsonService } from '../../services/json.service';
import { InmueblesComponent } from "../inmuebles/inmuebles.component";
import { ServiciosComponent } from "../servicios/servicios.component";
import { UltimoMinutoComponent, UltimoMinutoTarjeta } from '../../ultimo-minuto/ultimo-minuto.component';

@Component({
  selector: 'app-inicio',
  standalone: true,
  imports: [SwiperCabeceraComponent, InmueblesComponent, ServiciosComponent, UltimoMinutoComponent],
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css'
})
export class InicioComponent implements OnInit {
  constructor(private jsonService: JsonService) {}
  escritorio: { ruta: string; alt: string }[] = [];
  movil: { ruta: string; alt: string }[] = [];
  ultimoMinuto: UltimoMinutoTarjeta[] = [];

  ngOnInit() {
    this.jsonService.readJson('./assets/banners_inmo.json').subscribe(data => {
      this.escritorio = data['escritorio'];
      this.movil = data['movil'];
    });
    this.jsonService.readJson('./assets/ultimo-minuto-inmobiliaria.json').subscribe(data => {
      this.ultimoMinuto = data || [];
    });
  }

}
