export const interiorCoverFallbacks = {
  interior: '/assets/source/images/InteriorCards/library.png',
  'restorany-bary-kafe': '/assets/source/images/InteriorCards/restoraunt.png',
  'bani-i-bannye-kompleksy': '/assets/source/images/InteriorCards/sauna.png',
}

export const getInteriorCover = (page = {}) =>
  page.cover || interiorCoverFallbacks[page.slug] || ''
