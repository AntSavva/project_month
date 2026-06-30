import Icon from '@/components/Icon'

const fallbackSolutions = [
  {
    title: 'Библиотеки, кабинеты, офисы',
    slug: 'interior',
    description: 'Создаем акустический комфорт и порядок',
  },
  {
    title: 'Рестораны, бары, кафе',
    slug: 'restorany-bary-kafe',
    description: 'Износостойкая отделка под требования СЭС',
  },
  {
    title: 'Бани и банные комплексы',
    slug: 'bani-i-bannye-kompleksy',
    description: 'Правильная гидроизоляция и термодревесина',
  },
]

const getInteriorHref = (slug) => {
  if (!slug) {
    return '/'
  }

  return slug.startsWith('/') ? slug : `/${slug}`
}

export default ({ interiors = [] }) => {
  const items = interiors.length ? interiors : fallbackSolutions

  return (
    <section className="interior-solutions container" aria-labelledby="interior-solutions-title">
      <h2 className="interior-solutions__title h2" id="interior-solutions-title">
        Решения для отделки интерьеров
      </h2>

      <div className="interior-solutions__grid">
        {items.map(({ title, description, image, slug }, index) => (
          <a
            className="interior-solutions-card"
            href={getInteriorHref(slug)}
            key={`${slug || title}-${index}`}
          >
            {image ? (
              <span className="interior-solutions-card__media">
                <img
                  className="interior-solutions-card__image"
                  src={image}
                  alt=""
                  width="540"
                  height="304"
                  loading="lazy"
                />
              </span>
            ) : (
              <span
                className="interior-solutions-card__media"
                aria-label={`${title}: место для фотографии`}
                role="img"
              />
            )}

            <span className="interior-solutions-card__content">
              <span className="interior-solutions-card__title h3">{title}</span>
              {description && (
                <span className="interior-solutions-card__description">{description}</span>
              )}
            </span>

            <span className="interior-solutions-card__link" aria-hidden="true">
              <span>Подробнее</span>
              <Icon className="interior-solutions-card__link-icon" name="arrow-top-right" />
            </span>
          </a>
        ))}
      </div>
    </section>
  )
}
