import Icon from '@/components/Icon'

const filters = [
  { label: 'Все', value: 'all' },
  { label: 'Наличники', value: 'trim' },
  { label: 'Обсады', value: 'frames' },
  { label: 'Подоконники', value: 'sills' },
  { label: 'Лестницы', value: 'stairs' },
  { label: 'Малые архитектурные формы', value: 'small-forms' },
]

const getVisibleReviews = (reviews = []) => reviews.filter((review) => review.status !== 'draft')

export default ({ reviews = [] }) => {
  const visibleReviews = getVisibleReviews(reviews)

  return (
    <section
      className="reviews-hero"
      aria-labelledby="reviews-hero-title"
      data-js-reviews-page=""
    >
      <div className="reviews-hero__inner container">
        <div className="reviews-hero__content">
          <p className="reviews-hero__subtitle">Отзывы</p>
          <h1 className="reviews-hero__title h1" id="reviews-hero-title">
            Мнение наших клиентов
          </h1>
        </div>

        <div className="reviews-hero__toolbar">
          <label className="reviews-hero__type">
            <span>
              Тип: <span data-js-reviews-filter-current="">Все</span>
            </span>
            <select
              className="reviews-hero__type-select"
              name="reviews-type"
              aria-label="Тип отзыва"
              data-js-reviews-filter-select=""
            >
              {filters.map(({ label, value }) => (
                <option value={value} key={value}>
                  {label}
                </option>
              ))}
            </select>
            <span className="reviews-hero__select-icon" aria-hidden="true" />
          </label>

          <div
            className="reviews-hero__filters"
            aria-label="Категории отзывов"
            role="tablist"
          >
            {filters.map(({ label, value }, index) => (
              <button
                className={`reviews-hero__filter${index === 0 ? ' is-active' : ''}`}
                type="button"
                role="tab"
                aria-selected={index === 0 ? 'true' : 'false'}
                data-js-reviews-filter={value}
                key={value}
              >
                {label}
              </button>
            ))}
          </div>

          <label className="reviews-hero__sort">
            <span>Порядок:</span>
            <select
              className="reviews-hero__sort-select"
              name="reviews-order"
              data-js-reviews-sort=""
            >
              <option value="default">По умолчанию</option>
              <option value="new">Сначала новые</option>
              <option value="old">Сначала старые</option>
            </select>
            <span className="reviews-hero__select-icon" aria-hidden="true" />
          </label>
        </div>
      </div>

      <div className="reviews-hero__grid container" data-js-reviews-grid="">
        {visibleReviews.length === 0 ? (
          <p className="reviews-hero__empty">Отзывы пока не добавлены.</p>
        ) : (
          visibleReviews.map((review, index) => (
            <article
              className="reviews-card"
              data-category={review.category || 'all'}
              data-order={review.order || index}
              key={review.id || `${review.author}-${index}`}
            >
              <p className="reviews-card__text">{review.text}</p>

              <a className="reviews-card__more" href={`#review-page-popup-${index + 1}`}>
                <span>Читать полностью</span>
                <Icon className="reviews-card__more-icon" name="arrow-top-right" />
              </a>

              <footer className="reviews-card__author">
                {review.avatar && (
                  <img
                    className="reviews-card__avatar"
                    src={review.avatar}
                    alt=""
                    loading="lazy"
                  />
                )}
                <span className="reviews-card__meta">
                  <span className="reviews-card__name">{review.author}</span>
                  <span className="reviews-card__date">{review.date}</span>
                </span>
              </footer>
            </article>
          ))
        )}
      </div>

      {visibleReviews.map((review, index) => (
        <div
          className="reviews-popup"
          id={`review-page-popup-${index + 1}`}
          key={`popup-${review.id || `${review.author}-${index}`}`}
          role="dialog"
          aria-modal="true"
          aria-labelledby={`review-page-popup-title-${index + 1}`}
        >
          <a className="reviews-popup__backdrop" href="#reviews-hero-title" aria-label="Закрыть отзыв" />

          <article className="reviews-popup__body">
            <a className="reviews-popup__close" href="#reviews-hero-title" aria-label="Закрыть">
              x
            </a>

            <div className="reviews-popup__author">
              {review.avatar && (
                <img
                  className="reviews-popup__avatar"
                  src={review.avatar}
                  alt=""
                  loading="lazy"
                />
              )}
              <div className="reviews-popup__meta">
                <h3 className="reviews-popup__title h3" id={`review-page-popup-title-${index + 1}`}>
                  {review.author}
                </h3>
                <p className="reviews-popup__date">{review.date}</p>
              </div>
            </div>

            <p className="reviews-popup__text">{review.text}</p>
          </article>
        </div>
      ))}
    </section>
  )
}
