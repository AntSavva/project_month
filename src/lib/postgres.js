import pg from 'pg'

const { Pool } = pg

let pool

export const isDatabaseConfigured = () => Boolean(process.env.DATABASE_URL)

const getPool = () => {
  if (!isDatabaseConfigured()) {
    return null
  }

  if (!pool) {
    pool = new Pool({
      connectionString: process.env.DATABASE_URL,
    })
  }

  return pool
}

const toCamelPage = (row) => ({
  id: row.id,
  type: row.type,
  title: row.title,
  slug: row.slug,
  menuDescription: row.menu_description,
  seoTitle: row.seo_title,
  seoDescription: row.seo_description,
  status: row.status,
  cover: row.cover,
  content: row.content,
  createdAt: row.created_at,
  updatedAt: row.updated_at,
})

const toCamelSettings = (row) => ({
  id: row.id,
  phone: row.phone,
  email: row.email,
  address: row.address,
  workingHours: row.working_hours,
  legalInfo: row.legal_info,
  socials: row.socials,
  createdAt: row.created_at,
  updatedAt: row.updated_at,
})

const toCamelReview = (row) => ({
  id: row.id,
  author: row.author,
  date: row.date,
  text: row.text,
  avatar: row.avatar,
  category: row.category,
  order: row.sort_order,
  status: row.status,
  createdAt: row.created_at,
  updatedAt: row.updated_at,
})

const toCamelLead = (row) => ({
  id: row.id,
  status: row.status,
  name: row.name,
  phone: row.phone,
  email: row.email,
  comment: row.comment,
  product: row.product,
  plan: row.plan,
  contactMethod: row.contact_method,
  source: row.source,
  form: row.form,
  createdAt: row.created_at,
  updatedAt: row.updated_at,
})

const emptyNotIn = ['__empty__']

export const getPostgresStorage = async () => {
  const database = getPool()

  if (!database) {
    return null
  }

  await database.query('SELECT 1')

  return {
    $transaction: async (operations) => Promise.all(operations),
    siteSettings: {
      findUnique: async ({ where }) => {
        const result = await database.query('SELECT * FROM site_settings WHERE id = $1 LIMIT 1', [
          where.id,
        ])

        return result.rows[0] ? toCamelSettings(result.rows[0]) : null
      },
      upsert: async ({ where, create, update }) => {
        const data = { ...create, ...update }
        const result = await database.query(
          `
            INSERT INTO site_settings (id, phone, email, address, working_hours, legal_info, socials)
            VALUES ($1, $2, $3, $4, $5, $6, $7::jsonb)
            ON CONFLICT (id) DO UPDATE SET
              phone = EXCLUDED.phone,
              email = EXCLUDED.email,
              address = EXCLUDED.address,
              working_hours = EXCLUDED.working_hours,
              legal_info = EXCLUDED.legal_info,
              socials = EXCLUDED.socials,
              updated_at = now()
            RETURNING *
          `,
          [
            where.id,
            data.phone || '',
            data.email || '',
            data.address || '',
            data.workingHours || '',
            data.legalInfo || '',
            JSON.stringify(data.socials || {}),
          ]
        )

        return toCamelSettings(result.rows[0])
      },
    },
    page: {
      findMany: async () => {
        const result = await database.query('SELECT * FROM pages ORDER BY updated_at DESC')

        return result.rows.map(toCamelPage)
      },
      deleteMany: async ({ where }) => {
        const ids = where.id.notIn.length ? where.id.notIn : emptyNotIn

        return database.query('DELETE FROM pages WHERE NOT (id = ANY($1::text[]))', [ids])
      },
      upsert: async ({ where, create, update }) => {
        const data = { ...create, ...update }
        const result = await database.query(
          `
            INSERT INTO pages (
              id, type, title, slug, menu_description, seo_title, seo_description, status, cover, content
            )
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10::jsonb)
            ON CONFLICT (id) DO UPDATE SET
              type = EXCLUDED.type,
              title = EXCLUDED.title,
              slug = EXCLUDED.slug,
              menu_description = EXCLUDED.menu_description,
              seo_title = EXCLUDED.seo_title,
              seo_description = EXCLUDED.seo_description,
              status = EXCLUDED.status,
              cover = EXCLUDED.cover,
              content = EXCLUDED.content,
              updated_at = now()
            RETURNING *
          `,
          [
            where.id,
            data.type,
            data.title,
            data.slug,
            data.menuDescription || '',
            data.seoTitle || '',
            data.seoDescription || '',
            data.status || 'draft',
            data.cover || '',
            JSON.stringify(data.content || {}),
          ]
        )

        return toCamelPage(result.rows[0])
      },
    },
    review: {
      findMany: async () => {
        const result = await database.query(
          'SELECT * FROM reviews ORDER BY sort_order DESC, created_at DESC'
        )

        return result.rows.map(toCamelReview)
      },
      deleteMany: async ({ where }) => {
        const ids = where.id.notIn.length ? where.id.notIn : emptyNotIn

        return database.query('DELETE FROM reviews WHERE NOT (id = ANY($1::text[]))', [ids])
      },
      upsert: async ({ where, create, update }) => {
        const data = { ...create, ...update }
        const result = await database.query(
          `
            INSERT INTO reviews (id, author, date, text, avatar, category, sort_order, status)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
            ON CONFLICT (id) DO UPDATE SET
              author = EXCLUDED.author,
              date = EXCLUDED.date,
              text = EXCLUDED.text,
              avatar = EXCLUDED.avatar,
              category = EXCLUDED.category,
              sort_order = EXCLUDED.sort_order,
              status = EXCLUDED.status,
              updated_at = now()
            RETURNING *
          `,
          [
            where.id,
            data.author || '',
            data.date || '',
            data.text || '',
            data.avatar || '',
            data.category || 'all',
            Number(data.order) || 0,
            data.status || 'published',
          ]
        )

        return toCamelReview(result.rows[0])
      },
    },
    lead: {
      findMany: async () => {
        const result = await database.query('SELECT * FROM leads ORDER BY created_at DESC')

        return result.rows.map(toCamelLead)
      },
      deleteMany: async ({ where }) => {
        const ids = where.id.notIn.length ? where.id.notIn : emptyNotIn

        return database.query('DELETE FROM leads WHERE NOT (id = ANY($1::text[]))', [ids])
      },
      upsert: async ({ where, create, update }) => {
        const data = { ...create, ...update }
        const result = await database.query(
          `
            INSERT INTO leads (
              id, status, name, phone, email, comment, product, plan, contact_method, source, form, created_at
            )
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, COALESCE($12::timestamptz, now()))
            ON CONFLICT (id) DO UPDATE SET
              status = EXCLUDED.status,
              name = EXCLUDED.name,
              phone = EXCLUDED.phone,
              email = EXCLUDED.email,
              comment = EXCLUDED.comment,
              product = EXCLUDED.product,
              plan = EXCLUDED.plan,
              contact_method = EXCLUDED.contact_method,
              source = EXCLUDED.source,
              form = EXCLUDED.form,
              updated_at = now()
            RETURNING *
          `,
          [
            where.id,
            data.status || 'new',
            data.name || '',
            data.phone || '',
            data.email || '',
            data.comment || '',
            data.product || '',
            data.plan || '',
            data.contactMethod || '',
            data.source || '',
            data.form || '',
            data.createdAt || null,
          ]
        )

        return toCamelLead(result.rows[0])
      },
    },
  }
}
