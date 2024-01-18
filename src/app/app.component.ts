import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet } from '@angular/router';
import { CarouselComponent } from './carousel/carousel.component';
import { ICarouselItem } from './carousel/carousel-item';
import { CAROUSEL_DATA_ITEMS } from './constans/carousel-const';
import { HeaderComponent } from "./header/header.component";
import { GalleryCroquelComponent } from './gallery-croquel/gallery-croquel.component';
import { GalleryHorizontalParalaxComponent } from './gallery-horizontal-paralax/gallery-horizontal-paralax.component';
import { GalleryGridComponent } from './gallery-grid/gallery-grid.component';

@Component({
    selector: 'app-root',
    standalone: true,
    templateUrl: './app.component.html',
    styleUrl: './app.component.css',
    imports: [CommonModule, GalleryGridComponent, CarouselComponent, RouterOutlet, HeaderComponent]
})
export class AppComponent {
  title = 'singlePages';
  public carouselItems : ICarouselItem[] = CAROUSEL_DATA_ITEMS;
}
