import clsx from 'clsx'
import arrowDown from '@/assets/icons/arrow-down.svg'
import arrowLeft from '@/assets/icons/arrow-left.svg'
import arrowRight from '@/assets/icons/arrow-right.svg'
import arrowTopRight from '@/assets/icons/arrow-top-right.svg'
import folder from '@/assets/icons/folder.svg'
import locationMinus from '@/assets/icons/location-minus.svg'
import mail from '@/assets/icons/mail.svg'
import max from '@/assets/icons/max.svg'
import phone from '@/assets/icons/phone.svg'
import pinterest from '@/assets/icons/pinterest.svg'
import telegram from '@/assets/icons/telegram.svg'
import vk from '@/assets/icons/vk.svg'
import youtube from '@/assets/icons/youtube.svg'

const icons = {
  'arrow-down': arrowDown,
  'arrow-left': arrowLeft,
  'arrow-right': arrowRight,
  'arrow-top-right': arrowTopRight,
  folder,
  'location-minus': locationMinus,
  mail,
  max,
  phone,
  pinterest,
  telegram,
  vk,
  youtube,
}

export default (props) => {
  const { className, name, hasFill = false, ariaLabel } = props
  const icon = icons[name]

  if (!icon) {
    return null
  }

  return (
    <span
      className={clsx('icon', className)}
      aria-label={ariaLabel}
      aria-hidden={ariaLabel ? undefined : true}
      data-fill={hasFill ? '' : undefined}
      style={{ '--iconUrl': `url(${icon})` }}
    />
  )
}
