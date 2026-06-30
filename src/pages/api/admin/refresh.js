import { refreshAdminAuthCookies } from '@/lib/adminAuth'

export default function handler(req, res) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST')
    return res.status(405).json({ ok: false })
  }

  if (!refreshAdminAuthCookies(req, res)) {
    return res.status(401).json({ ok: false, message: 'Unauthorized' })
  }

  return res.status(200).json({ ok: true })
}
