import { Routes } from '@angular/router';
import { InicioComponent } from './lugra/inicio/inicio.component';
import { HomeComponent } from './home/home.component';

export const routes: Routes = [
    {
        path: '',
        component: HomeComponent
    },
    {
        path: 'lugra',
        component: InicioComponent
    }


];
