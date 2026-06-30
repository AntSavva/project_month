import { requireAdmin } from '@/lib/adminAuth'
import {
  createDefaultInteriorContent,
  createDefaultDocumentContent,
  createDefaultProductContent,
  createId,
  readSiteData,
  writeSiteData,
} from '@/lib/adminStorage'

const allowedTypes = ['product', 'interior', 'document']

const normalizeSlug = (slug = '') =>
  slug
    .toString()
    .trim()
    .replace(/^\/+|\/+$/g, '')

const requiredPageFields = [
  ['title', 'Название'],
  ['slug', 'URL / slug'],
  ['menuDescription', 'Описание в меню'],
  ['seoTitle', 'SEO title'],
  ['seoDescription', 'SEO description'],
  ['cover', 'Обложка'],
]

const getMissingPageFields = (page) =>
  requiredPageFields
    .filter(([field]) => {
      if (page.type === 'document' && (field === 'menuDescription' || field === 'cover')) {
        return false
      }

      return !page[field]?.toString().trim()
    })
    .map(([, label]) => label)

const createDefaultContent = (type) => {
  if (type === 'interior') {
    return createDefaultInteriorContent()
  }

  if (type === 'document') {
    return createDefaultDocumentContent()
  }

  return createDefaultProductContent()
}

export default async function handler(req, res) {
  if (!requireAdmin(req, res)) {
    return
  }

  const siteData = await readSiteData()

  if (req.method === 'GET') {
    return res.status(200).json({ ok: true, pages: siteData.pages })
  }

  if (req.method === 'POST') {
    const body = req.body || {}
    const type = allowedTypes.includes(body.type) ? body.type : 'product'
    const now = new Date().toISOString()
    const missingFields = getMissingPageFields({
      ...body,
      slug: normalizeSlug(body.slug),
    })

    if (missingFields.length) {
      return res.status(400).json({
        ok: false,
        message: `Заполните поля: ${missingFields.join(', ')}`,
      })
    }

    const page = {
      id: createId(type),
      type,
      title: body.title || 'Новая страница',
      slug: normalizeSlug(body.slug),
      menuDescription: body.menuDescription || '',
      seoTitle: body.seoTitle || body.title || '',
      seoDescription: body.seoDescription || '',
      status: body.status || (type === 'document' ? 'published' : 'draft'),
      cover: body.cover || '',
      content: body.content || createDefaultContent(type),
      updatedAt: now,
    }

    const nextData = {
      ...siteData,
      pages: [page, ...siteData.pages],
    }

    await writeSiteData(nextData)
    return res.status(201).json({ ok: true, page })
  }

  if (req.method === 'PUT') {
    const body = req.body || {}
    const now = new Date().toISOString()
    let updatedPage = null
    const pages = siteData.pages.map((page) => {
      if (page.id !== body.id) {
        return page
      }

      updatedPage = {
        ...page,
        ...body,
        type: allowedTypes.includes(body.type) ? body.type : page.type,
        slug: normalizeSlug(body.slug || page.slug),
        updatedAt: now,
      }

      return updatedPage
    })

    if (!updatedPage) {
      return res.status(404).json({ ok: false, message: 'Страница не найдена' })
    }

    await writeSiteData({ ...siteData, pages })
    return res.status(200).json({ ok: true, page: updatedPage })
  }

  if (req.method === 'DELETE') {
    const { id } = req.query
    const pages = siteData.pages.filter((page) => page.id !== id)

    await writeSiteData({ ...siteData, pages })
    return res.status(200).json({ ok: true })
  }

  res.setHeader('Allow', 'GET, POST, PUT, DELETE')
  return res.status(405).json({ ok: false })
}
