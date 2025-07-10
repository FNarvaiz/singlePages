import { Routes } from '@angular/router';
import { InicioComponent } from './lugra/inicio/inicio.component';
import { TarifasComponent } from './lugra/tarifas/tarifas.component';
import { NosotrosComponent } from './lugra/nosotros/nosotros.component';
import { PaginaLugraComponent } from './lugra/pagina-lugra/pagina-lugra.component';
import { HomeComponent } from './home/home.component';
import { ContactoLugraComponent } from './lugra/contacto-lugra/contacto-lugra.component';
import { GaleriaComponent } from './lugra/galeria/galeria.component';

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
        ]

    }


];
