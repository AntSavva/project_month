import { isAdminRequest, refreshAdminAuthCookies } from '@/lib/adminAuth'

export default function handler(req, res) {
  if (isAdminRequest(req) || refreshAdminAuthCookies(req, res)) {
    return res.status(200).json({ ok: true, isAuthenticated: true })
  }

  return res.status(200).json({ ok: true, isAuthenticated: false })
}
