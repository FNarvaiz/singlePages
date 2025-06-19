import { Component, OnInit, OnDestroy, Input } from '@angular/core';
import { DomSanitizer} from '@angular/platform-browser';
import { fromEvent, interval } from 'rxjs';

@Component({
  selector: 'app-grilla-vertical',
  standalone: true,
  imports: [],
  templateUrl: './grilla-vertical.component.html',
  styleUrl: './grilla-vertical.component.css'
})
export class GrillaVerticalComponent {
  @Input() directorioImgs = './assets/img/sucursales/'
  @Input() lista = [{
    image: 'nordelta.webp',
    nombre: 'NORDELTA',
    descripcion: 'Av. Agustín M. García Nº6348<br>(RUTA 27)',
  },]
  @Input() selected =  0;
  mostrarSlider =false
  resizeSubscription: any;
  timerSubscription: any;
  constructor(private domSanitizer: DomSanitizer) {}
  ngOnInit(): void {
    this.updateAnchoDePantalla();

    // Suscribirse al evento resize
    this.resizeSubscription = fromEvent(window, 'resize').subscribe(() => {
      this.updateAnchoDePantalla();
    });
  }
  transform(url: string) {
    return this.domSanitizer.bypassSecurityTrustResourceUrl(url);
  }
  cambiar(index: number) {
    let result = this.selected+index;
    if(result >= this.lista.length)
      result = 0;
    if (result >= 0) {
      this.selected = result;
    }
  }
  addClassHide(index: number) {

    return !this.mostrarSlider || index === this.selected ? '' : 'hide';
  }
  updateAnchoDePantalla() {
    this.mostrarSlider = window.innerWidth<800;
    if(this.mostrarSlider){
      if (!this.timerSubscription) 
       
        this.timerSubscription = interval(6000).subscribe(() => {
          this.cambiar(+1);
        });
    }
    else{
      if (this.timerSubscription) 
        this.timerSubscription.unsubscribe();
    }
  }
  ngOnDestroy(): void {
    // Limpiar la suscripción
    if (this.resizeSubscription) {
      this.resizeSubscription.unsubscribe();
    }
    if (this.timerSubscription) {
      this.timerSubscription.unsubscribe();
    }
  }
}
