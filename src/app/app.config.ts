import { ApplicationConfig } from '@angular/core';
import { LocationStrategy, HashLocationStrategy } from '@angular/common';
import { provideRouter, TitleStrategy } from '@angular/router';
import { provideHttpClient } from '@angular/common/http';
import { routes } from './app.routes';
import { provideClientHydration } from '@angular/platform-browser';
import { provideAnimations } from '@angular/platform-browser/animations';
import { AppTitleStrategy } from './seo/app-title-strategy';

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    provideClientHydration(),
    provideAnimations(),
    provideHttpClient(),
    { provide: TitleStrategy, useClass: AppTitleStrategy },
    { provide: LocationStrategy, useClass: HashLocationStrategy },
  ]
};
