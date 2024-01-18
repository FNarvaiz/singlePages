import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GalleryCroquelComponent } from './gallery-croquel.component';

describe('GalleryCroquelComponent', () => {
  let component: GalleryCroquelComponent;
  let fixture: ComponentFixture<GalleryCroquelComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GalleryCroquelComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(GalleryCroquelComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
