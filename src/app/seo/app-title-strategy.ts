import { inject, Injectable } from '@angular/core';
import { RouterStateSnapshot, TitleStrategy } from '@angular/router';
import { Meta, Title } from '@angular/platform-browser';

@Injectable()
export class AppTitleStrategy extends TitleStrategy {
  private readonly title = inject(Title);
  private readonly meta = inject(Meta);

  override updateTitle(snapshot: RouterStateSnapshot): void {
    const title = this.buildTitle(snapshot);
    if (title) {
      this.title.setTitle(title);
    }

    // Find the deepest activated route to read description
    let route = snapshot.root;
    while (route.firstChild) route = route.firstChild;
    const description = route.data && (route.data['description'] as string);
    if (typeof description === 'string' && description.trim().length > 0) {
      this.meta.updateTag({ name: 'description', content: description.trim() });
    }
  }
}

