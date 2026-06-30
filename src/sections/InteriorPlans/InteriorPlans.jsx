import Button from '@/components/Button'
import { box, medal, star } from '@/assets/images/CardIcons'

const planIcons = [box, star, medal]

const fallbackPlans = [
  {
    title: 'Стандарт',
    items: [
      'Мебельный щит из сосны 18 мм',
      'сорт экстра - без сучков и дефектов',
      'От 950 ₽/пог',
      'От 5 800 ₽ за окно',
    ],
  },
  {
    title: 'Премиум',
    items: [
      'Дуб, бук, ясень, сосна, лиственница, орех',
      'сорт экстра - без сучков и дефектов',
      'От 1 750 ₽/пог',
      'От 10 500 ₽ за окно',
    ],
    isFeatured: true,
  },
  {
    title: 'Эксклюзив',
    items: [
      'Фигурные, резные, рельефные наличники по желанию клиента',
      'Дуб, бук, ясень, сосна, лиственница, орех',
      'Сорт А - с сучками',
      'От 2 500 ₽/пог',
      'От 15 000 ₽ за окно',
    ],
  },
]

export default (props) => {
  const { data } = props
  const title = data?.title || 'Варианты сотрудничества'
  const hasCustomItems = Array.isArray(data?.items)
  const items = hasCustomItems ? data.items : fallbackPlans

  if (!items.length) {
    return null
  }

  return (
    <section className="interior-plans container" aria-labelledby="interior-plans-title">
      <h2 className="interior-plans__title h2" id="interior-plans-title">
        {title}
      </h2>

      <div className="interior-plans__grid">
        {items.map(({ title, items, isFeatured }, index) => (
          <article
            className={isFeatured ? 'interior-plans-card interior-plans-card--featured' : 'interior-plans-card'}
            key={`${title}-${index}`}
          >
            <div className="interior-plans-card__body">
              <div className="interior-plans-card__content">
                <img
                  className="interior-plans-card__icon"
                  src={planIcons[index] || planIcons[0]}
                  alt=""
                  width="100"
                  height="100"
                  loading="lazy"
                />

                <h3 className="interior-plans-card__title h3">{title}</h3>

                <ul className="interior-plans-card__items">
                  {(items || []).map((item) => (
                    <li className="interior-plans-card__item" key={item}>
                      {item}
                    </li>
                  ))}
                </ul>
              </div>

              <Button className="interior-plans-card__button" href="/">
                Записаться
              </Button>
            </div>
          </article>
        ))}
      </div>
    </section>
  )
}
