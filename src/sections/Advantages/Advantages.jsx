import { check, roulette, rulerAndPen, shield, target, woods } from '@/assets/images/CardIcons'

const advantages = [
  {
    title: 'Консультация',
    description: 'Ответим на все вопросы до замера',
    icon: check,
  },
  {
    title: 'Оперативный замер',
    description: 'Приедем в удобное для вас время',
    icon: roulette,
  },
  {
    title: 'Срок от 3 дней',
    description: 'Быстро и без потери качества',
    icon: target,
  },
  {
    title: 'Элитные материалы',
    description: 'Используем проверенных поставщиков',
    icon: woods,
  },
  {
    title: 'Дизайн-проект',
    description: 'Подберём стиль и планировку под бюджет',
    icon: rulerAndPen,
  },
  {
    title: 'Гарантия 10 лет',
    description: 'Бесплатно устраним любые дефекты',
    icon: shield,
  },
]

export default () => {
  return (
    <section className="advantages container" aria-labelledby="advantages-title">
      <h2 className="advantages__title h2" id="advantages-title">
        Залог качества нашей работы
      </h2>

      <div className="advantages__grid">
        {advantages.map(({ title, description, icon }) => (
          <article className="advantages-card" key={title}>
            <div className="advantages-card__content">
              <h3 className="advantages-card__title h3">{title}</h3>
              <p className="advantages-card__description">{description}</p>
            </div>

            <img
              className="advantages-card__image"
              src={icon}
              alt=""
              width="320"
              height="320"
              loading="lazy"
            />
          </article>
        ))}
      </div>
    </section>
  )
}
