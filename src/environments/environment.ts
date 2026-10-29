export const environment = {
  production: false,
  panelApiUrl: 'https://panel.lugrahotel.com.ar/api',
  recaptcha: {
    siteKey: '6LfSaXorAAAAALgtfbQ2bkVa-cgcrJeWiMjwC1kf' // Reemplaza con tu clave real
  },
  googleMaps: {
    apiKey: 'AIzaSyDH_KoSyWn69o039-OxCIgbckgYe3dPJ9w' // TODO: agrega tu Google Maps JavaScript API key
  },
  googleReviews: {
    hotel: {
      placeId: 'ChIJJ0IfTHwQhZURwapGFsTJqLU',
      fallback: '/assets/resenas-hotel.json'
    },
    inmobiliaria: {
      placeId: 'ChIJ21LSOXwQhZUR6drd0pVUTRQ',
      fallback: '/assets/resenas-inmobiliaria.json'
    }
  }
}; 