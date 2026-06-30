import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { defaultSiteSettings, getSettingsWithDefaults } from '@/lib/siteSettings'

const SiteSettingsContext = createContext(defaultSiteSettings)

export const SiteSettingsProvider = ({ children }) => {
  const [settings, setSettings] = useState(defaultSiteSettings)

  useEffect(() => {
    let isMounted = true

    const loadSettings = async () => {
      try {
        const response = await fetch('/api/settings')
        const data = await response.json()

        if (isMounted && response.ok) {
          setSettings(getSettingsWithDefaults({ ...data.settings, pages: data.pages }))
        }
      } catch (error) {
        // Defaults keep the public site usable if settings cannot be loaded.
      }
    }

    loadSettings()
    window.addEventListener('focus', loadSettings)
    document.addEventListener('visibilitychange', loadSettings)

    return () => {
      isMounted = false
      window.removeEventListener('focus', loadSettings)
      document.removeEventListener('visibilitychange', loadSettings)
    }
  }, [])

  const value = useMemo(() => getSettingsWithDefaults(settings), [settings])

  return <SiteSettingsContext.Provider value={value}>{children}</SiteSettingsContext.Provider>
}

export const useSiteSettings = () => useContext(SiteSettingsContext)
