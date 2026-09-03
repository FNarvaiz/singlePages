import { ApplicationConfig, mergeApplicationConfig } from '@angular/core';
import { provideServerRendering, withRoutes } from '@angular/ssr';
import { appConfig } from './app.config';
import { serverRouteConfig } from './app.routes';

const serverConfig: ApplicationConfig = {
  providers: [provideServerRendering(withRoutes(serverRouteConfig))],
};

export const config = mergeApplicationConfig(appConfig, serverConfig);
