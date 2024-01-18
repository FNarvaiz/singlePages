export interface ICarouselItem {
    id: number;
    title?: {
      first: string;
      second: string;
    };
    subtitle?: string;
    image: string;
    link?: string;
    order?: number;
    marginLeft?: number;
}
