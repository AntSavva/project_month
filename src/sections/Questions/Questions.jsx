import Button from '@/components/Button'
import Icon from '@/components/Icon'
import managerPhoto from '@/assets/images/Questions/manager-photo.png'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'
import { getContactSocialLinks } from '@/lib/siteSettings'

export default () => {
  const settings = useSiteSettings()
  const socialLinks = getContactSocialLinks(settings)

  return (
    <section className="questions container" aria-labelledby="questions-title">
      <div className="questions__inner">
        <div className="questions__content">
          <div className="questions__header">
            <h2 className="questions__title h2" id="questions-title">
              Остались вопросы?
            </h2>
            <p className="questions__description">
              Оставьте заявку или свяжитесь с нами удобным способом
            </p>
          </div>

          <ul className="questions__socials" aria-label="Способы связи">
            {socialLinks.map(({ name, label, href }) => (
              <li className="questions__social-item" key={name}>
                <a className="questions__social-link" href={href} aria-label={label}>
                  <Icon className="questions__social-icon" name={name} hasFill />
                </a>
              </li>
            ))}
          </ul>

          <div className="questions__manager">
            <img
              className="questions__manager-photo"
              src={managerPhoto}
              alt=""
              width="96"
              height="96"
              loading="lazy"
            />
            <div className="questions__manager-info">
              <p className="questions__manager-name">Григорий Карпинский</p>
              <p className="questions__manager-position">Менеджер продаж</p>
            </div>
          </div>
        </div>

        <form className="questions__form" action="/" method="post">
          <input
            className="questions__control"
            name="name"
            placeholder="Григорий"
            aria-label="Ваше имя"
          />
          <input
            className="questions__control"
            name="phone"
            placeholder={settings.phone}
            aria-label="Телефон"
            inputMode="tel"
            data-js-input-mask="+{7} (000) 000-00-00"
          />
          <textarea
            className="questions__control questions__control--textarea"
            name="comment"
            placeholder="Комментарий"
            aria-label="Комментарий"
          />
          <Button className="questions__button" type="submit">
            Записаться на замер
          </Button>
          <p className="questions__privacy">
            Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности
          </p>
        </form>
      </div>
    </section>
  )
}
