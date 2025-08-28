
import { ViewportScroller } from '@angular/common';


import {
  Component,
  ElementRef,
  HostListener,
  OnInit,
  ViewChild,
  Input
} from '@angular/core';
import { Router, RouterLink, RouterModule } from '@angular/router';
@Component({
    selector: 'app-navbar',
    imports: [RouterLink, RouterModule],
    templateUrl: './navbar.component.html',
    styleUrl: './navbar.component.css'
})
export class NavbarComponent implements OnInit {
  @ViewChild('navbar') navbar!: ElementRef;
  @ViewChild('toggleButton') toggleButton!: ElementRef;
  @ViewChild('Menu') menu!: ElementRef;
  navbarMobile = false;
  seEstaScrolleandoPorMenu: boolean = false;
  @Input() lema = '';
  @Input() logo = "";
  @Input() colorClass = "";
  @Input() menuItemsList :any[]= [];

  constructor(
    private router: Router,
    private viewportScroller: ViewportScroller
  ) {
    this.changeActive();
  }

  ngOnInit() {}
  changeActive() {
    this.menuItemsList.forEach(x => {
      x.active = x.linkPrimeraRuta === this.router.url;
    });
  }
  toggleNavbar() {
    this.navbarMobile = !this.navbarMobile;
    this.navbar.nativeElement.classList.toggle('navbar-mobile');
    const toggleIcon = this.navbar.nativeElement.querySelector(
      '.mobile-nav-toggle'
    );
    toggleIcon.classList.toggle('bi-list');
    toggleIcon.classList.toggle('bi-x');
  }

  exitNavbar() {
    this.navbarMobile = false;
    this.navbar.nativeElement.classList.remove('navbar-mobile');
    const toggleIcon = this.navbar.nativeElement.querySelector(
      '.mobile-nav-toggle'
    );
    toggleIcon.classList.add('bi-list');
    toggleIcon.classList.remove('bi-x');
  }

  scrollTo(hash: string, event?: Event, effectBehaviorSmooth?: boolean) {
    if (event) {
      event.preventDefault();
    }
    const navbar = this.navbar.nativeElement;
    if (navbar.classList.contains('navbar-mobile')) {
      navbar.classList.remove('navbar-mobile');
      const navbarToggle = navbar.querySelector('.mobile-nav-toggle');
      navbarToggle.classList.toggle('bi-list');
      navbarToggle.classList.toggle('bi-x');
    }
    const element = document.querySelector(hash);
    if (element) {
      const elementPosition =
        element.getBoundingClientRect().top + window.pageYOffset;
      const navbarHeight = navbar.offsetHeight;
      const scrollPosition = elementPosition - 20;
      if (effectBehaviorSmooth) {
        window.scrollTo({ top: scrollPosition, behavior: 'smooth' });
      } else {
        window.scrollTo({ top: scrollPosition, behavior: 'instant' });
      }
    }
  }

  navigateAndScrollTo(
    router: string,
    hash: string,
    effectBehaviorSmooth?: boolean
  ) {
    this.router.navigate([router]).then(() => {
      setTimeout(() => {
        this.scrollTo(hash, undefined, effectBehaviorSmooth);

        this.changeActive();
      }, 0);
    });
  }

  ubicacionPrincipal = this.viewportScroller.getScrollPosition()[1];
  @HostListener('window:scroll', ['$event'])
  ocultarYMostrarMenu() {
    if (
      this.ubicacionPrincipal >= this.viewportScroller.getScrollPosition()[1]
    ) {
      this.menu.nativeElement.style.top = '0';
    } else {
      this.menu.nativeElement.style.top = '-250px';
    }
    this.ubicacionPrincipal = this.viewportScroller.getScrollPosition()[1];
  }

  navigateTo(router: string, hash: string) {
    this.router.navigate([router]).then(() => {
      setTimeout(() => {
        this.viewportScroller.scrollToAnchor(hash);
      }, 0);
    });
  }
}
