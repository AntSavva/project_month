import Button from '@/components/Button'
import heroImage from '@/assets/images/ServiceHero/hero-image.png'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'

export default (props) => {
  const { data, image } = props
  const settings = useSiteSettings()
  const coverImage = image || heroImage
  const hero = data || {
    subtitle: 'Столярные изделия',
    title: 'Наличники и подоконники',
    accent: 'из дерева для вашего дома',
  }

  return (
    <section className="service-hero" aria-labelledby="service-hero-title">
      <div className="service-hero__inner container">
        <div className="service-hero__content">
          <p className="service-hero__subtitle">{hero.subtitle}</p>
          <h1 className="service-hero__title h1" id="service-hero-title">
            {hero.title}
            <span> {hero.accent}</span>
          </h1>

          <form className="service-hero__form" action="/" method="post">
            <div className="service-hero__fields">
              <input
                className="service-hero__control"
                name="name"
                placeholder="Григорий"
                aria-label="Ваше имя"
              />
              <input
                className="service-hero__control"
                name="phone"
                placeholder={settings.phone}
                aria-label="Телефон"
                inputMode="tel"
                data-js-input-mask="+{7} (000) 000-00-00"
              />
              <textarea
                className="service-hero__control service-hero__control--textarea"
                name="comment"
                placeholder="Комментарий"
                aria-label="Комментарий"
              />
              <Button className="service-hero__button" type="submit">
                Записаться на замер
              </Button>
            </div>

            <p className="service-hero__privacy">
              Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.
            </p>
          </form>
        </div>

        <img
          className="service-hero__image"
          src={coverImage}
          alt=""
          width="730"
          height="730"
          loading="eager"
        />
      </div>
    </section>
  )
}
