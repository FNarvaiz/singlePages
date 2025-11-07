import { Component } from '@angular/core';
import { SwiperCabeceraComponent } from '../../swiper-cabecera/swiper-cabecera.component';
import { JsonService } from '../../services/json.service';
import { InmueblesComponent } from "../inmuebles/inmuebles.component";
import { ServiciosComponent } from "../servicios/servicios.component";

@Component({
  selector: 'app-inicio',
  standalone: true,
  imports: [SwiperCabeceraComponent, InmueblesComponent, ServiciosComponent],
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css'
})
export class InicioComponent {
  constructor(private jsonService: JsonService) {}
  escritorio: { ruta: string; alt: string }[] = [];
  movil: { ruta: string; alt: string }[] = [];
  ngOnInit() {
    this.jsonService.readJson('./assets/banners_inmo.json').subscribe(data => {
      this.escritorio = data['escritorio'];
      this.movil = data['movil'];
    });
  }

}
