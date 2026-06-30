import { readFile } from 'fs/promises'
import path from 'path'
import { fileURLToPath } from 'url'

import { writeLeads, writeSiteData } from '../src/lib/adminStorage.js'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const rootDir = path.join(__dirname, '..')

const readJson = async (filePath, fallback) => {
  try {
    const content = await readFile(filePath, 'utf8')

    return JSON.parse(content.replace(/^\uFEFF/, ''))
  } catch (error) {
    if (error.code === 'ENOENT') {
      return fallback
    }

    throw error
  }
}

const main = async () => {
  if (!process.env.DATABASE_URL) {
    throw new Error('DATABASE_URL is required for seeding PostgreSQL.')
  }

  const siteData = await readJson(path.join(rootDir, 'data', 'site.json'), null)
  const leads = await readJson(path.join(rootDir, 'data', 'leads.json'), [])

  if (!siteData) {
    throw new Error('data/site.json was not found.')
  }

  await writeSiteData(siteData)
  await writeLeads(leads)

  console.log(`Seeded ${siteData.pages?.length || 0} pages, ${siteData.reviews?.length || 0} reviews.`)
  console.log(`Seeded ${leads.length} leads.`)
}

main().catch((error) => {
  console.error(error)
  process.exit(1)
})
