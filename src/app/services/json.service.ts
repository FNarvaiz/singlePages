import { Injectable, PLATFORM_ID, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map, timeout, catchError } from 'rxjs/operators';
import { isPlatformBrowser } from '@angular/common';
import { environment } from '../../environments/environment';
import { IImage } from './IImage';

@Injectable({
  providedIn: 'root'
})
export class JsonService {

  private http = inject(HttpClient);
  private platformId = inject(PLATFORM_ID);

  getImages(folderUrl: string): Observable<{ ruta: string, alt?: string }[]> {
    return this.http.get<{ images: IImage[] }>(folderUrl+"images.json").pipe(
      map(response => response.images.map(image => ({ ruta: folderUrl+image.name, alt: image.alt, url: image.url })))
    );
  }
  readJson(fileUrl: string): Observable<any> {
    return this.http.get<any>(fileUrl);
  }

  readFromPanel<T>(endpoint: string, fallbackUrl: string): Observable<T> {
    if (!isPlatformBrowser(this.platformId)) {
      return this.readJson(fallbackUrl);
    }
    return this.http.get<T>(`${environment.panelApiUrl}/${endpoint}`).pipe(
      timeout(5000),
      catchError(() => this.readJson(fallbackUrl))
    );
  }
}
