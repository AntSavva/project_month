import Button from '@/components/Button'
import heroImage from '@/assets/images/ServiceHero/hero-image.png'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'

export default (props) => {
  const settings = useSiteSettings()
  const { data } = props
  const subtitle = data?.subtitle || 'Внутренняя отделка'
  const title = data?.title || 'Эргономичные пространства'
  const accent = data?.accent || 'библиотеки, кабинеты, офисы'
  const image = data?.image || heroImage

  return (
    <section className="interior-hero" aria-labelledby="interior-hero-title">
      <div className="interior-hero__inner container">
        <div className="interior-hero__content">
          <p className="interior-hero__eyebrow">{subtitle}</p>
          <h1 className="interior-hero__title h1" id="interior-hero-title">
            <span>{title}</span>
            <span className="interior-hero__title-accent">{accent}</span>
          </h1>

          <form className="interior-hero__form" action="/" method="post">
            <div className="interior-hero__fields">
              <input
                className="interior-hero__control"
                name="name"
                placeholder="Григорий"
                aria-label="Ваше имя"
              />
              <input
                className="interior-hero__control"
                name="phone"
                placeholder={settings.phone}
                aria-label="Телефон"
                inputMode="tel"
                data-js-input-mask="+{7} (000) 000-00-00"
              />
              <Button className="interior-hero__button" type="submit">
                Записаться на замер
              </Button>
            </div>

            <p className="interior-hero__privacy">
              Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.
            </p>
          </form>
        </div>

        <img
          className="interior-hero__image"
          src={image}
          alt=""
          width="730"
          height="730"
          loading="eager"
        />
      </div>
    </section>
  )
}
