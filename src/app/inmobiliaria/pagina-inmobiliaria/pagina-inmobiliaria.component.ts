import { Component } from '@angular/core';
import { NavbarInmoComponent } from "../navbar-inmo/navbar-inmo.component";
import { FooterComponent } from "../footer/footer.component";
import { RouterOutlet } from '@angular/router';
@Component({
  selector: 'app-pagina-inmobiliaria',
  standalone: true,
  imports: [NavbarInmoComponent, FooterComponent, RouterOutlet],
  templateUrl: './pagina-inmobiliaria.component.html',
  styleUrl: './pagina-inmobiliaria.component.css'
})
export class PaginaInmobiliariaComponent {

}
