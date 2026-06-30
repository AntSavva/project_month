import caseImage from '@/assets/images/Cases/case-preview.png'


const cases = [
  {
    title: 'Название',
    description: 'Описание',
  },
  {
    title: 'Название',
    description: 'Описание',
  },
  {
    title: 'Название',
    description: 'Описание',
  },
  {
    title: 'Название',
    description: 'Описание',
  },
]

export default () => {
  return (
    <section className="service-cases" aria-labelledby="service-cases-title">
      <div className="service-cases__inner container">
        <div className="service-cases__header">
          <h2 className="service-cases__title h2" id="service-cases-title">
            Уже реализованные проекты
          </h2>
        </div>

        <div className="service-cases__list">
          {cases.map(({ title, description }, index) => (
            <article className="service-cases-card" key={`${title}-${index}`}>
              <div className="service-cases-card__content">
                <div className="service-cases-card__text">
                  <h3 className="service-cases-card__title h3">{title}</h3>
                  <p className="service-cases-card__description">{description}</p>
                </div>
              </div>

              <img
                className="service-cases-card__image"
                src={caseImage}
                alt=""
                width="910"
                height="380"
                loading="lazy"
              />
            </article>
          ))}
        </div>
      </div>
    </section>
  )
}