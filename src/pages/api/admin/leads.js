import { requireAdmin } from '@/lib/adminAuth'
import { readLeads, writeLeads } from '@/lib/adminStorage'

export default async function handler(req, res) {
  if (!requireAdmin(req, res)) {
    return
  }

  const leads = await readLeads()

  if (req.method === 'GET') {
    return res.status(200).json({ ok: true, leads })
  }

  if (req.method === 'PATCH') {
    const { id, status } = req.body || {}
    let updatedLead = null
    const nextLeads = leads.map((lead) => {
      if (lead.id !== id) {
        return lead
      }

      updatedLead = {
        ...lead,
        status: status || lead.status,
        updatedAt: new Date().toISOString(),
      }

      return updatedLead
    })

    if (!updatedLead) {
      return res.status(404).json({ ok: false, message: 'Заявка не найдена' })
    }

    await writeLeads(nextLeads)
    return res.status(200).json({ ok: true, lead: updatedLead })
  }

  res.setHeader('Allow', 'GET, PATCH')
  return res.status(405).json({ ok: false })
}
