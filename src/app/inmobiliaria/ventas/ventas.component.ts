import { Component } from '@angular/core';
import { InmueblesComponent } from "../inmuebles/inmuebles.component";
import { PoliticasComponent } from "../politicas/politicas.component";
import { CondicionesComponent } from "../condiciones/condiciones.component";

@Component({
  selector: 'app-ventas',
  imports: [InmueblesComponent, PoliticasComponent, CondicionesComponent],
  templateUrl: './ventas.component.html',
  styleUrl: './ventas.component.css'
})
export class VentasComponent {

}
