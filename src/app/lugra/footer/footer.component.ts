import { Component } from '@angular/core';
import { menuNavList } from '../menuItemList';
import { Router } from '@angular/router';
@Component({
  selector: 'app-footer',
  standalone: true,
  imports: [],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.css'
})
export class FooterComponent {
  mailLugra = 'reservas@lugrahotel.com';
  menuItems = menuNavList;
  constructor(private router: Router) {}
  navigateAndScrollTo(
    router: string,
    hash: string,
    effectBehaviorSmooth?: boolean
  ) {
    this.router.navigate([router]).then(() => {});
  }
}
