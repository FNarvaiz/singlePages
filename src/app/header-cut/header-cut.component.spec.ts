import { ComponentFixture, TestBed } from '@angular/core/testing';

import { HeaderCutComponent } from './header-cut.component';

describe('HeaderCutComponent', () => {
  let component: HeaderCutComponent;
  let fixture: ComponentFixture<HeaderCutComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HeaderCutComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(HeaderCutComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
