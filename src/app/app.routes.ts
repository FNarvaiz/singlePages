import { Routes } from '@angular/router';
import { InicioComponent } from './lugra/inicio/inicio.component';
import { TarifasComponent } from './lugra/tarifas/tarifas.component';
import { NosotrosComponent } from './lugra/nosotros/nosotros.component';
import { PaginaLugraComponent } from './lugra/pagina-lugra/pagina-lugra.component';
import { HomeComponent } from './home/home.component';
import { ContactoLugraComponent } from './lugra/contacto-lugra/contacto-lugra.component';
import { GaleriaComponent } from './lugra/galeria/galeria.component';
import { PaginaInmobiliariaComponent } from './inmobiliaria/pagina-inmobiliaria/pagina-inmobiliaria.component';
import { RestaurantComponent } from './lugra/restaurant/restaurant.component';
import { InicioComponent as InicioInmoComponent } from './inmobiliaria/inicio/inicio.component';
import { ContactoComponent } from './inmobiliaria/contacto/contacto.component';
import { ServiciosComponent } from './inmobiliaria/servicios/servicios.component';
import { InmueblesComponent } from './inmobiliaria/inmuebles/inmuebles.component';

export const routes: Routes = [
    {
        path: '',
        component: HomeComponent
    },
    {
        path: 'hotel',
        component: PaginaLugraComponent,
        children:[
            {
                path: '',
                component: InicioComponent,
            },
            {
                path: 'nosotros',
                component: NosotrosComponent,
            },
            {
                path: 'galeria',
                component: GaleriaComponent,
            },
            {
                path: 'tarifas',
                component: TarifasComponent,
            },{
                path: 'contacto',
                component: ContactoLugraComponent,
            },
            {
                path: 'restaurant',
                component: RestaurantComponent,
            },
        ]

    },
    {
        path: 'inmobiliaria',
        component: PaginaInmobiliariaComponent,
        children:[
            {
                path: '',
                component: InicioInmoComponent,
            },
            {
                path: 'inmuebles',
                component: InmueblesComponent,
            },
            {
                path: 'servicios',
                component: ServiciosComponent,
            },{
                path: 'contacto',
                component: ContactoComponent,
            },
        ]

    }


];
