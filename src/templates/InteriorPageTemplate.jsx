import Head from 'next/head'
import InteriorAdvantages from '@/sections/InteriorAdvantages'
import InteriorHero from '@/sections/InteriorHero'
import InteriorIncludes from '@/sections/InteriorIncludes'
import InteriorPlans from '@/sections/InteriorPlans'
import InteriorProposalRequest from '@/sections/InteriorProposalRequest'
import InteriorQuestions from '@/sections/InteriorQuestions'
import InteriorRoomSolutions from '@/sections/InteriorRoomSolutions'
import HomeRequest from '@/sections/HomeRequest'
import Cases from '@/sections/Cases'
import Reviews from '@/sections/Reviews'
import Route from '@/sections/Route'
import ServiceFaq from '@/sections/ServiceFaq'
import ServiceMaterials from '@/sections/ServiceMaterials'

export default function InteriorPageTemplate(props) {
  const { page, content, reviews = [] } = props
  const seoTitle = page?.seoTitle || page?.title || 'Отделка интерьера'
  const seoDescription = page?.seoDescription || ''
  const heroData = {
    ...(content?.hero || {}),
    image: content?.hero?.image || page?.cover,
  }

  return (
    <>
      <Head>
        <title>{seoTitle}</title>
        {seoDescription && <meta name="description" content={seoDescription} />}
      </Head>
      <InteriorHero data={heroData} />
      <InteriorIncludes data={content?.includes} />
      <InteriorRoomSolutions data={content?.roomSolutions} />
      <ServiceMaterials data={content?.materials} title="Материалы для отделки" />
      <InteriorProposalRequest />
      <InteriorAdvantages data={content?.advantages} />
      <InteriorPlans data={content?.plans} />
      <HomeRequest />
      <Cases />
      <Reviews reviews={reviews} maxItems={10} />
      <ServiceFaq items={content?.faq?.items} />
      <InteriorQuestions />
      <Route />
    </>
  )
}
