import { useRef } from 'react'

import Icon from '@/components/Icon'
import primaryImage from '@/assets/images/Products/product-primary.png'
import secondaryImage from '@/assets/images/Products/product-secondary.png'

const fallbackProducts = [
  {
    title: 'Наличники',
    slug: 'service',
    description: 'Изготовим по вашим размерам из массива',
    image: primaryImage,
  },
  {
    title: 'Лестницы',
    slug: 'ladder',
    description: 'Продуманные маршевые и винтовые конструкции из массива',
    image: secondaryImage,
  },
]

const fallbackImages = [primaryImage, secondaryImage]

const getProductHref = (slug) => {
  if (!slug) {
    return '/'
  }

  return slug.startsWith('/') ? slug : `/${slug}`
}

export default ({ products = [] }) => {
  const sliderRef = useRef(null)
  const items = products.length ? products : fallbackProducts

  const onSlide = (direction) => {
    const slider = sliderRef.current

    if (!slider) {
      return
    }

    const firstCard = slider.querySelector('.products-card')
    const step = firstCard ? firstCard.getBoundingClientRect().width + 12 : slider.clientWidth

    slider.scrollBy({
      left: direction * step,
      behavior: 'smooth',
    })
  }

  return (
    <section className="products container" aria-labelledby="products-title">
      <h2 className="products__title h2" id="products-title">
        Что мы производим
      </h2>

      <div className="products__viewport" ref={sliderRef}>
        <div className="products__grid">
          {items.map(({ title, description, image, slug }, index) => {
            const cardImage = image || fallbackImages[index % fallbackImages.length]
            const href = getProductHref(slug)

            return (
              <a className="products-card" href={href} key={`${slug || title}-${index}`}>
                <span className="products-card__picture">
                  <img
                    className="products-card__image"
                    src={cardImage}
                    alt=""
                    width="450"
                    height="450"
                    loading="lazy"
                  />
                </span>

                <span className="products-card__content">
                  <span className="products-card__title h3">{title}</span>
                  {description && (
                    <span className="products-card__description">{description}</span>
                  )}
                </span>

                <span className="products-card__link" aria-hidden="true">
                  <span>Подробнее</span>
                  <Icon className="products-card__link-icon" name="arrow-top-right" />
                </span>
              </a>
            )
          })}
        </div>
      </div>

      {items.length > 1 && (
        <div className="products__controls" aria-label="Листать продукцию">
          <button
            className="products__control"
            type="button"
            aria-label="Предыдущая продукция"
            onClick={() => onSlide(-1)}
          >
            <Icon className="products__control-icon" name="arrow-left" />
          </button>
          <button
            className="products__control"
            type="button"
            aria-label="Следующая продукция"
            onClick={() => onSlide(1)}
          >
            <Icon className="products__control-icon" name="arrow-right" />
          </button>
        </div>
      )}
    </section>
  )
}
