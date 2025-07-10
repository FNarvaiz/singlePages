import { ViewportScroller } from '@angular/common';
import { AfterViewInit, Component, ElementRef, HostListener, OnInit, ViewChild } from '@angular/core';
import { Router, RouterLink, RouterModule } from '@angular/router';

@Component({
  selector: 'app-navbar-lugra',
  standalone: true,
  imports: [RouterLink, RouterModule],
  templateUrl: './navbar-lugra.component.html',
  styleUrl: './navbar-lugra.component.css'
})
export class NavbarLugraComponent implements OnInit {
  @ViewChild('navbar') navbar!: ElementRef;
  @ViewChild('toggleButton') toggleButton!: ElementRef;
  @ViewChild('Menu') menu!: ElementRef;
  navbarMobile = false;
  seEstaScrolleandoPorMenu: boolean = false;
  imgBanner = '';
  menuItemsList = [
    { name: 'INICIO', linkPrimeraRuta:'/hotel', link: '#topNavBar', routerLink: '', effectBehavior: false  },
    { name: 'TARIFAS', linkPrimeraRuta:'/hotel/tarifas', link: '#topNavBar', routerLink: '', effectBehavior: false  },
    { name: 'GALERIA', linkPrimeraRuta:'/hotel/galeria', link: '#topNavBar', routerLink: '', effectBehavior: false  },
    { name: 'NOSOTROS', linkPrimeraRuta: '/hotel/nosotros', link: '#topNavBar', routerLink: '', effectBehavior: false },
    { name: 'CONTACTO', linkPrimeraRuta: '/hotel/contacto', link: '#topNavBar', routerLink: '', effectBehavior: false },
  ];

  constructor(private router: Router,
    private viewportScroller: ViewportScroller,) { }

  ngOnInit() {
    
  }

  toggleNavbar() {
    this.navbarMobile = !this.navbarMobile;
    this.navbar.nativeElement.classList.toggle('navbar-mobile');
    const toggleIcon = this.navbar.nativeElement.querySelector('.mobile-nav-toggle');
    toggleIcon.classList.toggle('bi-list');
    toggleIcon.classList.toggle('bi-x');
  }

  exitNavbar() {
    this.navbarMobile = false;
    this.navbar.nativeElement.classList.remove('navbar-mobile');
    const toggleIcon = this.navbar.nativeElement.querySelector('.mobile-nav-toggle');
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
      const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
      const navbarHeight = navbar.offsetHeight;
      const scrollPosition = elementPosition - 20;
      if (effectBehaviorSmooth) {
        window.scrollTo ({ top: scrollPosition, behavior: 'smooth' });
      }
      else {
        window.scrollTo({ top: scrollPosition, behavior: 'instant' });
      }
    }
  }




  navigateAndScrollTo(router: string, hash: string, effectBehaviorSmooth?: boolean) {
    this.router.navigate([router]).then(() => {
      setTimeout(() => {
        this.scrollTo(hash, undefined, effectBehaviorSmooth);
      }, 0);
    });
  }

  ubicacionPrincipal = this.viewportScroller.getScrollPosition()[1];
  @HostListener('window:scroll', ['$event'])
  ocultarYMostrarMenu(){
      if(this.ubicacionPrincipal >= this.viewportScroller.getScrollPosition()[1]){
        this.menu.nativeElement.style.top = '0';
      }
      else{
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
