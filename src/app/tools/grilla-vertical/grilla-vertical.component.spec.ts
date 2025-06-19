import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GrillaVerticalComponent } from './grilla-vertical.component';

describe('GrillaVerticalComponent', () => {
  let component: GrillaVerticalComponent;
  let fixture: ComponentFixture<GrillaVerticalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GrillaVerticalComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(GrillaVerticalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
