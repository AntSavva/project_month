import clsx from 'clsx'
import { useState } from 'react'
import productCoverImage from '@/assets/images/AboutProduction/production-photo.png'
import interiorCoverImage from '@/assets/images/Cases/case-preview.png'
import BurgerButton from '@/components/BurgerButton'
import Button from '@/components/Button'
import Icon from '@/components/Icon'
import Logo from '@/components/Logo'
import { useSiteSettings } from '@/contexts/SiteSettingsContext'
import { getEmailHref, getPhoneHref } from '@/lib/siteSettings'

const normalizePath = (value = '') => {
  const path = value.split('?')[0].split('#')[0]

  return path.endsWith('/') && path !== '/' ? path.slice(0, -1) : path
}

const isActiveHref = (href, currentPath) => normalizePath(href) === normalizePath(currentPath)

const createMenuItem = (page) => ({
  label: page.title,
  href: page.href,
  description: page.menuDescription,
  cover: page.cover,
})

const getCoverSrc = (items, fallbackImage) =>
  items.find((item) => item.cover)?.cover || fallbackImage.src

const DropdownMenu = ({ dropdown, currentPath }) => {
  const activePage =
    dropdown.items.find((item) => isActiveHref(item.href, currentPath)) ||
    dropdown.items.find((item) => item.cover) ||
    dropdown.items[0]
  const [previewItem, setPreviewItem] = useState(activePage)
  const previewCover = previewItem?.cover || getCoverSrc(dropdown.items, dropdown.fallbackCover)

  return (
    <div className="header__mega-menu" aria-label={dropdown.title}>
      <div className="header__mega-menu-inner header__mega-menu-inner--compact">
        <ul className="header__mega-list">
          {dropdown.items.map((item) => {
            const { label, href } = item

            return (
              <li className="header__mega-item" key={href}>
                <a
                  className={clsx('header__mega-link', {
                    'is-active': isActiveHref(href, currentPath),
                  })}
                  href={href}
                  onMouseEnter={() => setPreviewItem(item)}
                  onFocus={() => setPreviewItem(item)}
                >
                  {label}
                </a>
              </li>
            )
          })}
        </ul>

        <img
          className="header__mega-cover"
          src={previewCover}
          alt=""
          width="730"
          height="360"
        />
      </div>
    </div>
  )
}

const DropdownButton = ({ label }) => (
  <button className="header__nav-link" type="button" aria-haspopup="true">
    <span>{label}</span>
    <svg
      className="header__nav-arrow"
      width="8"
      height="6"
      viewBox="0 0 8 6"
      aria-hidden="true"
    >
      <path d="M4 6L0.535898 0H7.4641L4 6Z" />
    </svg>
  </button>
)

