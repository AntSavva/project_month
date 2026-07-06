import Button from '@/components/Button'
import Icon from '@/components/Icon'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'
import { getContactSocialLinks } from '@/lib/siteSettings'

export default () => {
  const settings = useSiteSettings()
  const socialItems = getContactSocialLinks(settings)

  return (
    <section className="service-request container" aria-labelledby="service-request-title">
      <div className="service-request__inner">
        <div className="service-request__content">
          <div className="service-request__offer">
            <h2 className="service-request__title h2" id="service-request-title">
              Наличники на весь дом внутренние и наружные
            </h2>
            <ul className="service-request__benefits">
              <li>Оперативный выезд на замер</li>
              <li>Скидка 10%</li>
            </ul>
          </div>

          <div className="service-request__contacts">
            <p className="service-request__description">
              Оставьте заявку или свяжитесь с нами удобным способом
            </p>

            <ul className="service-request__socials" aria-label="Способы связи">
              {socialItems.map(({ name, label, href }) => (
                <li className="service-request__social-item" key={name}>
                  <a className="service-request__social-link" href={href} aria-label={label}>
                    <Icon className="service-request__social-icon" name={name} hasFill />
                  </a>
                </li>
              ))}
            </ul>
          </div>
        </div>

        <form className="service-request__form" action="/" method="post">
          <input
            className="service-request__control"
            name="name"
            placeholder="Григорий"
            aria-label="Ваше имя"
          />
          <input
            className="service-request__control"
            name="phone"
            placeholder={settings.phone}
            aria-label="Телефон"
            inputMode="tel"
            data-js-input-mask="+{7} (000) 000-00-00"
          />
          <textarea
            className="service-request__control service-request__control--textarea"
            name="comment"
            placeholder="Комментарий"
            aria-label="Комментарий"
          />
          <Button className="service-request__button" type="submit">
            Записаться на замер
          </Button>
          <p className="service-request__privacy">
            Нажимая на кнопку, вы соглашаетесь с{' '}
            <a href="/privacy-policy">политикой конфиденциальности</a>.
          </p>
        </form>
      </div>
    </section>
  )
}
