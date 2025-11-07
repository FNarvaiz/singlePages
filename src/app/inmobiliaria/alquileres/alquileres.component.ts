import { Component } from '@angular/core';
import { InmueblesComponent } from "../inmuebles/inmuebles.component";
import { ServiciosComponent } from "../servicios/servicios.component";
import { ComoReservarComponent } from "../como-reservar/como-reservar.component";

@Component({
  selector: 'app-alquileres',
  imports: [InmueblesComponent, ServiciosComponent, ComoReservarComponent],
  templateUrl: './alquileres.component.html',
  styleUrl: './alquileres.component.css'
})
export class AlquileresComponent {

}
