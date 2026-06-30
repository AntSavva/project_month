import { car, cardIcons, level, machine, roulette } from '@/assets/images/CardIcons'

const includesIcons = [roulette, machine, car, level]

const fallback = {
  title: 'Что входит в услугу',
  items: [
    { title: 'Замер', description: 'Лазерный замер с точностью до 0,1 мм' },
    { title: 'Производство', description: 'Станки ЧПУ и ручная подгонка деталей' },
    { title: 'Доставка', description: 'Разгрузка и подъём к объекту' },
    { title: 'Установка', description: 'Без повреждений чистовой отделки' },
  ],
}

export default (props) => {
  const data = props.data || fallback
  const description = data.description || ''

  return (
    <section className="service-includes container" aria-labelledby="service-includes-title">
      <header className="service-includes__header">
        <h2 className="service-includes__title h2" id="service-includes-title">
          {data.title}
        </h2>
        {description && <p className="service-includes__description">{description}</p>}
      </header>

      <ul className="service-includes__list">
        {data.items.map(({ title, description, icon }, index) => (
          <li className="service-includes__item" key={title}>
            <article className="service-includes-card">
              <div className="service-includes-card__content">
                <h3 className="service-includes-card__title h3">{title}</h3>
                <p className="service-includes-card__description">{description}</p>
              </div>
              <picture className="service-includes-card__picture">
                <img
                  className="service-includes-card__image"
                  src={cardIcons[icon] || includesIcons[index] || includesIcons[0]}
                  alt=""
                  width="454"
                  height="454"
                  loading="lazy"
                />
              </picture>
            </article>
          </li>
        ))}
      </ul>
    </section>
  )
}
