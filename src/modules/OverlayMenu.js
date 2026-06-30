class OverlayMenu {
  selectors = {
    root: '[data-js-overlay-menu]',
    dialog: '[data-js-overlay-menu-dialog]',
    burgerButton: '[data-js-overlay-menu-burger-button]',
    closeButton: '[data-js-overlay-menu-close]',
    menuLink: 'a',
  }

  stateClasses = {
    isActive: 'is-active',
    isLock: 'is-lock',
  }

  constructor() {
    this.rootElement = document.querySelector(this.selectors.root)
    if (!this.rootElement) {
      return
    }

    if (this.rootElement.hasAttribute('data-js-overlay-menu-bound')) {
      return
    }

    this.dialogElement = this.rootElement.querySelector(this.selectors.dialog)
    this.burgerButtonElement = this.rootElement.querySelector(
      this.selectors.burgerButton
    )
    if (!this.dialogElement || !this.burgerButtonElement) {
      return
    }

    this.rootElement.setAttribute('data-js-overlay-menu-bound', '')
    this.bindEvents()
  }

  onBurgerButtonClick = (event) => {
    event.preventDefault()

    if (this.dialogElement.open) {
      this.close()
      return
    }

    this.burgerButtonElement.classList.add(this.stateClasses.isActive)

    if (typeof this.dialogElement.showModal === 'function') {
      this.dialogElement.showModal()
    } else {
      this.dialogElement.open = true
    }

    document.documentElement.classList.add(this.stateClasses.isLock)
  }

  close = () => {
    this.burgerButtonElement.classList.remove(this.stateClasses.isActive)

    if (this.dialogElement.open && typeof this.dialogElement.close === 'function') {
      this.dialogElement.close()
    } else {
      this.dialogElement.open = false
    }

    document.documentElement.classList.remove(this.stateClasses.isLock)
  }

  onDialogClick = (event) => {
    if (
      event.target === this.dialogElement ||
      event.target.closest(this.selectors.closeButton) ||
      event.target.closest(this.selectors.menuLink)
    ) {
      this.close()
    }
  }

  onDialogClose = () => {
    this.burgerButtonElement.classList.remove(this.stateClasses.isActive)
    document.documentElement.classList.remove(this.stateClasses.isLock)
  }

  bindEvents() {
    this.burgerButtonElement.addEventListener('click', this.onBurgerButtonClick)
    this.dialogElement.addEventListener('click', this.onDialogClick)
    this.dialogElement.addEventListener('close', this.onDialogClose)
  }
}

export default OverlayMenu
