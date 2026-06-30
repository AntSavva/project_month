import crypto from 'crypto'

const accessCookieName = 'kubera_admin_access'
const refreshCookieName = 'kubera_admin_refresh'
const accessMaxAge = 15 * 60
const refreshMaxAge = 60 * 60 * 24 * 7

const parseCookies = (cookieHeader = '') =>
  cookieHeader.split(';').reduce((acc, cookie) => {
    const [key, ...value] = cookie.trim().split('=')

    if (key) {
      acc[key] = decodeURIComponent(value.join('='))
    }

    return acc
  }, {})

const base64UrlEncode = (value) =>
  Buffer.from(value)
    .toString('base64')
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_')

const base64UrlDecode = (value) => {
  const normalized = value.replace(/-/g, '+').replace(/_/g, '/')
  const padding = '='.repeat((4 - (normalized.length % 4)) % 4)

  return Buffer.from(`${normalized}${padding}`, 'base64').toString('utf8')
}

const getJwtSecret = () => process.env.JWT_SECRET || process.env.ADMIN_JWT_SECRET || adminPassword()

const getRefreshJwtSecret = () =>
  process.env.JWT_REFRESH_SECRET || process.env.ADMIN_JWT_REFRESH_SECRET || `${getJwtSecret()}:refresh`

const getCookieOptions = (maxAge) => [
  'Path=/',
  'HttpOnly',
  'SameSite=Lax',
  `Max-Age=${maxAge}`,
  process.env.NODE_ENV === 'production' ? 'Secure' : '',
]
  .filter(Boolean)
  .join('; ')

const signToken = (payload, secret, maxAge) => {
  const now = Math.floor(Date.now() / 1000)
  const header = { alg: 'HS256', typ: 'JWT' }
  const body = {
    ...payload,
    iat: now,
    exp: now + maxAge,
  }
  const encodedHeader = base64UrlEncode(JSON.stringify(header))
  const encodedBody = base64UrlEncode(JSON.stringify(body))
  const signature = crypto
    .createHmac('sha256', secret)
    .update(`${encodedHeader}.${encodedBody}`)
    .digest('base64')
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_')

  return `${encodedHeader}.${encodedBody}.${signature}`
}

const verifyToken = (token, secret, expectedType) => {
  if (!token || typeof token !== 'string') {
    return null
  }

  const [encodedHeader, encodedBody, signature] = token.split('.')

  if (!encodedHeader || !encodedBody || !signature) {
    return null
  }

  const expectedSignature = crypto
    .createHmac('sha256', secret)
    .update(`${encodedHeader}.${encodedBody}`)
    .digest('base64')
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_')

  const signatureBuffer = Buffer.from(signature)
  const expectedBuffer = Buffer.from(expectedSignature)

  if (
    signatureBuffer.length !== expectedBuffer.length ||
    !crypto.timingSafeEqual(signatureBuffer, expectedBuffer)
  ) {
    return null
  }

  try {
    const header = JSON.parse(base64UrlDecode(encodedHeader))
    const payload = JSON.parse(base64UrlDecode(encodedBody))
    const now = Math.floor(Date.now() / 1000)

    if (
      header.alg !== 'HS256' ||
      header.typ !== 'JWT' ||
      typeof payload.exp !== 'number' ||
      payload.exp <= now ||
      payload.type !== expectedType ||
      payload.sub !== 'admin'
    ) {
      return null
    }

    return payload
  } catch {
    return null
  }
}

const createAccessToken = () =>
  signToken({ sub: 'admin', type: 'access' }, getJwtSecret(), accessMaxAge)

const createRefreshToken = () =>
  signToken({ sub: 'admin', type: 'refresh' }, getRefreshJwtSecret(), refreshMaxAge)

const setCookies = (res, cookies) => {
  res.setHeader('Set-Cookie', cookies)
}

export const adminPassword = () => process.env.ADMIN_PASSWORD || 'admin123'

export const isAdminRequest = (req) => {
  const cookies = parseCookies(req.headers.cookie)
  return Boolean(verifyToken(cookies[accessCookieName], getJwtSecret(), 'access'))
}

export const hasValidRefreshToken = (req) => {
  const cookies = parseCookies(req.headers.cookie)
  return Boolean(verifyToken(cookies[refreshCookieName], getRefreshJwtSecret(), 'refresh'))
}

export const requireAdmin = (req, res) => {
  if (isAdminRequest(req)) {
    return true
  }

  res.status(401).json({ ok: false, message: 'Unauthorized' })
  return false
}

export const setAdminAuthCookies = (res) => {
  setCookies(res, [
    `${accessCookieName}=${createAccessToken()}; ${getCookieOptions(accessMaxAge)}`,
    `${refreshCookieName}=${createRefreshToken()}; ${getCookieOptions(refreshMaxAge)}`,
  ])
}

export const refreshAdminAuthCookies = (req, res) => {
  if (!hasValidRefreshToken(req)) {
    return false
  }

  setAdminAuthCookies(res)
  return true
}

export const clearAdminAuthCookies = (res) => {
  setCookies(res, [
    `${accessCookieName}=; ${getCookieOptions(0)}`,
    `${refreshCookieName}=; ${getCookieOptions(0)}`,
  ])
}
