import Icon from '@/components/Icon'

const standards = [
  {
    title: 'Продукция мебельного производства. Термины и определения',
    code: 'ГОСТ 20400-80',
  },
  {
    title: 'Мебель. Общие технические условия',
    code: 'ГОСТ 16371-2014',
  },
  {
    title: 'Мебель для сидения и лежания. Общие технические условия',
    code: 'ГОСТ 19917-2014',
  },
  {
    title: 'Межкомнатные двери. Общие технические условия',
    code: 'ГОСТ 30211-94',
  },
  {
    title: 'Блоки дверные деревянные и комбинированные. Общие технические условия',
    code: 'ГОСТ 475-2016',
  },
  {
    title:
      'Детали профильные из древесины и древесных материалов для строительства. Технические условия',
    code: 'ГОСТ 8242-88',
  },
  {
    title: 'Тепловая защита зданий (актуальные требования к энергоэффективности)',
    code: 'СНиП 23-02-2003',
  },
  {
    title: 'Деревянные конструкции. Актуализированная редакция СНиП II-25-80',
    code: 'СП 64.13330.2017',
  },
]

export default ({ isOpen = true } = {}) => {
  return (
    <section className="standards container" aria-labelledby="standards-title">
      <details className="standards__details" open={isOpen ? true : undefined}>
        <summary className="standards__header">
          <h2 className="standards__title h2" id="standards-title">
            Основные нормативные документы
          </h2>

          <span className="standards__summary">
            <span>Подробнее</span>
            <span className="standards__summary-icon" aria-hidden="true" />
          </span>
        </summary>

        <div className="standards__list">
          {standards.map(({ title, code }) => (
            <article className="standards__item" key={code}>
              <h3 className="standards__item-title">{title}</h3>
              <p className="standards__item-code">{code}</p>
              <a
                className="standards__link"
                href="/"
                aria-label={`${code}: посмотреть`}
              >
                <span>Посмотреть</span>
                <span className="standards__link-icon" aria-hidden="true">
                  <Icon name="folder" />
                </span>
              </a>
            </article>
          ))}
        </div>
      </details>
    </section>
  )
}