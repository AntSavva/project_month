import ashImage from '@/assets/images/ServiceMaterials/ash.png'
import beechImage from '@/assets/images/ServiceMaterials/beech.png'
import larchImage from '@/assets/images/ServiceMaterials/larch.png'
import oakImage from '@/assets/images/ServiceMaterials/oak.png'
import pineImage from '@/assets/images/ServiceMaterials/pine.png'
import walnutImage from '@/assets/images/ServiceMaterials/walnut.png'

const fallbackMaterials = [
  { title: 'Дуб', image: oakImage },
  { title: 'Ясень', image: ashImage },
  { title: 'Лиственница', image: larchImage },
  { title: 'Сосна', image: pineImage },
  { title: 'Орех', image: walnutImage },
  { title: 'Бук', image: beechImage },
]

const normalizeMaterial = (item, fallback) => {
  if (typeof item === 'string') {
    return {
      ...fallback,
      title: item || fallback.title,
    }
  }

  return {
    ...fallback,
    title: item?.title || fallback.title,
    image: item?.image || fallback.image,
  }
}

const getImageSrc = (image) => image?.src || image

export default (props) => {
  const { data } = props
  const title = data?.title || props.title || 'Материалы для наличников'
  const sourceMaterials = data?.items?.length ? data.items : fallbackMaterials
  const materials = sourceMaterials.map((material, index) =>
    normalizeMaterial(material, fallbackMaterials[index % fallbackMaterials.length])
  )

  return (
    <section className="service-materials container" aria-labelledby="service-materials-title">
      <h2 className="service-materials__title h2" id="service-materials-title">
        {title}
      </h2>

      <ul className="service-materials__list">
        {materials.map(({ title, image }, index) => (
          <li className="service-materials__item" key={`${title}-${index}`}>
            <article
              className="service-materials-card"
              style={{ '--material-image': `url(${getImageSrc(image)})` }}
            >
              <h3 className="service-materials-card__title h3">{title}</h3>
            </article>
          </li>
        ))}
      </ul>
    </section>
  )
}
