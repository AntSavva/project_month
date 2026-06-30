import Head from 'next/head'
import ServiceColors from '@/sections/ServiceColors'
import ServiceBenefits from '@/sections/ServiceBenefits'
import ServiceCases from '@/sections/ServiceCases'
import ServiceHero from '@/sections/ServiceHero'
import ServiceIncludes from '@/sections/ServiceIncludes'
import ServiceMaterials from '@/sections/ServiceMaterials'
import ServiceOptions from '@/sections/ServiceOptions'
import ServicePlans from '@/sections/ServicePlans'
import ServiceProcess from '@/sections/ServiceProcess'
import ServiceRequest from '@/sections/ServiceRequest'
import HomeRequest from '@/sections/HomeRequest'
import Reviews from '@/sections/Reviews'
import ServiceFaq from '@/sections/ServiceFaq'
import Questions from '@/sections/Questions'
import Route from '@/sections/Route'

export default function ProductPageTemplate(props) {
  const { page, content, reviews = [] } = props
  const seoTitle = page?.seoTitle || page?.title || 'Услуга'
  const seoDescription = page?.seoDescription || ''

  return (
    <>
      <Head>
        <title>{seoTitle}</title>
        {seoDescription && <meta name="description" content={seoDescription} />}
      </Head>
      <ServiceHero data={content?.hero} image={page?.cover} />
      <ServiceIncludes data={content?.includes} />
      <ServiceMaterials data={content?.materials} />
      <ServiceColors data={content?.colors} />
      <ServiceRequest />
      <ServiceBenefits data={content?.benefits} />
      <ServicePlans data={content?.plans} />
      <HomeRequest />
      <ServiceOptions />
      <ServiceCases />
      <ServiceProcess />
      <Reviews reviews={reviews} maxItems={10} />
      <ServiceFaq items={content?.faq?.items} />
      <Questions />
      <Route />
    </>
  )
}
