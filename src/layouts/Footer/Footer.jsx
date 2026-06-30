import Icon from '@/components/Icon'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'
import { getEmailHref, getPhoneHref } from '@/lib/siteSettings'

const socialItems = [
  {
    name: 'vk',
    label: 'ВКонтакте',
  },
  {
    name: 'telegram',
    label: 'Telegram',
  },
  {
    name: 'youtube',
    label: 'YouTube',
  },
  {
    name: 'max',
    label: 'Max',
  },
]

export default () => {
  const settings = useSiteSettings()
  const productItems = settings.pages.filter((page) => page.type === 'product')
  const interiorItems = settings.pages.filter((page) => page.type === 'interior')
  const documentItems = settings.pages.filter((page) => page.type === 'document')

  return (
    <footer className="footer">
      <div className="footer__inner container">
        <div className="footer__main">
          <section className="footer__section">
            <h2 className="footer__title">Юридическая информация</h2>
            <p className="footer__text">
              {settings.legalInfo.split('\n').map((line) => (
                <span key={line}>
                  {line}
                  <br />
                </span>
              ))}
            </p>

            <h2 className="footer__title footer__title--spaced">Телефон</h2>
            <a className="footer__link" href={getPhoneHref(settings.phone)}>
              {settings.phone}
            </a>

            <h2 className="footer__title footer__title--spaced">Почта</h2>
            <a className="footer__link" href={getEmailHref(settings.email)}>
              {settings.email}
            </a>

            <h2 className="footer__title footer__title--spaced">Соц сети</h2>
            <ul className="footer__socials" aria-label="Социальные сети">
              {socialItems.map(({ name, label }) => (
                <li className="footer__social-item" key={name}>
                  <a
                    className="footer__social-link"
                    href={settings.socials[name] || '/'}
                    aria-label={label}
                  >
                    <Icon className="footer__social-icon" name={name} hasFill />
                  </a>
                </li>
              ))}
            </ul>
          </section>

          <nav className="footer__section footer__section--products" aria-label="Продукция">
            {productItems.length > 0 && (
              <>
                <h2 className="footer__title">Продукция</h2>
                <ul className="footer__list footer__list--products">
                  {productItems.map((page) => (
                    <li className="footer__item" key={page.id}>
                      <a className="footer__link" href={page.href}>
                        {page.title}
                      </a>
                    </li>
                  ))}
                </ul>
              </>
            )}
          </nav>

          {interiorItems.length > 0 && (
            <nav className="footer__section footer__section--interior" aria-label="Интерьер">
              <h2 className="footer__title">Интерьер</h2>
              <ul className="footer__list">
                {interiorItems.map((page) => (
                  <li className="footer__item" key={page.id}>
                    <a className="footer__link" href={page.href}>
                      {page.title}
                    </a>
                  </li>
                ))}
              </ul>
            </nav>
          )}
        </div>

        {documentItems.length > 0 && (
          <nav className="footer__documents" aria-label="Документы">
            <ul className="footer__documents-list">
              {documentItems.map((page) => (
                <li className="footer__documents-item" key={page.id}>
                  <a className="footer__documents-link" href={page.href}>
                    {page.title}
                  </a>
                </li>
              ))}
            </ul>
          </nav>
        )}

        <div className="footer__bottom">
          <p className="footer__copyright">Все права зарегистрированы</p>
          <a className="footer__developer" href="/">
            Разработка сайта
          </a>
        </div>
      </div>
    </footer>
  )
}