export default (props) => {
  const { className, url = '' } = props
  const settings = useSiteSettings()
  const productItems = settings.pages.filter((page) => page.type === 'product').map(createMenuItem)
  const interiorItems = settings.pages.filter((page) => page.type === 'interior').map(createMenuItem)
  const dropdowns = {
    products: {
      title: 'Продукция',
      items: productItems,
      fallbackCover: productCoverImage,
    },
    interior: {
      title: 'Отделка интерьера',
      items: interiorItems,
      fallbackCover: interiorCoverImage,
    },
  }
  const navigationItems = [
    productItems.length && {
      label: 'Продукция',
      href: '/services/',
      dropdown: 'products',
    },
    interiorItems.length && {
      label: 'Отделка интерьера',
      href: '/interior/',
      dropdown: 'interior',
    },
    {
      label: 'О компании',
      href: '/about/',
    },
    {
      label: 'Отзывы',
      href: '/reviews/',
    },
    {
      label: 'Контакты',
      href: '/contacts/',
    },
  ].filter(Boolean)

  const closeOverlayMenu = () => {
    if (typeof document === 'undefined') {
      return
    }

    const dialogElement = document.querySelector('[data-js-overlay-menu-dialog]')
    const burgerButtonElement = document.querySelector('[data-js-overlay-menu-burger-button]')

    burgerButtonElement?.classList.remove('is-active')
    document.documentElement.classList.remove('is-lock')

    if (dialogElement?.open && typeof dialogElement.close === 'function') {
      dialogElement.close()
    } else if (dialogElement) {
      dialogElement.open = false
    }
  }

  return (
    <header className={clsx('header', className)} data-js-overlay-menu="">
      <div className="header__inner">
        <Logo className="header__logo" loading="eager" />

        <nav className="header__nav" aria-label="Основная навигация">
          <ul className="header__nav-list">
            {navigationItems.map(({ label, href, dropdown }) => (
              <li
                className={clsx('header__nav-item', {
                  'header__nav-item--dropdown': dropdown,
                })}
                key={label}
              >
                {dropdown ? (
                  <DropdownButton label={label} />
                ) : (
                  <a
                    className={clsx('header__nav-link', {
                      'is-active': isActiveHref(href, url),
                    })}
                    href={href}
                  >
                    <span>{label}</span>
                  </a>
                )}

                {dropdown && <DropdownMenu dropdown={dropdowns[dropdown]} currentPath={url} />}
              </li>
            ))}
          </ul>
        </nav>

        <div className="header__actions">
          <address className="header__contacts">
            <a className="header__contact-link" href={getPhoneHref(settings.phone)}>
              <Icon className="header__contact-icon" name="phone" hasFill />
              <span>{settings.phone}</span>
            </a>
            <a className="header__contact-link" href={getEmailHref(settings.email)}>
              <Icon className="header__contact-icon" name="mail" hasFill />
              <span>{settings.email}</span>
            </a>
          </address>

          <Button
            className="header__callback"
            extraAttrs={{ 'data-js-callback-popup-open': '' }}
          >
            Записаться на замер
          </Button>

          <BurgerButton
            className="header__burger visible-tablet"
            extraAttrs={{ 'data-js-overlay-menu-burger-button': '' }}
          />
        </div>
      </div>

      <dialog className="header__overlay-menu" data-js-overlay-menu-dialog="">
        <div className="header__overlay-menu-inner">
          <div className="header__overlay-head">
            <Logo className="header__overlay-logo" loading="lazy" />
            <button
              className="header__overlay-close"
              type="button"
              aria-label="Закрыть меню"
              onClick={closeOverlayMenu}
              data-js-overlay-menu-close=""
            />
          </div>
          <nav className="header__overlay-nav" aria-label="Мобильная навигация">
            {productItems.length > 0 && (
              <details className="header__overlay-nav-group" open>
                <summary className="header__overlay-nav-title">
                  Продукция
                </summary>
                <ul className="header__overlay-product-list">
                  {productItems.map(({ label, description, href }) => (
                    <li className="header__overlay-product-item" key={href}>
                      <a
                        className={clsx('header__overlay-product-link', {
                          'is-active': isActiveHref(href, url),
                        })}
                        href={href}
                      >
                        <span className="header__overlay-product-label">{label}</span>
                        {description && (
                          <span className="header__overlay-product-description">
                            {description}
                          </span>
                        )}
                      </a>
                    </li>
                  ))}
                </ul>
              </details>
            )}

            {interiorItems.length > 0 && (
              <details className="header__overlay-nav-group" open>
                <summary className="header__overlay-nav-title">
                  Отделка интерьера
                </summary>
                <ul className="header__overlay-product-list">
                  {interiorItems.map(({ label, href }) => (
                    <li className="header__overlay-product-item" key={href}>
                      <a
                        className={clsx('header__overlay-product-link', {
                          'is-active': isActiveHref(href, url),
                        })}
                        href={href}
                      >
                        <span className="header__overlay-product-label">{label}</span>
                      </a>
                    </li>
                  ))}
                </ul>
              </details>
            )}

            <ul className="header__overlay-nav-list header__overlay-nav-list--main">
              {navigationItems
                .filter((item) => !item.dropdown)
                .map(({ label, href }) => (
                  <li className="header__overlay-nav-item" key={label}>
                    <a
                      className={clsx('header__overlay-nav-link', {
                        'is-active': isActiveHref(href, url),
                      })}
                      href={href}
                    >
                      {label}
                    </a>
                  </li>
                ))}
            </ul>
          </nav>

          <div className="header__overlay-footer">
            <address className="header__overlay-contacts">
              <a
                className="header__overlay-contact-link"
                href={getPhoneHref(settings.phone)}
              >
                <Icon
                  className="header__overlay-contact-icon"
                  name="phone"
                  hasFill
                />
                <span>{settings.phone}</span>
              </a>
              <a
                className="header__overlay-contact-link"
                href={getEmailHref(settings.email)}
              >
                <Icon
                  className="header__overlay-contact-icon"
                  name="mail"
                  hasFill
                />
                <span>{settings.email}</span>
              </a>
            </address>

            <Button
              className="header__overlay-callback"
              extraAttrs={{ 'data-js-callback-popup-open': '' }}
            >
              Записаться на замер
            </Button>
          </div>
        </div>
      </dialog>
    </header>
  )
}
