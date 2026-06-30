import decorImage from '@/assets/images/Advantages/advantage-decor.png'

const includes = [
  {
    title: 'Замер',
    description: 'Лазерный замер с точностью до 0,1 мм',
  },
  {
    title: 'Дизайн и 3D',
    description: 'Разработка дизайн-проекта отделки',
  },
  {
    title: 'Основание',
    description: 'Выравнивание стен, пола, потолка',
  },
  {
    title: 'Черновая отделка',
    description: 'Монтаж каркасов и прокладка скрытых коммуникаций',
  },
  {
    title: 'Чистовая отделка',
    description: 'Покраску, монтаж акустических панелей, облицовку откосов',
  },
  {
    title: 'Монтаж мебели',
    description: 'Сборка и установка встроенных книжных стеллажей',
  },
  {
    title: 'Контроль и уборка',
    description: 'Проверка геометрии, зазоров, качества покрытий',
  },
  {
    title: 'Сопровождение',
    description: 'Гарантия на скрытые работы - 3 года',
  },
]

export default (props) => {
  const { data } = props
  const title = data?.title || 'Что входит в услугу'
  const items = data?.items?.length ? data.items : includes

  return (
    <section className="interior-includes container" aria-labelledby="interior-includes-title">
      <h2 className="interior-includes__title h2" id="interior-includes-title">
        {title}
      </h2>

      <div className="interior-includes__grid">
        {items.map(({ title, description }) => (
          <article className="interior-includes-card" key={title}>
            <div className="interior-includes-card__content">
              <h3 className="interior-includes-card__title h3">{title}</h3>
              <p className="interior-includes-card__description">{description}</p>
            </div>

            <img
              className="interior-includes-card__decor"
              src={decorImage}
              alt=""
              width="454"
              height="454"
              loading="lazy"
            />
          </article>
        ))}
      </div>
    </section>
  )
}
