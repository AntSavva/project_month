import Icon from '@/components/Icon'
import cardBg from '@/assets/images/ServiceOptions/card-bg.png'

const advantages = [
  {
    title: 'Натуральные материалы',
    description: 'Используем массив дерева, безопасные краски и клеи',
  },
  {
    title: 'Шумоизоляция',
    description: 'Звукопоглощающие панели, мягкие покрытия',
  },
  {
    title: 'Сохранность имущества',
    description: 'Обеспечиваем стабильную влажность и температуру',
  },
  {
    title: 'Скрытые коммуникации',
    description: 'Прячем все провода, кабели, розетки и климат-оборудование',
  },
]

export default (props) => {
  const { data } = props
  const title = data?.title || 'Преимущества'
  const description =
    data?.description ||
    'Акцент на эргономику, зонирование, нормы безопасности, индивидуальный дизайн и интеграция с интерьером'
  const items = data?.items?.length ? data.items : advantages

  return (
    <section className="interior-advantages container" aria-labelledby="interior-advantages-title">
      <header className="interior-advantages__header">
        <h2 className="interior-advantages__title h2" id="interior-advantages-title">
          {title}
        </h2>
        <p className="interior-advantages__description">{description}</p>
      </header>

      <div className="interior-advantages__grid">
        {items.map(({ title, description }) => (
          <article
            className="interior-advantages-card"
            style={{ '--card-bg': `url(${cardBg})` }}
            key={title}
          >
            <div className="interior-advantages-card__content">
              <h3 className="interior-advantages-card__title h3">{title}</h3>
              <p className="interior-advantages-card__description">{description}</p>
            </div>

            <a className="interior-advantages-card__link" href="/" aria-label={`${title}: подробнее`}>
              <span>Подробнее</span>
              <Icon className="interior-advantages-card__icon" name="arrow-top-right" />
            </a>
          </article>
        ))}
      </div>
    </section>
  )
}
