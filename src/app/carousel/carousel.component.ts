import { Component, Input, OnInit } from '@angular/core';

import { ICarouselItem } from './carousel-item';
import { timer } from 'rxjs';
import {CommonModule, NgStyle} from '@angular/common';
import {MatIconModule} from '@angular/material/icon';
@Component({
  selector: 'app-carousel',
  standalone: true,
  imports: [NgStyle, CommonModule, MatIconModule],
  templateUrl: './carousel.component.html',
  styleUrl: './carousel.component.css'
})
export class CarouselComponent implements OnInit{
  /*
  Implement the CarouselComponent class
  */
  @Input() height: number = 500;
  @Input() transitionTime: number = 0;
  @Input() isFullScreen: boolean = false;
  @Input() items: ICarouselItem[] = [];

  public finalHeight: string | number = 500;
  public currentPosition: number = 0;

  constructor() {

  }
  ngOnInit(): void {
    this.finalHeight = this.isFullScreen ? "100vh" : this.height + 'px';
    this.items.map((item, index) => {
      item.id = index;
      item.marginLeft = 0;
    });
    if(this.transitionTime > 0){
      timer(0, this.transitionTime * 1000).subscribe(() => {
        this.setNext();
      });
    }
  }
  setCurrentPosition(position: number): void {
    this.currentPosition = position;
    this.items.find(item => item.id === 0)!.marginLeft = position * -100;
  }
  setNext(): void {

    const position = this.currentPosition < this.items.length - 1 ? this.currentPosition + 1 : 0;
    this.setCurrentPosition(position);
  }

  setBack(): void {
    const position = this.currentPosition >0 ? this.currentPosition - 1 : this.items.length - 1;
    this.setCurrentPosition(position);
  }

}

