import Icon from '@/components/Icon'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'
import { getEmailHref, getPhoneHref } from '@/lib/siteSettings'

export default () => {
  const settings = useSiteSettings()
  const contactCards = [
    {
      icon: 'phone',
      title: 'Телефон отдела продаж',
      value: settings.phone,
      href: getPhoneHref(settings.phone),
    },
    {
      icon: 'mail',
      title: 'E-mail',
      value: settings.email,
      href: getEmailHref(settings.email),
    },
    {
      icon: 'telegram',
      title: 'Telegram для связи',
      value: 'Ссылка',
      href: settings.socials.telegram || '/',
    },
    {
      icon: 'max',
      title: 'Мессенджер MAX',
      value: 'Ссылка',
      href: settings.socials.max || '/',
    },
  ]

  return (
    <section className="contacts-hero" aria-labelledby="contacts-hero-title">
      <div className="contacts-hero__inner container">
        <div className="contacts-hero__content">
          <p className="contacts-hero__subtitle">Контакты</p>
          <h1 className="contacts-hero__title h1" id="contacts-hero-title">
            Всегда остаемся на связи
          </h1>
        </div>

        <ul className="contacts-hero__list">
          {contactCards.map(({ icon, title, value, href }) => (
            <li className="contacts-hero__item" key={title}>
              <a className="contacts-hero-card" href={href}>
                <span className="contacts-hero-card__icon" aria-hidden="true">
                  <Icon name={icon} hasFill />
                </span>
                <span className="contacts-hero-card__content">
                  <span className="contacts-hero-card__title">{title}</span>
                  <span className="contacts-hero-card__value">{value}</span>
                </span>
              </a>
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}
