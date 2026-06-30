import { clearAdminAuthCookies } from '@/lib/adminAuth'

export default function handler(req, res) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST')
    return res.status(405).json({ ok: false })
  }

  clearAdminAuthCookies(res)
  return res.status(200).json({ ok: true })
}
