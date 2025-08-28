import { Component } from '@angular/core';
import { MatButton } from '@angular/material/button';

@Component({
    selector: 'app-navbar-lugra-top',
    imports: [MatButton],
    templateUrl: './navbar-lugra-top.component.html',
    styleUrl: './navbar-lugra-top.component.css'
})
export class NavbarLugraTopComponent {
  mailLugra = "reservas@lugrahotel.com"

  reservar(){
    
  }
}
