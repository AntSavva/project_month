export const defaultSiteSettings = {
  phone: '+7-981-887-28-04',
  email: 'kuberaspb@mail.ru',
  address: 'Ленинградская область, Всеволожский район, д. Порошкино, ул. Дорожников, 20А',
  workingHours: 'Пн-Пт 10:00-19:00',
  legalInfo: 'Полное юридическое название,\nИНН,\nОГРН',
  socials: {
    vk: '/',
    telegram: '/',
    youtube: '/',
    max: '/',
  },
  pages: [],
}

export const getPhoneHref = (phone = '') => {
  const normalized = phone.replace(/[^\d+]/g, '')

  return normalized ? `tel:${normalized}` : '/'
}

export const getEmailHref = (email = '') => (email ? `mailto:${email}` : '/')

export const getSettingsWithDefaults = (settings = {}) => ({
  ...defaultSiteSettings,
  ...settings,
  socials: {
    ...defaultSiteSettings.socials,
    ...(settings.socials || {}),
  },
  pages: Array.isArray(settings.pages) ? settings.pages : [],
})

export const getContactSocialLinks = (settings = {}, options = {}) => {
  const nextSettings = getSettingsWithDefaults(settings)
  const items = [
    {
      name: 'vk',
      label: 'ВКонтакте',
      href: nextSettings.socials.vk,
    },
    {
      name: 'telegram',
      label: 'Telegram',
      href: nextSettings.socials.telegram,
    },
    {
      name: 'mail',
      label: 'Email',
      href: getEmailHref(nextSettings.email),
    },
    {
      name: 'phone',
      label: 'Телефон',
      href: getPhoneHref(nextSettings.phone),
    },
  ]

  if (options.withYoutube) {
    items.push({
      name: 'youtube',
      label: 'YouTube',
      href: nextSettings.socials.youtube,
    })
  }

  if (options.withMax) {
    items.push({
      name: 'max',
      label: 'Max',
      href: nextSettings.socials.max,
    })
  }

  return items
}
