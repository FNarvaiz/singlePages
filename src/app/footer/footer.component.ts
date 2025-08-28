import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';
@Component({
  selector: 'app-footer',
  standalone: true,
  imports: [],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.css'
})
export class FooterComponent {

  @Input() menuItemsList :any[]= [];
  @Input() mail : string= 'reservas@lugrahotel.com';
  @Input() class : string= '';
  constructor(private router: Router) {}
  navigateAndScrollTo(
    router: string,
    hash: string,
    effectBehaviorSmooth?: boolean
  ) {
    this.router.navigate([router]).then(() => {});
  }
}
