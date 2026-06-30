import ReviewsHero from '@/sections/ReviewsHero'
import Questions from '@/sections/Questions'
import Route from '@/sections/Route'
import { readSiteData } from '@/lib/adminStorage'

export const metadata = {
  title: 'Отзывы',
}

export default ({ reviews = [] }) => {
  return (
    <>
      <ReviewsHero reviews={reviews} />
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
    },
  }
}
