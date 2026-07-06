import AboutHero from '@/sections/AboutHero'
import AboutProduction from '@/sections/AboutProduction'
import AboutValues from '@/sections/AboutValues'
import Route from '@/sections/Route'

export const metadata = {
  title: 'О компании',
}

export default () => {
  return (
    <>
      <AboutHero />
      <AboutValues />
      <AboutProduction />
      <Route />
    </>
  )
}
