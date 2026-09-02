import { Component, Input, AfterViewInit, Renderer2, ElementRef, inject, NgZone } from '@angular/core';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';

@Component({
  selector: 'app-opiniones',
  standalone: true,
  imports: [],
  templateUrl: './opiniones.component.html',
  styleUrl: './opiniones.component.css'
})
export class OpinionesComponent implements AfterViewInit {
  private renderer = inject(Renderer2);
  private el = inject(ElementRef);
  private sanitizer = inject(DomSanitizer);
  private zone = inject(NgZone);

  @Input() elfsightWidgetId = '';
  @Input() embedUrl = '';

  fallbackActivo = false;
  private fallbackTimer: ReturnType<typeof setTimeout> | null = null;

  get safeEmbedUrl(): SafeResourceUrl | null {
    if (!this.embedUrl) return null;
    return this.sanitizer.bypassSecurityTrustResourceUrl(this.embedUrl);
  }

  ngAfterViewInit() {
    if (this.elfsightWidgetId) {
      this.cargarElfsightConFallback();
    }
  }

  private cargarElfsightConFallback() {
    const container = this.el.nativeElement.querySelector('#elfsight-container');
    if (container) {
      const widgetDiv = this.renderer.createElement('div');
      this.renderer.setAttribute(widgetDiv, 'class', `elfsight-app-${this.elfsightWidgetId}`);
      this.renderer.appendChild(container, widgetDiv);
    }

    const script = this.renderer.createElement('script');
    this.renderer.setAttribute(script, 'src', 'https://static.elfsight.com/platform/platform.js');
    this.renderer.setAttribute(script, 'async', 'true');

    // Si el script falla en cargar (error de red), fallback inmediato
    this.renderer.listen(script, 'error', () => {
      this.activarFallback();
    });

    this.renderer.appendChild(document.body, script);

    // Fallback por timeout: si después de 8s el widget no renderizó contenido
    this.zone.runOutsideAngular(() => {
      this.fallbackTimer = setTimeout(() => {
        const widget = this.el.nativeElement.querySelector(`.elfsight-app-${this.elfsightWidgetId}`);
        // Elfsight renderiza iframes o múltiples divs dentro del widget. Si sigue vacío, no cargó.
        const renderizo = widget && (
          widget.querySelector('iframe') != null ||
          widget.children.length > 0
        );
        if (!renderizo && this.embedUrl) {
          this.zone.run(() => { this.fallbackActivo = true; });
        }
      }, 8000);
    });
  }

  private activarFallback() {
    if (this.fallbackTimer) { clearTimeout(this.fallbackTimer); }
    if (this.embedUrl) {
      this.zone.run(() => { this.fallbackActivo = true; });
    }
  }
}
