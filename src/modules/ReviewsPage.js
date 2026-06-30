const rootSelector = '[data-js-reviews-page]'

class ReviewsPage {
  selectors = {
    filterButton: '[data-js-reviews-filter]',
    filterSelect: '[data-js-reviews-filter-select]',
    filterCurrent: '[data-js-reviews-filter-current]',
    sortSelect: '[data-js-reviews-sort]',
    grid: '[data-js-reviews-grid]',
    card: '.reviews-card',
  }

  constructor(rootElement) {
    this.rootElement = rootElement
    this.filterButtonElements = [
      ...this.rootElement.querySelectorAll(this.selectors.filterButton),
    ]
    this.sortSelectElement = this.rootElement.querySelector(
      this.selectors.sortSelect
    )
    this.filterSelectElement = this.rootElement.querySelector(
      this.selectors.filterSelect
    )
    this.filterCurrentElement = this.rootElement.querySelector(
      this.selectors.filterCurrent
    )
    this.gridElement = this.rootElement.querySelector(this.selectors.grid)
    this.cardElements = [
      ...this.rootElement.querySelectorAll(this.selectors.card),
    ]
    this.activeFilter = 'all'

    if (!this.gridElement || !this.filterButtonElements.length) {
      return
    }

    this.bindEvents()
    this.update()
  }

  updateFilterButtons() {
    this.filterButtonElements.forEach((buttonElement) => {
      const isActive =
        buttonElement.dataset.jsReviewsFilter === this.activeFilter

      buttonElement.classList.toggle('is-active', isActive)
      buttonElement.setAttribute('aria-selected', isActive ? 'true' : 'false')
    })

    if (this.filterSelectElement) {
      this.filterSelectElement.value = this.activeFilter
    }

    if (this.filterCurrentElement && this.filterSelectElement) {
      this.filterCurrentElement.textContent =
        this.filterSelectElement.selectedOptions[0].textContent
    }
  }

  updateCards() {
    this.cardElements.forEach((cardElement) => {
      const isVisible =
        this.activeFilter === 'all' ||
        cardElement.dataset.category === this.activeFilter

      cardElement.classList.toggle('is-hidden', !isVisible)
    })
  }

  updateSort() {
    if (!this.sortSelectElement) {
      return
    }

    const sortedCards = [...this.cardElements]
    const order = this.sortSelectElement.value

    if (order !== 'default') {
      sortedCards.sort((firstCard, secondCard) => {
        const firstOrder = Number(firstCard.dataset.order)
        const secondOrder = Number(secondCard.dataset.order)

        return order === 'new'
          ? secondOrder - firstOrder
          : firstOrder - secondOrder
      })
    }

    sortedCards.forEach((cardElement) => {
      this.gridElement.append(cardElement)
    })
  }

  update = () => {
    this.updateFilterButtons()
    this.updateSort()
    this.updateCards()
  }

  onFilterButtonClick = (event) => {
    this.activeFilter = event.currentTarget.dataset.jsReviewsFilter
    this.update()
  }

  onFilterSelectChange = (event) => {
    this.activeFilter = event.currentTarget.value
    this.update()
  }

  bindEvents() {
    this.filterButtonElements.forEach((buttonElement) => {
      buttonElement.addEventListener('click', this.onFilterButtonClick)
    })

    this.filterSelectElement?.addEventListener(
      'change',
      this.onFilterSelectChange
    )
    this.sortSelectElement?.addEventListener('change', this.update)
  }
}

class ReviewsPageCollection {
  constructor() {
    this.init()
  }

  init() {
    document.querySelectorAll(rootSelector).forEach((element) => {
      new ReviewsPage(element)
    })
  }
}

export default ReviewsPageCollection
