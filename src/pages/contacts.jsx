import ContactsHero from '@/sections/ContactsHero'
import ContactsSchedule from '@/sections/ContactsSchedule'
import Questions from '@/sections/Questions'
import Route from '@/sections/Route'

export const metadata = {
  title: 'Контакты',
}

export default () => {
  return (
    <>
      <ContactsHero />
      <ContactsSchedule />
      <Questions />
      <Route />
    </>
  )
}
