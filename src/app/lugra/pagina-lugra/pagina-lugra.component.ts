import { Component } from '@angular/core';
import { NavigationEnd, Router, RouterOutlet } from '@angular/router';
import { NavbarLugraComponent } from './../navbar-lugra/navbar-lugra.component'
import { NavbarLugraTopComponent } from "../navbar-lugra-top/navbar-lugra-top.component";
@Component({
  selector: 'app-pagina-lugra',
  standalone: true,
  imports: [NavbarLugraComponent, RouterOutlet, NavbarLugraTopComponent],
  templateUrl: './pagina-lugra.component.html',
  styleUrl: './pagina-lugra.component.css'
})
export class PaginaLugraComponent {

}
