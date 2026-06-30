import Button from '@/components/Button'
import { box, cardIcons, medal, star } from '@/assets/images/CardIcons'

const planIcons = [box, star, medal]

const fallbackItems = [
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
  const items = data?.items?.length ? data.items : fallbackItems

  return (
    <section className="service-plans" aria-labelledby="service-plans-title">
      <div className="service-plans__inner container">
        <h2 className="service-plans__title h2" id="service-plans-title">
          {title}
        </h2>

        <ul className="service-plans__list">
          {items.map(({ title, icon, items }, index) => (
            <li className="service-plans-card" key={title}>
              <div className="service-plans-card__body">
                <div className="service-plans-card__content">
                  <img
                    className="service-plans-card__icon"
                    src={cardIcons[icon] || planIcons[index] || planIcons[0]}
                    alt=""
                    width="100"
                    height="100"
                    loading="lazy"
                  />

                  <h3 className="service-plans-card__title h3">{title}</h3>

                  <ul className="service-plans-card__items">
                    {(items || []).map((item) => (
                      <li className="service-plans-card__item" key={item}>
                        {item}
                      </li>
                    ))}
                  </ul>
                </div>

                <Button className="service-plans-card__button" href="/">
                  Записаться на замер
                </Button>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}
