
const steps = [
  {
    title: 'Профессиональная консультация',
    description:
      'Мы поможем выбрать материал и конструкцию, предложим проверенные решения и назовем примерную стоимость',
  },
  {
    title: 'Оперативный замер на объекте',
    description:
      'Точные замеры рабочих габаритов до 0.1 мм предотвращают проблемы с монтажом лестницы',
  },
  {
    title: 'Разработка 3D-модели',
    description:
      'Разработаем модель изделия с размерами, схемой сборки, доработкой, согласованием и точным расчетом стоимости',
  },
  {
    title: 'Изготовление на производстве',
    description:
      'Детали создаются на высокоточных итальянских ЧПУ-станках с раскроем до 0,01 мм и последующей шлифовкой и полировкой',
  },
  {
    title: 'Предварительная сборка на производстве',
    description:
      'На производстве собираем и подгоняем детали изделия, чтобы исключить задержки установки на объекте',
  },
  {
    title: 'Сборка на объекте за 6 часов',
    description:
      'Доставка изделия и установка на объекте за 6 часов без лишнего мусора и грязи, сборка на объекте изделий свыше 3 метров, гарантия на монтаж - 2 года',
  },
]

export default () => {
  return (
    <section className="service-process" aria-labelledby="service-process-title">
      <div className="service-process__inner container">
        <h2 className="service-process__title h2" id="service-process-title">
          Процесс работы
        </h2>

        <ol className="service-process__list">
          {steps.map(({ title, description }, index) => (
            <li className="service-process__item" key={title}>
              <span className="service-process__number" aria-hidden="true">
                {index + 1}
              </span>

              <div className="service-process__content">
                <h3 className="service-process__item-title h3">{title}</h3>
                <p className="service-process__description">{description}</p>
              </div>
            </li>
          ))}
        </ol>
      </div>
    </section>
  )
}