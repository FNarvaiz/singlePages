import { Component } from '@angular/core';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { faWhatsapp, faFacebook, faInstagram} from '@fortawesome/free-brands-svg-icons';
import { faPhone } from '@fortawesome/free-solid-svg-icons';
@Component({
    selector: 'app-navbar-inmo-top',
    standalone: true,
    imports: [FontAwesomeModule],
    templateUrl: './navbar-inmo-top.component.html',
    styleUrl: './navbar-inmo-top.component.css'
})
export class NavbarInmoTopComponent {
    faWhatsapp = faWhatsapp
    faFacebook = faFacebook
    faInstagram = faInstagram 
    faPhone = faPhone
    constructor() {
      }
}
