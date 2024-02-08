import { ComponentFixture, TestBed } from '@angular/core/testing';

import { HeaderMinimalComponent } from './header-minimal.component';

describe('HeaderMinimalComponent', () => {
  let component: HeaderMinimalComponent;
  let fixture: ComponentFixture<HeaderMinimalComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HeaderMinimalComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(HeaderMinimalComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
