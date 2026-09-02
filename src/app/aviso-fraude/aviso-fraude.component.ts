import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-aviso-fraude',
  standalone: true,
  imports: [],
  templateUrl: './aviso-fraude.component.html',
  styleUrl: './aviso-fraude.component.css'
})
export class AvisoFraudeComponent implements OnInit {
  visible = false;
  private readonly STORAGE_KEY = 'aviso-fraude-cerrado';

  canales = [
    { icono: 'fa-globe', label: 'Web Oficial', valor: 'www.lugrahotel.com / www.luisprotti.com' },
    { icono: 'fa-phone', label: 'Teléfonos', valor: '(02291) 43-2325 / (02291) 43-1766' },
    { icono: 'fa-whatsapp', label: 'WhatsApp', valor: '(02291) 45-1314' },
    { icono: 'fa-envelope', label: 'Email', valor: 'reservas@lugrahotel.com' },
    { icono: 'fa-facebook', label: 'Facebook', valor: '/lugrahotel' },
    { icono: 'fa-instagram', label: 'Instagram', valor: '@lugrahotel' },
  ];

  ngOnInit() {
    const cerrado = localStorage.getItem(this.STORAGE_KEY);
    if (!cerrado) {
      this.visible = true;
    }
  }

  cerrar() {
    this.visible = false;
    localStorage.setItem(this.STORAGE_KEY, 'true');
  }
}
