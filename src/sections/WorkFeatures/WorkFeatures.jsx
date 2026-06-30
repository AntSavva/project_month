import clsx from 'clsx'
import { detail, rubles, shield, star } from '@/assets/images/CardIcons'

const featureIcons = [star, rubles, detail, shield]

const features = [
  {
    title: 'Древесина эсктра-класса и 1 сорта',
    description:
      'На наших изделиях вы не найдете сколы, трещины, сучки и другие дефекты',
  },
  {
    title: 'Фиксированная стоимость',
    description: 'Описание',
    isLastOnMobile: true,
  },
  {
    title: 'Условия в договоре',
    description:
      'Срок изготовления, гарантия на монтаж и конструкцию, критерии проверки качества',
  },
  {
    title: 'Большой запас прочности',
    description: 'Учитываем множество факторов, обязательно соблюдаем ГОСТы и СНиПы',
  },
]

export default () => {
  return (
    <section className="work-features container" aria-labelledby="work-features-title">
      <h2 className="work-features__title h2" id="work-features-title">
        Особенности нашей работы
      </h2>

      <div className="work-features__grid">
        {features.map(({ title, description, isLastOnMobile }, index) => (
          <article
            className={clsx(
              'work-features-card',
              isLastOnMobile && 'work-features-card--last-mobile'
            )}
            key={title}
          >
            <div className="work-features-card__content">
              <h3 className="work-features-card__title h3">{title}</h3>
              <p className="work-features-card__description">{description}</p>
            </div>

            <img
              className="work-features-card__decor"
              src={featureIcons[index]}
              alt=""
              width="189"
              height="189"
              loading="lazy"
            />
          </article>
        ))}
      </div>
    </section>
  )
}
