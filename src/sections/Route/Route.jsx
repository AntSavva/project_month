import Icon from '@/components/Icon'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'

export default () => {
  const settings = useSiteSettings()

  return (
    <section className="route container" aria-labelledby="route-title">
      <div className="route__header">
        <h2 className="route__title h2" id="route-title">
          Как к нам добраться?
        </h2>

        <div className="route__info">
          <address className="route__address">
            {settings.address}
            <br />
            {settings.workingHours}
          </address>

          <a
            className="route__link"
            href="https://yandex.ru/maps/-/CPcFYA8G"
            target="_blank"
            rel="noreferrer"
          >
            <span>Построить маршрут</span>
            <span className="route__link-icon" aria-hidden="true">
              <Icon name="location-minus" />
            </span>
          </a>
        </div>
      </div>

      <div className="route__map">
        <iframe
          className="route__map-iframe"
          src="https://yandex.ru/map-widget/v1/?um=constructor%3A0ac116e0529e203ff85ff75480538831d74af8ef092c310fd14db9f30abf4248&amp;source=constructor"
          width="649"
          height="495"
          frameBorder="0"
          title="Карта проезда до производства Кубэра"
        />
      </div>
    </section>
  )
}
