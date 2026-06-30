import { readSiteData } from '@/lib/adminStorage'
import ProductPageTemplate from '@/templates/ProductPageTemplate'

export const metadata = {
  title: 'Услуга',
}

export default (props) => {
  return <ProductPageTemplate {...props} />
}

export const getServerSideProps = async () => {
  const siteData = await readSiteData()
  const page = siteData.pages.find((item) => item.type === 'product' && item.slug === 'service')

  return {
    props: {
      page: page || null,
      content: page?.content || siteData.productPage,
      reviews: siteData.reviews || [],
    },
  }
}
