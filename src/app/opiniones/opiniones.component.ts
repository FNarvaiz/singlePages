import { Component, Input, OnInit, inject } from '@angular/core';
import { JsonService } from '../services/json.service';
import { environment } from '../../environments/environment';

export interface GoogleReview {
  rating: number;
  relativePublishTimeDescription: string;
  text?: { text: string };
  originalText?: { text: string };
  authorAttribution?: { displayName: string; photoUri: string; uri: string };
}

export interface ResenasGoogle {
  rating?: number;
  userRatingCount?: number;
  reviews?: GoogleReview[];
}

export type SitioResenas = 'hotel' | 'inmobiliaria';

@Component({
  selector: 'app-opiniones',
  standalone: true,
  imports: [],
  templateUrl: './opiniones.component.html',
  styleUrl: './opiniones.component.css'
})
export class OpinionesComponent implements OnInit {
  private jsonService = inject(JsonService);

  @Input() sitio: SitioResenas = 'hotel';

  rating = 0;
  total = 0;
  reviews: GoogleReview[] = [];
  cargando = true;
  error = false;

  get config() {
    return environment.googleReviews[this.sitio];
  }

  get titulo(): string {
    return this.sitio === 'hotel'
      ? 'Lo que dicen nuestros huéspedes'
      : 'Lo que dicen nuestros clientes';
  }

  get verTodasUrl(): string {
    return `https://search.google.com/local/reviews?placeid=${this.config.placeId}`;
  }

  estrellas(valor: number): boolean[] {
    const redondeado = Math.round(valor);
    return [1, 2, 3, 4, 5].map(n => n <= redondeado);
  }

  ngOnInit() {
    this.jsonService.readFromPanel<ResenasGoogle>(
      `resenas.php?sitio=${this.sitio}`,
      this.config.fallback
    ).subscribe({
      next: (data) => {
        this.rating = data?.rating ?? 0;
        this.total = data?.userRatingCount ?? 0;
        this.reviews = (data?.reviews ?? []).slice(0, 5);
        this.cargando = false;
      },
      error: () => {
        this.reviews = [];
        this.cargando = false;
        this.error = true;
      }
    });
  }
}
