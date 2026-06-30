import { readSiteData } from '@/lib/adminStorage'

export default async function handler(req, res) {
  if (req.method !== 'GET') {
    res.setHeader('Allow', 'GET')
    return res.status(405).json({ ok: false })
  }

  const siteData = await readSiteData()
  const pages = siteData.pages
    .filter((page) => page.status === 'published')
    .map(({ id, type, title, slug, menuDescription, cover }) => ({
      id,
      type,
      title,
      slug,
      href:
        slug === 'service' || slug === '/service'
          ? '/service/'
          : slug === 'interior' || slug === '/interior'
            ? '/interior/'
            : `/${slug}/`,
      menuDescription,
      cover,
    }))

  return res.status(200).json({ ok: true, settings: siteData.settings, pages })
}
