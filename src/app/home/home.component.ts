import { Component, OnInit } from '@angular/core';
import { HeaderComponent } from '../header/header.component';
import { HeaderCutComponent } from '../header-cut/header-cut.component';
import { GrillaVerticalComponent } from '../tools/grilla-vertical/grilla-vertical.component';
import { Title } from '@angular/platform-browser';
import { JsonService } from '../services/json.service';


@Component({
  selector: 'app-home',
  standalone: true,
  imports: [HeaderComponent, HeaderCutComponent, GrillaVerticalComponent],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent implements OnInit {
  constructor(private titleService: Title, private jsonService: JsonService) {
    this.titleService.setTitle('Home - '+this.titleService.getTitle());
  }
  
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
