import Button from '@/components/Button'
import Icon from '@/components/Icon'
import managerPhoto from '@/assets/images/Questions/manager-photo.png'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'
import { getContactSocialLinks } from '@/lib/siteSettings'

export default () => {
  const settings = useSiteSettings()
  const socialLinks = getContactSocialLinks(settings, { withYoutube: true })

  return (
    <section className="interior-questions container" aria-labelledby="interior-questions-title">
      <div className="interior-questions__inner">
        <div className="interior-questions__content">
          <div className="interior-questions__header">
            <h2 className="interior-questions__title h2" id="interior-questions-title">
              Остались вопросы?
            </h2>
            <p className="interior-questions__description">
              Оставьте заявку или свяжитесь с нами удобным способом
            </p>
          </div>

          <ul className="interior-questions__socials" aria-label="Способы связи">
            {socialLinks.map(({ name, label, href }) => (
              <li className="interior-questions__social-item" key={name}>
                <a className="interior-questions__social-link" href={href} aria-label={label}>
                  <Icon className="interior-questions__social-icon" name={name} hasFill />
                </a>
              </li>
            ))}
          </ul>

          <div className="interior-questions__manager">
            <img
              className="interior-questions__manager-photo"
              src={managerPhoto}
              alt=""
              width="80"
              height="80"
              loading="lazy"
            />
            <div className="interior-questions__manager-info">
              <p className="interior-questions__manager-name">Григорий Карпинский</p>
              <p className="interior-questions__manager-position">Менеджер продаж</p>
            </div>
          </div>
        </div>

        <form className="interior-questions__form" action="/" method="post">
          <input
            className="interior-questions__control"
            name="name"
            placeholder="Григорий"
            aria-label="Ваше имя"
          />
          <input
            className="interior-questions__control"
            name="phone"
            placeholder={settings.phone}
            aria-label="Телефон"
            inputMode="tel"
            data-js-input-mask="+{7} (000) 000-00-00"
          />
          <textarea
            className="interior-questions__control interior-questions__control--textarea"
            name="comment"
            placeholder="Комментарий"
            aria-label="Комментарий"
          />
          <Button className="interior-questions__button" type="submit">
            Записаться на замер
          </Button>
          <p className="interior-questions__privacy">
            Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.
          </p>
        </form>
      </div>
    </section>
  )
}
