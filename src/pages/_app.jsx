import { useEffect } from 'react'
import Head from 'next/head'
import { useRouter } from 'next/router'
import '@/styles/index.scss'
import Header from '@/layouts/Header'
import Content from '@/layouts/Content'
import Footer from '@/layouts/Footer'
import CallbackPopup from '@/components/CallbackPopup'
import { SiteSettingsProvider } from '@/contexts/SiteSettingsContext'

const siteName = 'Кубэра'
const siteDescription =
  'Производство столярных изделий из массива дерева: наличники, лестницы, порталы, арки, декор и изделия на заказ.'

const pageSeo = {
  '/': {
    title: 'Главная',
    description: siteDescription,
  },
  '/about': {
    title: 'О компании',
    description: 'Информация о производстве, ценностях и подходе столярной мастерской Кубэра.',
  },
  '/contacts': {
    title: 'Контакты',
    description: 'Контакты, график работы и схема проезда столярной мастерской Кубэра.',
  },
  '/document': {
    title: 'Документы',
    description: 'Документы и справочная информация компании Кубэра.',
  },
  '/reviews': {
    title: 'Отзывы',
    description: 'Отзывы клиентов о столярных изделиях и работе компании Кубэра.',
  },
  '/service': {
    title: 'Услуга',
    description: 'Услуги производства столярных изделий из массива дерева на заказ.',
  },
  '/services': {
    title: 'Продукция',
    description: 'Каталог столярных изделий из массива дерева от компании Кубэра.',
  },
}

const initClientModules = async () => {
  const [
    { default: OverlayMenu },
    { default: InputMaskCollection },
    { default: ReviewsSliderCollection },
    { default: ReviewsPageCollection },
    { default: CallbackPopupModule },
    { default: LeadForms },
  ] = await Promise.all([
    import('@/modules/OverlayMenu'),
    import('@/modules/InputMaskCollection'),
    import('@/modules/ReviewsSlider'),
    import('@/modules/ReviewsPage'),
    import('@/modules/CallbackPopup'),
    import('@/modules/LeadForms'),
  ])

  new OverlayMenu()
  new InputMaskCollection()
  new ReviewsSliderCollection()
  new ReviewsPageCollection()
  new CallbackPopupModule()
  new LeadForms()
}

export default function App({ Component, pageProps }) {
  const router = useRouter()
  const isAdminPage = Component.isAdminPage
  const cleanPath = router.pathname === '/404' ? router.pathname : router.pathname.replace(/\/$/, '')
  const seo = pageSeo[cleanPath] ?? pageSeo['/']
  const title = `${siteName} | ${seo.title}`

  useEffect(() => {
    initClientModules()
  }, [router.asPath])

  return (
    <>
      <Head>
        <meta charSet="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{title}</title>
        <meta name="description" content={seo.description} />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content={siteName} />
        <meta property="og:title" content={title} />
        <meta property="og:description" content={seo.description} />
        <meta name="twitter:card" content="summary_large_image" />
      </Head>
      {isAdminPage ? (
        <Component {...pageProps} />
      ) : (
        <SiteSettingsProvider>
          <Header url={router.asPath} />
          <Content>
            <Component {...pageProps} />
          </Content>
          <Footer />
          <CallbackPopup />
        </SiteSettingsProvider>
      )}
    </>
  )
}
