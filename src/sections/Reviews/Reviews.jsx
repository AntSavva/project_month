import Button from '@/components/Button'
import Icon from '@/components/Icon'

const getVisibleReviews = (reviews = [], maxItems = 10) =>
  reviews.filter((review) => review.status !== 'draft').slice(0, maxItems)

export default ({ reviews = [], maxItems = 10, popupPrefix = 'review-popup' }) => {
  const visibleReviews = getVisibleReviews(reviews, maxItems)

  if (!visibleReviews.length) {
    return null
  }

  return (
    <section className="reviews container" aria-labelledby="reviews-title">
      <div className="reviews__header">
        <h2 className="reviews__title h2" id="reviews-title">
          Отзывы клиентов
        </h2>
      </div>

      <div className="reviews__slider" data-js-reviews-slider="">
        <div className="reviews__viewport">
          <div className="reviews__track" data-js-reviews-slider-track="">
            {visibleReviews.map((review, index) => (
              <article className="reviews-card" key={review.id || `${review.author}-${index}`}>
                <p className="reviews-card__text">{review.text}</p>

                <a className="reviews-card__more" href={`#${popupPrefix}-${index + 1}`}>
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
            ))}
          </div>
        </div>

        <div className="reviews__controls" aria-label="Навигация по отзывам">
          <button
            className="reviews__arrow reviews__arrow--prev"
            type="button"
            aria-label="Предыдущие отзывы"
            data-js-reviews-slider-prev=""
          >
            <Icon className="reviews__arrow-icon" name="arrow-left" />
          </button>
          <button
            className="reviews__arrow reviews__arrow--next"
            type="button"
            aria-label="Следующие отзывы"
            data-js-reviews-slider-next=""
          >
            <Icon className="reviews__arrow-icon" name="arrow-right" />
          </button>
        </div>
      </div>

      <Button className="reviews__button" href="/reviews">
        Посмотреть все отзывы
      </Button>

      {visibleReviews.map((review, index) => (
        <div
          className="reviews-popup"
          id={`${popupPrefix}-${index + 1}`}
          key={`popup-${review.id || `${review.author}-${index}`}`}
          role="dialog"
          aria-modal="true"
          aria-labelledby={`${popupPrefix}-title-${index + 1}`}
        >
          <a className="reviews-popup__backdrop" href="#reviews-title" aria-label="Закрыть отзыв" />

          <article className="reviews-popup__body">
            <a className="reviews-popup__close" href="#reviews-title" aria-label="Закрыть">
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
                <h3 className="reviews-popup__title h3" id={`${popupPrefix}-title-${index + 1}`}>
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
