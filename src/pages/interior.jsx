import { readSiteData } from '@/lib/adminStorage'
import InteriorPageTemplate from '@/templates/InteriorPageTemplate'

export default function InteriorPage(props) {
  return <InteriorPageTemplate {...props} />
}

export const getServerSideProps = async () => {
  const siteData = await readSiteData()
  const page =
    siteData.pages.find(
      (item) => item.type === 'interior' && item.status === 'published' && item.slug === 'interior'
    ) ||
    siteData.pages.find((item) => item.type === 'interior' && item.status === 'published')

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
