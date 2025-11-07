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
import { UnidadComponent } from './inmobiliaria/unidad/unidad.component';
import { NosotrosComponent as NosotrosInmoComponent } from './inmobiliaria/nosotros/nosotros.component';
import { AlquileresComponent } from './inmobiliaria/alquileres/alquileres.component';
import { VentasComponent } from './inmobiliaria/ventas/ventas.component';

export const routes: Routes = [
    {
        path: '',
        component: HomeComponent,
        title: 'Luis Protti & Hotel Lugra | Miramar',
        data: {
            description: 'Sitio oficial de Inmobiliaria Luis Protti y Hotel Lugra en Miramar. Alquileres, ventas y alojamiento.'
        }
    },
    {
        path: 'hotel',
        component: PaginaLugraComponent,
        children:[
            {
                path: '',
                component: InicioComponent,
                title: 'Hotel Lugra | Hotel en Miramar',
                data: {
                    description: 'Habitaciones cómodas, desayuno buffet, Wi‑Fi y excelente ubicación en Miramar. Reserva online con promociones.'
                }
            },
            {
                path: 'nosotros',
                component: NosotrosComponent,
                title: 'Hotel Lugra | Nuestra historia y valores',
                data: {
                    description: 'Conoce la historia del Hotel Lugra en Miramar, nuestro servicio y atención familiar.'
                }
            },
            {
                path: 'galeria',
                component: GaleriaComponent,
                title: 'Hotel Lugra | Galería de fotos',
                data: {
                    description: 'Recorre en fotos nuestras habitaciones, espacios comunes y servicios del Hotel Lugra en Miramar.'
                }
            },
            {
                path: 'tarifas',
                component: TarifasComponent,
                title: 'Hotel Lugra | Tarifas y promociones',
                data: {
                    description: 'Consulta precios actualizados, temporadas y promociones especiales del Hotel Lugra. Reserva directa al mejor precio.'
                }
            },{
                path: 'contacto',
                component: ContactoLugraComponent,
                title: 'Hotel Lugra | Contacto y reservas',
                data: {
                    description: 'Escríbenos por WhatsApp o email para consultas y reservas en Hotel Lugra, Miramar.'
                }
            },
            {
                path: 'restaurant',
                component: RestaurantComponent,
                title: 'Hotel Lugra | Restaurant y desayunos',
                data: {
                    description: 'Conoce nuestro servicio de desayunos y opciones gastronómicas dentro del Hotel Lugra.'
                }
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
                title: 'Inmobiliaria Luis Protti | Alquileres y ventas en Miramar',
                data: {
                    description: 'Alquileres temporarios y ventas con asesoramiento profesional en Miramar y Quequén.'
                }
            },
            {
                path: 'alquileres',
                component: AlquileresComponent,
                title: 'Inmobiliaria Luis Protti | Alquileres temporarios',
                data: {
                    description: 'Departamentos y casas equipadas en Miramar para temporada y fines de semana. Ubicaciones seleccionadas.'
                }
            },
            {
                path: 'ventas',
                component: VentasComponent,
                title: 'Inmobiliaria Luis Protti | Ventas de propiedades',
                data: {
                    description: 'Compra y venta de casas, departamentos, lotes y locales en Miramar y zona. Asesoramiento profesional.'
                }
            },
            {
                path: 'nosotros',
                component: NosotrosInmoComponent,
                title: 'Inmobiliaria Luis Protti | Nosotros',
                data: {
                    description: 'Trayectoria, valores y atención personalizada en el mercado inmobiliario de Miramar.'
                }
            },{
                path: 'contacto',
                component: ContactoComponent,
                title: 'Inmobiliaria Luis Protti | Contacto',
                data: {
                    description: 'Comunicate con nuestro equipo para consultas de alquiler, venta y tasaciones.'
                }
            },
            {
                path: 'unidad/:carpeta',
                component: UnidadComponent,
                title: 'Inmobiliaria Luis Protti | Propiedad',
                data: {
                    description: 'Detalles, fotos, ubicación y tarifas de la unidad seleccionada.'
                }
            },
        ]

    }


];
