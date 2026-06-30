import Button from '@/components/Button'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'

const productOptions = [
  'Наличники',
  'Лестницы',
  'Порталы и арки',
  'Обсады и подоконники',
  'Декор',
]

const contactOptions = ['Телефон', 'Telegram', 'WhatsApp', 'MAX', 'Email']

export default () => {
  const settings = useSiteSettings()

  return (
    <dialog className="callback-popup" id="callback" data-js-callback-popup="">
      <div className="callback-popup__body">
        <button
          className="callback-popup__close"
          type="button"
          aria-label="Закрыть"
          data-js-callback-popup-close=""
        />

        <div className="callback-popup__header">
          <h2 className="callback-popup__title h2">Запись на замер</h2>
          <p className="callback-popup__description">
            В течении часа с вами свяжется наш менеджер
          </p>
        </div>

        <form className="callback-popup__form" action="/" method="post">
          <label className="callback-popup__select-wrapper">
            <span className="visually-hidden">Продукт</span>
            <select className="callback-popup__control" name="product">
              <option value="">Продукт</option>
              {productOptions.map((option) => (
                <option value={option} key={option}>
                  {option}
                </option>
              ))}
            </select>
          </label>

          <input
            className="callback-popup__control"
            name="name"
            placeholder="Григорий"
            aria-label="Ваше имя"
          />

          <label className="callback-popup__select-wrapper">
            <span className="visually-hidden">Удобный способ связи</span>
            <select className="callback-popup__control" name="contactMethod">
              <option value="">Удобный способ связи</option>
              {contactOptions.map((option) => (
                <option value={option} key={option}>
                  {option}
                </option>
              ))}
            </select>
          </label>

          <input
            className="callback-popup__control"
            name="phone"
            placeholder={settings.phone}
            aria-label="Телефон"
            inputMode="tel"
            data-js-input-mask="+{7} (000) 000-00-00"
          />

          <Button className="callback-popup__button" type="submit">
            Записаться на замер
          </Button>

          <p className="callback-popup__privacy">
            Нажимая на кнопку, вы соглашаетесь с политикой конфиденциальности.
          </p>
        </form>
      </div>
    </dialog>
  )
}
