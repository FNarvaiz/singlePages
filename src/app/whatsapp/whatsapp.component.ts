import { Component, Input, input } from '@angular/core';

@Component({
    selector: 'app-whatsapp',
    imports: [],
    templateUrl: './whatsapp.component.html',
    styleUrl: './whatsapp.component.css'
})
export class WhatsappComponent {
    @Input() numero_telefonico : string = "";
}
