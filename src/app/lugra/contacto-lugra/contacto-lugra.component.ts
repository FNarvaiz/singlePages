import { Component } from '@angular/core';
import { FormularioComponent } from "../../formulario/formulario.component";
import {MatCardModule} from '@angular/material/card';
@Component({
  selector: 'app-contacto-lugra',
  standalone: true,
  imports: [FormularioComponent,MatCardModule],
  templateUrl: './contacto-lugra.component.html',
  styleUrl: './contacto-lugra.component.css'
})
export class ContactoLugraComponent {

  mailLugra = "reservas@lugrahotel.com"
}
