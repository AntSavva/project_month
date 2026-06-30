import { requireAdmin } from '@/lib/adminAuth'
import { readSiteData, writeSiteData } from '@/lib/adminStorage'

export default async function handler(req, res) {
  if (!requireAdmin(req, res)) {
    return
  }

  const siteData = await readSiteData()

  if (req.method === 'GET') {
    return res.status(200).json({ ok: true, productPage: siteData.productPage })
  }

  if (req.method === 'PUT') {
    const productPage = {
      ...siteData.productPage,
      ...(req.body || {}),
    }

    await writeSiteData({ ...siteData, productPage })
    return res.status(200).json({ ok: true, productPage })
  }

  res.setHeader('Allow', 'GET, PUT')
  return res.status(405).json({ ok: false })
}
