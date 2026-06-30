class LeadForms {
  selectors = {
    form: 'form',
  }

  constructor() {
    this.formElements = [...document.querySelectorAll(this.selectors.form)].filter(
      (formElement) =>
        !formElement.closest('.admin-page') &&
        formElement.method.toLowerCase() === 'post' &&
        !formElement.hasAttribute('data-js-lead-form-bound')
    )

    this.bindEvents()
  }

  getPayload(formElement) {
    const formData = new FormData(formElement)
    const payload = Object.fromEntries(formData.entries())
    const phoneInput = formElement.querySelector('[name="phone"]')
    const phone = phoneInput?.value || payload.phone || ''

    return {
      ...payload,
      phone,
      source: window.location.pathname,
      form: formElement.className || 'site-form',
    }
  }

  onSubmit = async (event) => {
    event.preventDefault()

    const formElement = event.currentTarget

    if (formElement.hasAttribute('data-js-lead-form-submitting')) {
      return
    }

    const submitButton = formElement.querySelector('[type="submit"]')
    const previousButtonText =
      submitButton?.dataset.previousText || submitButton?.textContent || ''

    formElement.setAttribute('data-js-lead-form-submitting', '')

    if (submitButton) {
      submitButton.dataset.previousText = previousButtonText
      submitButton.disabled = true
      submitButton.textContent = 'Отправляем...'
    }

    try {
      const response = await fetch('/api/leads', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(this.getPayload(formElement)),
      })

      if (!response.ok) {
        throw new Error('Не удалось отправить заявку')
      }

      formElement.reset()

      if (submitButton) {
        submitButton.textContent = 'Заявка отправлена'
      }
    } catch (error) {
      if (submitButton) {
        submitButton.textContent = 'Ошибка отправки'
      }
    } finally {
      window.setTimeout(() => {
        formElement.removeAttribute('data-js-lead-form-submitting')

        if (submitButton) {
          submitButton.disabled = false
          submitButton.textContent = previousButtonText
          delete submitButton.dataset.previousText
        }
      }, 1800)
    }
  }

  bindEvents() {
    this.formElements.forEach((formElement) => {
      formElement.setAttribute('data-js-lead-form-bound', '')
      formElement.addEventListener('submit', this.onSubmit)
    })
  }
}

export default LeadForms
