import { cardIcons, color, lines, target, shield, tree, woods } from '@/assets/images/CardIcons'

const colorIcons = [tree, target, lines, color, shield, woods]

const fallbackItems = [
  {
    title: 'Натуральные компоненты',
    description:
      'Изготовлено из натуральных восков, масел и смол, что обеспечивает высокое качество и безопасность',
  },
  {
    title: 'Специальное назначение',
    description:
      'Разработано специально для древесины, учитывает ее особенности и обеспечивает оптимальное покрытие',
  },
  {
    title: 'Универсальность применения',
    description:
      'Подходит для использования как на открытом воздухе, так и в помещениях, не имеет запаха',
  },
  {
    title: 'Широкий выбор цветов',
    description:
      '25 видов цветов и оттенков позволяет подобрать оптимальный вариант для любого дизайна',
  },
  {
    title: 'Долговечность',
    description:
      'Не отслаивается и не шелушится, впитывается в древесину, сокращая набухание и усыхание',
  },
  {
    title: 'Сохранение структуры древесины',
    description:
      'Не образует дополнительный слой, сохраняя естественную структуру, легко поддается реставрации',
  },
]

export default (props) => {
  const { data } = props
  const title = data?.title || 'Цветовые решения'
  const description =
    data?.description ||
    'Окрашиваем изделия в любые цвета по шкале RAL для идеального соответствия окружению или вашему вкусу'
  const items = data?.items?.length ? data.items : fallbackItems

  return (
    <section className="service-colors container" aria-labelledby="service-colors-title">
      <div className="service-colors__header">
        <h2 className="service-colors__title h2" id="service-colors-title">
          {title}
        </h2>
        <p className="service-colors__description">{description}</p>
      </div>

      <div className="service-colors__grid">
        {items.map(({ title, description, icon }, index) => (
          <article className="service-colors-card" key={title}>
            <div className="service-colors-card__content">
              <h3 className="service-colors-card__title h3">{title}</h3>
              <p className="service-colors-card__description">{description}</p>
            </div>

            <img
              className="service-colors-card__decor"
              src={cardIcons[icon] || colorIcons[index] || colorIcons[0]}
              alt=""
              width="189"
              height="189"
              loading="lazy"
            />
          </article>
        ))}
      </div>
    </section>
  )
}
