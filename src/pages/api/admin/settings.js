import { requireAdmin } from '@/lib/adminAuth'
import { readSiteData, writeSiteData } from '@/lib/adminStorage'

export default async function handler(req, res) {
  if (!requireAdmin(req, res)) {
    return
  }

  const siteData = await readSiteData()

  if (req.method === 'GET') {
    return res.status(200).json({ ok: true, settings: siteData.settings })
  }

  if (req.method === 'PUT') {
    const nextSettings = {
      ...siteData.settings,
      ...(req.body || {}),
      socials: {
        ...siteData.settings.socials,
        ...(req.body?.socials || {}),
      },
    }

    await writeSiteData({ ...siteData, settings: nextSettings })
    return res.status(200).json({ ok: true, settings: nextSettings })
  }

  res.setHeader('Allow', 'GET, PUT')
  return res.status(405).json({ ok: false })
}
