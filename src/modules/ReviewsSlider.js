const rootSelector = '[data-js-reviews-slider]'

class ReviewsSlider {
  selectors = {
    root: rootSelector,
    track: '[data-js-reviews-slider-track]',
    prevButton: '[data-js-reviews-slider-prev]',
    nextButton: '[data-js-reviews-slider-next]',
  }

  constructor(rootElement) {
    this.rootElement = rootElement
    this.trackElement = this.rootElement.querySelector(this.selectors.track)
    this.prevButtonElement = this.rootElement.querySelector(
      this.selectors.prevButton
    )
    this.nextButtonElement = this.rootElement.querySelector(
      this.selectors.nextButton
    )
    this.currentPage = 0

    if (
      !this.trackElement ||
      !this.prevButtonElement ||
      !this.nextButtonElement
    ) {
      return
    }

    this.bindEvents()
    this.update()
  }

  get gap() {
    const styles = getComputedStyle(this.trackElement)

    return parseFloat(styles.columnGap) || 0
  }

  get perView() {
    const styles = getComputedStyle(this.rootElement)
    const value = parseInt(styles.getPropertyValue('--reviews-per-view'), 10)

    return Number.isNaN(value) ? 1 : value
  }

  get pagesCount() {
    return Math.ceil(this.trackElement.children.length / this.perView)
  }

  get maxPage() {
    return Math.max(this.pagesCount - 1, 0)
  }

  update = () => {
    this.currentPage = Math.min(this.currentPage, this.maxPage)

    const offset =
      this.currentPage * (this.rootElement.clientWidth + this.gap)

    this.trackElement.style.transform = `translateX(${-offset}px)`
    this.prevButtonElement.disabled = this.currentPage === 0
    this.nextButtonElement.disabled = this.currentPage === this.maxPage
  }

  onPrevButtonClick = () => {
    this.currentPage = Math.max(this.currentPage - 1, 0)
    this.update()
  }

  onNextButtonClick = () => {
    this.currentPage = Math.min(this.currentPage + 1, this.maxPage)
    this.update()
  }

  bindEvents() {
    this.prevButtonElement.addEventListener('click', this.onPrevButtonClick)
    this.nextButtonElement.addEventListener('click', this.onNextButtonClick)
    window.addEventListener('resize', this.update)
  }
}

class ReviewsSliderCollection {
  constructor() {
    this.init()
  }

  init() {
    document.querySelectorAll(rootSelector).forEach((element) => {
      new ReviewsSlider(element)
    })
  }
}

export default ReviewsSliderCollection
