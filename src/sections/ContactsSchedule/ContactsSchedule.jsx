import clockIcon from '@/assets/images/ContactsSchedule/clock.svg'

export default () => {
  return (
    <section
      className="contacts-schedule"
      aria-labelledby="contacts-schedule-title"
    >
      <div className="contacts-schedule__inner container">
        <h2 className="contacts-schedule__title h2" id="contacts-schedule-title">
          Режим работы
        </h2>

        <div className="contacts-schedule__grid">
          <article className="contacts-schedule__card contacts-schedule__card--worktime">
            <span className="contacts-schedule__icon" aria-hidden="true">
              <img
                className="contacts-schedule__icon-image"
                src={clockIcon}
                alt=""
                width="28"
                height="28"
                loading="lazy"
              />
            </span>

            <p className="contacts-schedule__worktime">
              Понедельник-пятница: с 9:00 до 19:00
              <br />
              Суббота-воскресенье: выходные дни
            </p>
          </article>

          <article className="contacts-schedule__card contacts-schedule__card--notice">
            <p className="contacts-schedule__notice">
              <strong>Внимание!</strong> В нерабочее время мы принимаем заказы
              только через наш сайт, по электронной почте или в Telegram. Если
              вы напишите нам в выходные - мы ответим в течение ближайшего
              рабочего часа.
            </p>
          </article>
        </div>
      </div>
    </section>
  )
}