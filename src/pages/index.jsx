import Hero from '@/sections/Hero'
import Advantages from '@/sections/Advantages'
import Cases from '@/sections/Cases'
import Products from '@/sections/Products'
import HomeRequest from '@/sections/HomeRequest'
import InteriorSolutions from '@/sections/InteriorSolutions'
import Questions from '@/sections/Questions'
import Reviews from '@/sections/Reviews'
import Route from '@/sections/Route'
import WorkFeatures from '@/sections/WorkFeatures'
import ProductionShowcase from '@/sections/ProductionShowcase'
import { readSiteData } from '@/lib/adminStorage'
import { getInteriorCover } from '@/lib/interiorCovers'

export const metadata = {
  title: 'Главная',
}

export default ({ reviews = [], products = [], interiors = [] }) => {
  return (
    <>
      <Hero />
      <Products products={products} />
      <HomeRequest />
      <Advantages />
      <InteriorSolutions interiors={interiors} />
      <Cases />
      <ProductionShowcase />
      <Reviews reviews={reviews} maxItems={10} />
      <WorkFeatures />
      <Questions />
      <Route />
    </>
  )
}

export const getServerSideProps = async () => {
  const siteData = await readSiteData()

  return {
    props: {
      reviews: siteData.reviews || [],
      products: (siteData.pages || [])
        .filter((page) => page.type === 'product' && page.status === 'published')
        .map((page) => ({
          title: page.title,
          slug: page.slug,
          description: page.menuDescription || page.seoDescription || '',
          image: page.cover || '',
        })),
      interiors: (siteData.pages || [])
        .filter((page) => page.type === 'interior' && page.status === 'published')
        .map((page) => ({
          title: page.title,
          slug: page.slug,
          description: page.menuDescription || page.seoDescription || '',
          image: getInteriorCover(page),
        })),
    },
  }
}
