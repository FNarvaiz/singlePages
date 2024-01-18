import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GalleryHorizontalParalaxComponent } from './gallery-horizontal-paralax.component';

describe('GalleryHorizontalParalaxComponent', () => {
  let component: GalleryHorizontalParalaxComponent;
  let fixture: ComponentFixture<GalleryHorizontalParalaxComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GalleryHorizontalParalaxComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(GalleryHorizontalParalaxComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
