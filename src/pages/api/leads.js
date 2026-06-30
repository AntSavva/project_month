import { createId, readLeads, writeLeads } from '@/lib/adminStorage'

const normalizeText = (value) => String(value || '').trim()
const normalizePhone = (value) => {
  const phone = normalizeText(value)

  return ['phone', 'tel', 'telephone'].includes(phone.toLowerCase()) ? '' : phone
}

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    res.setHeader('Allow', 'POST')
    return res.status(405).json({ ok: false })
  }

  const body = req.body || {}
  const name = normalizeText(body.name)
  const phone = normalizePhone(body.phone || body.tel)
  const comment = normalizeText(body.comment)

  if (!phone && !name) {
    return res.status(400).json({
      ok: false,
      message: 'Укажите имя или телефон',
    })
  }

  const leads = await readLeads()
  const lead = {
    id: createId('lead'),
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
    status: 'new',
    name,
    phone,
    email: normalizeText(body.email),
    comment,
    product: normalizeText(body.product),
    plan: normalizeText(body.plan),
    contactMethod: normalizeText(body.contactMethod),
    source: normalizeText(body.source),
    form: normalizeText(body.form),
  }

  await writeLeads([lead, ...leads])

  return res.status(201).json({ ok: true, lead })
}
