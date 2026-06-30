const fs = require('fs')

const [, , inputPath] = process.argv

if (!inputPath) {
  console.error('Usage: node scripts/summarize-product-tsv.cjs <path-to-tsv>')
  process.exit(1)
}

function parseTSV(text) {
  const rows = []
  let row = []
  let cell = ''
  let quoted = false

  for (let index = 0; index < text.length; index += 1) {
    const char = text[index]
    const next = text[index + 1]

    if (char === '"') {
      if (quoted && next === '"') {
        cell += '"'
        index += 1
      } else {
        quoted = !quoted
      }
    } else if (char === '\t' && !quoted) {
      row.push(cell)
      cell = ''
    } else if ((char === '\n' || char === '\r') && !quoted) {
      if (char === '\r' && next === '\n') {
        index += 1
      }
      row.push(cell)
      rows.push(row)
      row = []
      cell = ''
    } else {
      cell += char
    }
  }

  if (cell.length || row.length) {
    row.push(cell)
    rows.push(row)
  }

  return rows.filter((rowItem) => rowItem.some((cellItem) => String(cellItem).trim()))
}

const text = fs.readFileSync(inputPath, 'utf8').replace(/^\uFEFF/, '')
const rows = parseTSV(text)
const firstRow = rows[0] || []

const pageStarts = firstRow
  .map((cell, column) => ({ column, title: String(cell).trim() }))
  .filter(({ column, title }) => column > 0 && title)

console.log(`logical rows: ${rows.length}`)
console.log(`max cols: ${Math.max(...rows.map((row) => row.length))}`)
console.log('pages:')
for (const { column, title } of pageStarts) {
  console.log(`  ${column}: ${title}`)
}

console.log('\nrow labels:')
rows.forEach((row, index) => {
  const label = String(row[0] || '').trim().replace(/\s+/g, ' ')
  const filledColumns = row
    .map((cell, column) => ({ column, value: String(cell).trim() }))
    .filter(({ column, value }) => column > 0 && value)
    .map(({ column }) => column)

  const preview = row
    .map((cell, column) => ({ column, value: String(cell).trim().replace(/\s+/g, ' ') }))
    .filter(({ column, value }) => column > 0 && value)
    .slice(0, 5)
    .map(({ column, value }) => `${column}:${value.slice(0, 50)}`)
    .join(' | ')

  console.log(`#${index} ${label || '(empty)'} | filled=${filledColumns.length} | ${preview}`)
})
