import { Component } from '@angular/core';
import { NavbarComponent } from "../../navbar/navbar.component";
import { FooterComponent } from "../../footer/footer.component";
import { RouterOutlet } from '@angular/router';
import { NavbarInmoTopComponent } from '../navbar-inmo-top/navbar-inmo-top.component';
import { menuNavList } from '../menuItemList';
@Component({
    selector: 'app-pagina-inmobiliaria',
    standalone: true,
    imports: [NavbarComponent, NavbarInmoTopComponent, FooterComponent, RouterOutlet],
    templateUrl: './pagina-inmobiliaria.component.html',
    styleUrl: './pagina-inmobiliaria.component.css'
})
export class PaginaInmobiliariaComponent {
  menuList : any[]=menuNavList;
  logo = "./assets/inmo/logo-50.png"
}
