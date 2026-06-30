import { readFile } from 'fs/promises'
import path from 'path'
import { fileURLToPath } from 'url'

import pg from 'pg'

const { Pool } = pg
const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)
const rootDir = path.join(__dirname, '..')

const main = async () => {
  if (!process.env.DATABASE_URL) {
    throw new Error('DATABASE_URL is required to initialize PostgreSQL.')
  }

  const pool = new Pool({ connectionString: process.env.DATABASE_URL })
  const schema = await readFile(path.join(rootDir, 'database', 'schema.sql'), 'utf8')

  await pool.query(schema)
  await pool.end()

  console.log('PostgreSQL schema is ready.')
}

main().catch((error) => {
  console.error(error)
  process.exit(1)
})
