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
    <section className="cases container" aria-labelledby="cases-title">
      <div className="cases__header">
        <h2 className="cases__title h2" id="cases-title">
          Уже реализованные проекты
        </h2>
      </div>

      <div className="cases__list">
        {cases.map(({ title, description }, index) => (
          <article className="cases-card" key={`${title}-${index}`}>
            <div className="cases-card__content">
              <div className="cases-card__text">
                <h3 className="cases-card__title h3">{title}</h3>
                <p className="cases-card__description">{description}</p>
              </div>
            </div>

            <img
              className="cases-card__image"
              src={caseImage}
              alt=""
              width="910"
              height="380"
              loading="lazy"
            />
          </article>
        ))}
      </div>
    </section>
  )
}
