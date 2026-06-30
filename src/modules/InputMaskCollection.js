import getAttrNameFromSelector from '@/utils/getAttrNameFromSelector'
import IMask from 'imask'

const rootSelector = '[data-js-input-mask]'

class InputMask {
  selectors = {
    root: rootSelector,
  }

  constructor(rootElement) {
    this.rootElement = rootElement
    this.init()
  }

  init() {
    if (this.rootElement.hasAttribute('data-js-input-mask-bound')) {
      return
    }

    const mask = this.rootElement.getAttribute(
      getAttrNameFromSelector(this.selectors.root)
    )

    IMask(this.rootElement, { mask })
    this.rootElement.setAttribute('data-js-input-mask-bound', '')
  }
}

class InputMaskCollection {
  constructor() {
    this.init()
  }

  init() {
    document.querySelectorAll(rootSelector).forEach((element) => {
      new InputMask(element)
    })
  }
}

export default InputMaskCollection
