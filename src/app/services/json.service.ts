import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { IImage } from './IImage';

@Injectable({
  providedIn: 'root'
})
export class JsonService {

  constructor(private http: HttpClient) { }

  getImages(folderUrl: string): Observable<{ ruta: string, alt?: string }[]> {
    return this.http.get<{ images: IImage[] }>(folderUrl+"images.json").pipe(
      map(response => response.images.map(image => ({ ruta: folderUrl+image.name, alt: image.alt, url: image.url })))
    );
  }
  readJson(fileUrl: string): Observable<any> {
    return this.http.get<any>(fileUrl);
  }
}
