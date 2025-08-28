import { Component } from '@angular/core';
import { GalleryGridComponent } from "../../gallery-grid/gallery-grid.component";

@Component({
    selector: 'app-galeria',
    imports: [GalleryGridComponent],
    templateUrl: './galeria.component.html',
    styleUrl: './galeria.component.css'
})
export class GaleriaComponent {
}
