import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { NavbarComponent } from './../../navbar/navbar.component'
import { NavbarLugraTopComponent } from "../navbar-lugra-top/navbar-lugra-top.component";
import { FooterComponent } from '../../footer/footer.component';
import { WhatsappComponent } from '../../whatsapp/whatsapp.component';
import { menuNavList } from '../menuItemList';
@Component({
    selector: 'app-pagina-lugra',
    imports: [WhatsappComponent, FooterComponent, NavbarComponent, RouterOutlet, NavbarLugraTopComponent],
    templateUrl: './pagina-lugra.component.html',
    styleUrl: './pagina-lugra.component.css'
})
export class PaginaLugraComponent {
  numero= '542291451314';
  menuList : any[]=menuNavList;
  logo = "./assets/lugra/SVG/logo_lugra.svg"
  lema = 'Más de 50 años<br />Brindando la más alta calidad';
}
