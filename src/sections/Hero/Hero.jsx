import { detail, person, star } from '@/assets/images/CardIcons'
import heroBg from '@/assets/images/Hero/hero-bg.png'

const advantages = [
  {
    title: '15 лет мастерства',
    description: 'Подпись',
    icon: star,
  },
  {
    title: 'Индивидуальный подход',
    description: 'Подпись',
    icon: person,
  },
  {
    title: 'От дизайна до установки',
    description: 'Подпись',
    icon: detail,
  },
]

export default () => {
  return (
    <section className="hero" aria-labelledby="hero-title">
      <img
        className="hero__bg-image"
        src={heroBg}
        alt=""
        width="1304"
        height="586"
        loading="eager"
      />
      <div className="hero__inner container">
        <div className="hero__content">
          <p className="hero__eyebrow h3">Столярные изделия</p>
          <h1 className="hero__title h1" id="hero-title">
            <span>Производство эксклюзивных</span>
            <span className="hero__title-accent">
              столярных изделий из массива
            </span>
          </h1>
        </div>

        <ul className="hero__advantages" aria-label="Преимущества">
          {advantages.map(({ title, description, icon }) => (
            <li className="hero__advantage" key={title}>
              <img
                className="hero__advantage-icon"
                src={icon}
                alt=""
                width="64"
                height="64"
                loading="eager"
              />
              <div className="hero__advantage-content">
                <h2 className="hero__advantage-title h4">{title}</h2>
                <p className="hero__advantage-description">{description}</p>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </section>
  )
}
