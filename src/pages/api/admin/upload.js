import { mkdir, writeFile } from 'fs/promises'
import path from 'path'
import { requireAdmin } from '@/lib/adminAuth'

const allowedMimeTypes = {
  'image/jpeg': 'jpg',
  'image/png': 'png',
  'image/webp': 'webp',
  'image/gif': 'gif',
}

const sanitizeName = (value = 'cover') =>
  value
    .toString()
    .toLowerCase()
    .replace(/\.[^.]+$/, '')
    .replace(/[^a-z0-9а-яё_-]+/gi, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 80) || 'cover'

export const config = {
  api: {
    bodyParser: {
      sizeLimit: '6mb',
    },
  },
}

export default async function handler(req, res) {
  if (!requireAdmin(req, res)) {
    return
  }

  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST')
    return res.status(405).json({ ok: false })
  }

  const { fileName = 'cover', dataUrl = '' } = req.body || {}
  const match = dataUrl.match(/^data:([^;]+);base64,(.+)$/)

  if (!match) {
    return res.status(400).json({ ok: false, message: 'Некорректный файл' })
  }

  const [, mimeType, content] = match
  const extension = allowedMimeTypes[mimeType]

  if (!extension) {
    return res.status(400).json({ ok: false, message: 'Поддерживаются JPG, PNG, WebP и GIF' })
  }

  const buffer = Buffer.from(content, 'base64')

  if (buffer.byteLength > 5 * 1024 * 1024) {
    return res.status(400).json({ ok: false, message: 'Файл должен быть меньше 5 МБ' })
  }

  const uploadsDir = path.join(process.cwd(), 'public', 'uploads')
  await mkdir(uploadsDir, { recursive: true })

  const name = `${Date.now()}-${sanitizeName(fileName)}.${extension}`
  const filePath = path.join(uploadsDir, name)

  await writeFile(filePath, buffer)

  return res.status(201).json({ ok: true, url: `/uploads/${name}` })
}
