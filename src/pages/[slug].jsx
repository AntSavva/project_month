import { readSiteData } from '@/lib/adminStorage'
import DocumentPageTemplate from '@/templates/DocumentPageTemplate'
import InteriorPageTemplate from '@/templates/InteriorPageTemplate'
import ProductPageTemplate from '@/templates/ProductPageTemplate'

export default function DynamicPage(props) {
  if (props.page?.type === 'document') {
    return <DocumentPageTemplate {...props} />
  }

  if (props.page?.type === 'interior') {
    return <InteriorPageTemplate {...props} />
  }

  return <ProductPageTemplate {...props} />
}

export const getServerSideProps = async (context) => {
  const { slug } = context.params
  const siteData = await readSiteData()
  const page = siteData.pages.find(
      (item) =>
      (item.type === 'product' || item.type === 'interior' || item.type === 'document') &&
      item.status === 'published' &&
      item.slug === slug
  )

  if (!page) {
    return {
      notFound: true,
    }
  }

  return {
    props: {
      page,
      content: page.content,
      reviews: siteData.reviews || [],
    },
  }
}
