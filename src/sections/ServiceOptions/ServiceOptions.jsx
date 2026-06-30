import Icon from '@/components/Icon'
import cardBg from '@/assets/images/ServiceOptions/card-bg.png'

const options = [
  {
    title: 'Интеграция с интерьером и экстерьером',
    description: 'Подберём профиль под ваши двери и окна',
    href: '/',
  },
  {
    title: 'Уникальные технологии и материалы',
    description: 'Лазерная резка и обработка от влаги',
    href: '/',
  },
]

export default () => {
  return (
    <section className="service-options" aria-labelledby="service-options-title">
      <div className="service-options__inner container">
        <h2 className="service-options__title h2" id="service-options-title">
          Дополнительные опции и возможности
        </h2>

        <ul className="service-options__list">
          {options.map(({ title, description, href }) => (
            <li
              className="service-options-card"
              style={{ '--card-bg': `url(${cardBg})` }}
              key={title}
            >
              <div className="service-options-card__content">
                <h3 className="service-options-card__title h3">{title}</h3>
                <p className="service-options-card__description">{description}</p>
              </div>

              <a
                className="service-options-card__link"
                href={href}
                aria-label={`${title}: подробнее`}
              >
                <span>Подробнее</span>
                <Icon className="service-options-card__icon" name="arrow-top-right" />
              </a>
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}
