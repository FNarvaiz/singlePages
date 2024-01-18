import { Component } from '@angular/core';

@Component({
  selector: 'app-gallery-grid',
  standalone: true,
  imports: [],
  templateUrl: './gallery-grid.component.html',
  styleUrl: './gallery-grid.component.css'
})
export class GalleryGridComponent {
  //el ideal para mostrar es 18 imagenes
  // falta hacerlo customizable
  miArreglo = Array.from({length: 18}, (_, i) => i + 1);

}
