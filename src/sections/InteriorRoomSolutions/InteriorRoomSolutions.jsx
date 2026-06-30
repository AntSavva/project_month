import cardBg from '@/assets/images/ServiceOptions/card-bg.png'

const solutions = [
  {
    title: 'Для библиотек',
    items: [
      'Влажностный режим (40-60%)',
      'Антибликовое освещение',
      'Усиленные полы под тяжелые стеллажи',
    ],
  },
  {
    title: 'Для кабинетов',
    items: [
      'Премиальные материалы',
      'Акустический комфорт',
      'Скрытая проводка',
      'Климат-контроль',
    ],
  },
  {
    title: 'Для офисов',
    items: [
      'Износостойкие покрытия',
      'Простота уборки и ремонта',
      'Соблюдение пожарных норм',
      'Зонирование звукопоглощающими перегородками',
    ],
  },
]

export default (props) => {
  const { data } = props
  const title =
    data?.title || 'Специальные решения для вашего типа помещения'
  const items = data?.items?.length ? data.items : solutions

  return (
    <section
      className="interior-room-solutions container"
      aria-labelledby="interior-room-solutions-title"
    >
      <h2 className="interior-room-solutions__title h2" id="interior-room-solutions-title">
        {title}
      </h2>

      <div className="interior-room-solutions__grid">
        {items.map(({ title, items }) => (
          <article
            className="interior-room-solutions-card"
            style={{ '--card-bg': `url(${cardBg})` }}
            key={title}
          >
            <h3 className="interior-room-solutions-card__title h3">{title}</h3>
            <ul className="interior-room-solutions-card__list">
              {(items || []).map((item) => (
                <li className="interior-room-solutions-card__item" key={item}>
                  {item}
                </li>
              ))}
            </ul>
          </article>
        ))}
      </div>
    </section>
  )
}
