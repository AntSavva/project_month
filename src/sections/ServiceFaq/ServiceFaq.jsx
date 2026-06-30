const defaultItems = [
  {
    question: 'Сколько времени занимает изготовление?',
    answer:
      'Срок зависит от материала, объема и сложности профиля. После замера мы фиксируем этапы работ и называем понятный срок производства.',
  },
  {
    question: 'Можно ли подобрать цвет под интерьер?',
    answer:
      'Да, подберем оттенок по образцу, каталогу или фотографии. Перед запуском партии можно согласовать тестовое окрашивание.',
  },
  {
    question: 'Вы выезжаете на замер?',
    answer:
      'Да, специалист выезжает на объект, проверяет размеры проемов и учитывает особенности монтажа, чтобы детали подошли без доработок.',
  },
  {
    question: 'С какими материалами вы работаете?',
    answer:
      'Работаем с массивом, мебельным щитом и разными породами дерева. Подскажем вариант под бюджет, нагрузку и визуальную задачу.',
  },
  {
    question: 'Что согласовывается перед началом работ?',
    answer:
      'Перед началом работ согласуем размеры, материал, цвет и комплектацию. Все параметры фиксируются, чтобы результат совпал с ожиданиями.',
  },
  {
    question: 'Можно ли оформить доставку?',
    answer:
      'Готовые изделия можно забрать самостоятельно или оформить доставку. Условия зависят от адреса, объема заказа и выбранного способа монтажа.',
  },
]

export default (props) => {
  const items = (props.items?.length ? props.items : defaultItems).filter(
    ({ question, answer }) => question || answer
  )

  return (
    <section className="service-faq container" aria-labelledby="service-faq-title">
      <div className="service-faq__header">
        <h2 className="service-faq__title h2" id="service-faq-title">
          Частые вопросы
        </h2>
      </div>

      <div className="service-faq__list">
        {items.map(({ question, answer }) => (
          <details className="service-faq__item" key={question}>
            <summary className="service-faq__summary">
              <span className="service-faq__question">{question}</span>
              <span className="service-faq__icon" aria-hidden="true" />
            </summary>
            <div className="service-faq__content">
              <p className="service-faq__answer">{answer}</p>
            </div>
          </details>
        ))}
      </div>
    </section>
  )
}
