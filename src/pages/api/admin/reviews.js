import { requireAdmin } from '@/lib/adminAuth'
import { createId, readSiteData, writeSiteData } from '@/lib/adminStorage'

const normalizeText = (value) => String(value || '').trim()
const normalizeNumber = (value, fallback = 0) => {
  const number = Number(value)

  return Number.isFinite(number) ? number : fallback
}

const normalizeReview = (value = {}, fallback = {}) => ({
  id: value.id || fallback.id || createId('review'),
  author: normalizeText(value.author || fallback.author),
  date: normalizeText(value.date || fallback.date),
  text: normalizeText(value.text || fallback.text),
  avatar: normalizeText(value.avatar || fallback.avatar),
  category: normalizeText(value.category || fallback.category || 'all'),
  order: normalizeNumber(value.order ?? fallback.order, 0),
  status: value.status === 'draft' ? 'draft' : 'published',
  updatedAt: new Date().toISOString(),
})

const validateReview = (review) => {
  const missingFields = []

  if (!review.author) missingFields.push('Имя')
  if (!review.date) missingFields.push('Дата')
  if (!review.text) missingFields.push('Текст отзыва')

  return missingFields
}

export default async function handler(req, res) {
  if (!requireAdmin(req, res)) {
    return
  }

  const siteData = await readSiteData()
  const reviews = Array.isArray(siteData.reviews) ? siteData.reviews : []

  if (req.method === 'GET') {
    return res.status(200).json({ ok: true, reviews })
  }

  if (req.method === 'POST') {
    const review = normalizeReview(req.body)
    const missingFields = validateReview(review)

    if (missingFields.length) {
      return res.status(400).json({
        ok: false,
        message: `Заполните поля: ${missingFields.join(', ')}`,
      })
    }

    const nextReviews = [review, ...reviews]
    await writeSiteData({ ...siteData, reviews: nextReviews })

    return res.status(201).json({ ok: true, review, reviews: nextReviews })
  }

  if (req.method === 'PUT') {
    const body = req.body || {}
    let updatedReview = null
    const nextReviews = reviews.map((review) => {
      if (review.id !== body.id) {
        return review
      }

      updatedReview = normalizeReview(body, review)
      return updatedReview
    })

    if (!updatedReview) {
      return res.status(404).json({ ok: false, message: 'Отзыв не найден' })
    }

    const missingFields = validateReview(updatedReview)

    if (missingFields.length) {
      return res.status(400).json({
        ok: false,
        message: `Заполните поля: ${missingFields.join(', ')}`,
      })
    }

    await writeSiteData({ ...siteData, reviews: nextReviews })

    return res.status(200).json({ ok: true, review: updatedReview, reviews: nextReviews })
  }

  if (req.method === 'DELETE') {
    const { id } = req.query
    const nextReviews = reviews.filter((review) => review.id !== id)

    await writeSiteData({ ...siteData, reviews: nextReviews })
    return res.status(200).json({ ok: true, reviews: nextReviews })
  }

  res.setHeader('Allow', 'GET, POST, PUT, DELETE')
  return res.status(405).json({ ok: false })
}
