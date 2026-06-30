import Icon from '@/components/Icon'
import { cardIcons, shieldPlain, star, medal, person } from '@/assets/images/CardIcons'

const benefitIcons = [shieldPlain, star, medal, person]

const fallbackItems = [
  {
    title: 'Эстетика',
    description: 'Чистые линии без видимых стыков',
  },
  {
    title: 'Функциональность',
    description: 'Перекрывают монтажные зазоры от пыли',
  },
  {
    title: 'Долговечность',
    description: 'Пропитка от гниения и насекомых',
  },
  {
    title: 'Уникальность',
    description: 'Фрезеровка любой сложности',
  },
]

export default (props) => {
  const { data } = props
  const title = data?.title || 'Преимущества'
  const description =
    data?.description ||
    'Наличники из дерева - это не просто декоративный элемент, а важная функциональная и эстетическая часть оконного или дверного проема'
  const items = data?.items?.length ? data.items : fallbackItems

  return (
    <section className="service-benefits" aria-labelledby="service-benefits-title">
      <div className="service-benefits__inner container">
        <header className="service-benefits__header">
          <h2 className="service-benefits__title h2" id="service-benefits-title">
            {title}
          </h2>
          <p className="service-benefits__description">{description}</p>
        </header>

        <ul className="service-benefits__list">
          {items.map(({ title, description, icon, items }, index) => {
            const cardItems = items?.length ? items : [description].filter(Boolean)

            return (
              <li className="service-benefits-card" key={title}>
                <img
                  className="service-benefits-card__decor"
                  src={cardIcons[icon] || benefitIcons[index] || benefitIcons[0]}
                  alt=""
                  width="150"
                  height="150"
                  loading="lazy"
                />
                <div className="service-benefits-card__content">
                  <h3 className="service-benefits-card__title h3">{title}</h3>
                  <ul className="service-benefits-card__items">
                    {cardItems.map((item) => (
                      <li className="service-benefits-card__item" key={item}>
                        {item}
                      </li>
                    ))}
                  </ul>
                </div>

                <a
                  className="service-benefits-card__link"
                  href="/"
                  aria-label={`${title}: подробнее`}
                >
                  <span>Подробнее</span>
                  <Icon className="service-benefits-card__icon" name="arrow-top-right" />
                </a>
              </li>
            )
          })}
        </ul>
      </div>
    </section>
  )
}
