import { Component, OnInit } from '@angular/core';
import { JsonService } from '../services/json.service';


@Component({
    selector: 'app-home',
    standalone: true,
    imports: [],
    templateUrl: './home.component.html',
    styleUrl: './home.component.css'
})
export class HomeComponent implements OnInit {
  constructor(private jsonService: JsonService) {}
  
  cards: any[] = [];
  
  ngOnInit() {
    this.jsonService.readJson('./assets/home.json').subscribe(data => {
      this.cards = data;
    });
  }

  onReservar(link: string) {
    window.location.href = link;
  }
}
