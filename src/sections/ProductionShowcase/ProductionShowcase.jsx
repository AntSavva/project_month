import productionPhoto from '@/assets/images/AboutProduction/production-photo.png'
import {
  loop,
  machine,
  medal,
  personWithStar,
  woods,
} from '@/assets/images/CardIcons'

const featureIcons = [woods, medal, personWithStar, machine, loop]

const features = [
  {
    title: '1500+ объектов',
    description: 'Описание',
  },
  {
    title: 'Работаем с 2009 года',
    description: 'Описание',
  },
  {
    title: 'Индивидуальный подход',
    description: 'Описание',
  },
  {
    title: 'Профессиональное оборудование',
    description: 'Описание',
  },
  {
    title: 'Строгий входной контроль качества материалов',
    description: 'Описание',
  },
]

export default () => {
  return (
    <section
      className="production-showcase"
      aria-labelledby="production-showcase-title"
    >
      <div className="production-showcase__inner container">
        <div className="production-showcase__header">
          <h2
            className="production-showcase__title h2"
            id="production-showcase-title"
          >
            Создаем красивые, стильные и долговечные изделия
          </h2>

          <p className="production-showcase__description">
            Собственное производство изделий в Санкт-Петербурге с минимальными
            сроками изготовления от 3 дней
          </p>
        </div>

        <div className="production-showcase__body">
          <div className="production-showcase__features">
            {features.map(({ title, description }, index) => (
              <article className="production-showcase-feature" key={title}>
                <img
                  className="production-showcase-feature__icon"
                  src={featureIcons[index]}
                  alt=""
                  width="64"
                  height="64"
                  loading="lazy"
                />

                <div className="production-showcase-feature__content">
                  <h3 className="production-showcase-feature__title">
                    {title}
                  </h3>
                  <p className="production-showcase-feature__description">
                    {description}
                  </p>
                </div>
              </article>
            ))}
          </div>

          <img
            className="production-showcase__image"
            src={productionPhoto}
            alt=""
            width="910"
            height="662"
            loading="lazy"
          />
        </div>
      </div>
    </section>
  )
}
