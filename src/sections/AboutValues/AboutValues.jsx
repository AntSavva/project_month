import { medal, person, shield, star } from '@/assets/images/CardIcons'

const valueIcons = [shield, star, medal, person]

const values = [
  {
    title: 'Долговечность',
    description: 'Изделия служат не один десяток лет',
  },
  {
    title: 'Эстетика',
    description: 'Создание красивых, стильных изделий, которые радуют глаз',
  },
  {
    title: 'Честный подход',
    description: 'Отказ от компромиссов в качестве, вкладываем душу и сердце',
  },
  {
    title: 'Клиентоориентированность',
    description: 'Создание ценности для клиента, которая дорого стоит',
  },
]

export default () => {
  return (
    <section className="about-values" aria-label="Ценности компании">
      <div className="about-values__inner container">
        <div className="about-values__grid">
          {values.map(({ title, description }, index) => (
            <article className="about-values-card" key={title}>
              <img
                className="about-values-card__icon"
                src={valueIcons[index]}
                alt=""
                width="64"
                height="64"
                loading="lazy"
              />

              <div className="about-values-card__content">
                <h3 className="about-values-card__title">{title}</h3>
                <p className="about-values-card__description">{description}</p>
              </div>
            </article>
          ))}
        </div>
      </div>
    </section>
  )
}
