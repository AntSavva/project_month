import { adminPassword, setAdminAuthCookies } from '@/lib/adminAuth'

export default function handler(req, res) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST')
    return res.status(405).json({ ok: false })
  }

  const { password } = req.body || {}

  if (password !== adminPassword()) {
    return res.status(401).json({ ok: false, message: 'Неверный пароль' })
  }

  setAdminAuthCookies(res)
  return res.status(200).json({ ok: true })
}
