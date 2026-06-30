import Button from '@/components/Button'
import Icon from '@/components/Icon'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'
import { getContactSocialLinks } from '@/lib/siteSettings'

export default () => {
  const settings = useSiteSettings()
  const socialItems = getContactSocialLinks(settings, { withYoutube: true })

  return (
    <section className="home-request container" aria-labelledby="home-request-title">
      <div className="home-request__inner">
        <div className="home-request__content">
          <div className="home-request__offer">
            <h2 className="home-request__title h2" id="home-request-title">
              Скидка 10% при оформлении комплексного заказа
            </h2>
            <p className="home-request__description">
              Действует только при полной оплате за проект
            </p>
          </div>

          <ul className="home-request__socials" aria-label="Способы связи">
            {socialItems.map(({ name, label, href }) => (
              <li className="home-request__social-item" key={name}>
                <a className="home-request__social-link" href={href} aria-label={label}>
                  <Icon className="home-request__social-icon" name={name} hasFill />
                </a>
              </li>
            ))}
          </ul>
        </div>

        <form className="home-request__form" action="/" method="post">
          <input
            className="home-request__control"
            name="name"
            placeholder="Григорий"
            aria-label="Ваше имя"
          />
          <input
            className="home-request__control"
            name="phone"
            placeholder={settings.phone}
            aria-label="Телефон"
            inputMode="tel"
            data-js-input-mask="+{7} (000) 000-00-00"
          />
          <textarea
            className="home-request__control home-request__control--textarea"
            name="comment"
            placeholder="Комментарий"
            aria-label="Комментарий"
          />
          <Button className="home-request__button" type="submit">
            Записаться на замер
          </Button>
          <p className="home-request__privacy">
            Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.
          </p>
        </form>
      </div>
    </section>
  )
}
