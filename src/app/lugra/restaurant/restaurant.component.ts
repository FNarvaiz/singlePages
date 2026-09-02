import { Component } from '@angular/core';
import { Lightbox, LightboxModule } from 'ngx-lightbox';

@Component({
    selector: 'app-restaurant',
    standalone: true,
    imports: [LightboxModule],
    templateUrl: './restaurant.component.html',
    styleUrl: './restaurant.component.css'
})
export class RestaurantComponent {
  constructor(private lightbox: Lightbox) {}

  verCarta() {
    this.lightbox.open([{
      src: 'assets/lugra/restaurant/carta.jpg',
      caption: 'Carta Restaurant Jurere',
      thumb: 'assets/lugra/restaurant/carta.jpg'
    }], 0, { centerVertically: true });
  }
}
