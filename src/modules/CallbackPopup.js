const rootSelector = '[data-js-callback-popup]'

class CallbackPopup {
  selectors = {
    trigger: '[data-js-callback-popup-open]',
    closeButton: '[data-js-callback-popup-close]',
    overlayBurgerButton: '[data-js-overlay-menu-burger-button]',
  }

  stateClasses = {
    isActive: 'is-active',
    isLock: 'is-lock',
  }

  constructor() {
    this.dialogElement = document.querySelector(rootSelector)

    if (!this.dialogElement) {
      return
    }

    this.closeButtonElement = this.dialogElement.querySelector(
      this.selectors.closeButton
    )
    this.triggerElements = [...document.querySelectorAll(this.selectors.trigger)]

    this.bindEvents()
  }

  open = (event) => {
    event.preventDefault()

    document.querySelectorAll('dialog[open]').forEach((dialogElement) => {
      if (dialogElement !== this.dialogElement) {
        dialogElement.close()
      }
    })

    document
      .querySelector(this.selectors.overlayBurgerButton)
      ?.classList.remove(this.stateClasses.isActive)

    this.dialogElement.showModal()
    document.documentElement.classList.add(this.stateClasses.isLock)
  }

  close = () => {
    this.dialogElement.close()
  }

  onDialogClose = () => {
    document.documentElement.classList.remove(this.stateClasses.isLock)
  }

  onDialogClick = (event) => {
    if (event.target === this.dialogElement) {
      this.close()
    }
  }

  bindEvents() {
    this.triggerElements.forEach((triggerElement) => {
      triggerElement.addEventListener('click', this.open)
    })
    this.closeButtonElement?.addEventListener('click', this.close)
    this.dialogElement.addEventListener('close', this.onDialogClose)
    this.dialogElement.addEventListener('click', this.onDialogClick)
  }
}

export default CallbackPopup
