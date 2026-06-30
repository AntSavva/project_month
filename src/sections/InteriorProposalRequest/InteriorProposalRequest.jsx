import Button from '@/components/Button'
import Icon from '@/components/Icon'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'
import { getContactSocialLinks } from '@/lib/siteSettings'

export default () => {
  const settings = useSiteSettings()
  const socialItems = getContactSocialLinks(settings, { withYoutube: true })

  return (
    <section
      className="interior-proposal-request container"
      aria-labelledby="interior-proposal-request-title"
    >
      <div className="interior-proposal-request__inner">
        <div className="interior-proposal-request__content">
          <div className="interior-proposal-request__offer">
            <h2 className="interior-proposal-request__title h2" id="interior-proposal-request-title">
              Подготовим детальное коммерческое предложение
            </h2>
            <p className="interior-proposal-request__description">
              Оперативный выезд на замер и скидка 10%
            </p>
          </div>

          <div className="interior-proposal-request__contacts">
            <p className="interior-proposal-request__contacts-text">
              Оставьте заявку или свяжитесь с нами удобным способом
            </p>
            <ul className="interior-proposal-request__socials" aria-label="Способы связи">
              {socialItems.map(({ name, label, href }) => (
                <li className="interior-proposal-request__social-item" key={name}>
                  <a className="interior-proposal-request__social-link" href={href} aria-label={label}>
                    <Icon className="interior-proposal-request__social-icon" name={name} hasFill />
                  </a>
                </li>
              ))}
            </ul>
          </div>
        </div>

        <form className="interior-proposal-request__form" action="/" method="post">
          <input
            className="interior-proposal-request__control"
            name="name"
            placeholder="Григорий"
            aria-label="Ваше имя"
          />
          <input
            className="interior-proposal-request__control"
            name="phone"
            placeholder={settings.phone}
            aria-label="Телефон"
            inputMode="tel"
            data-js-input-mask="+{7} (000) 000-00-00"
          />
          <textarea
            className="interior-proposal-request__control interior-proposal-request__control--textarea"
            name="comment"
            placeholder="Комментарий"
            aria-label="Комментарий"
          />
          <Button className="interior-proposal-request__button" type="submit">
            Записаться на замер
          </Button>
          <p className="interior-proposal-request__privacy">
            Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.
          </p>
        </form>
      </div>
    </section>
  )
}
