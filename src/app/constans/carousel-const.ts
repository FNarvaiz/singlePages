import { ICarouselItem } from '../carousel/carousel-item';

export const CAROUSEL_DATA_ITEMS: ICarouselItem[] = [
  {
    id: 1,
    title: {
      first: 'Welcome to',
      second: 'Angular'
    },
    subtitle: 'Angular is a platform that makes it easy to build applications with the web.',
    image: '',//'assets/carousel/slide1.jpg',
    link: 'https://angular.io/',
    order: 1
  },
  {
    id: 2,
    title: {
      first: 'Welcome to',
      second: 'React'
    },
    subtitle: 'React is a JavaScript library for building user interfaces.',
    image: '',//'assets/carousel/slide2.jpg',
    link: 'https://reactjs.org/',
    order: 2
  },
  {
    id: 3,
    title: {
      first: 'Welcome to',
      second: 'Vue'
    },
    subtitle: 'Vue is a progressive framework for building user interfaces.',
    image: '',//'assets/carousel/slide3.jpg',
    link: 'https://vuejs.org/',
    order: 3
  }
]
