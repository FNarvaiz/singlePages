import { Component, Input } from '@angular/core';

export interface UltimoMinutoTarjeta {
  tipo: string;
  checkIn: string;
  checkOut: string;
  precio: number;
  info?: string;
}

@Component({
  selector: 'app-ultimo-minuto',
  standalone: true,
  imports: [],
  templateUrl: './ultimo-minuto.component.html',
  styleUrl: './ultimo-minuto.component.css'
})
export class UltimoMinutoComponent {
  @Input() tarjetas: UltimoMinutoTarjeta[] = [];
  @Input() whatsappNumero = '542291451314';

  formatoPrecio(precio: number): string {
    return '$' + precio.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  whatsappLink(tarjeta: UltimoMinutoTarjeta): string {
    const mensaje = `Hola, me gustaría obtener más información sobre ${tarjeta.tipo} (${tarjeta.checkIn} al ${tarjeta.checkOut}).`;
    return `https://api.whatsapp.com/send?phone=${this.whatsappNumero}&text=${encodeURIComponent(mensaje)}`;
  }

  scrollIzquierda(container: HTMLElement) {
    container.scrollBy({ left: -320, behavior: 'smooth' });
  }

  scrollDerecha(container: HTMLElement) {
    container.scrollBy({ left: 320, behavior: 'smooth' });
  }
}
