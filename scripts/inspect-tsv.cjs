const fs = require("fs")

const [, , inputPath, limitValue = "80"] = process.argv

if (!inputPath) {
  console.error("Usage: node scripts/inspect-tsv.cjs <path-to-tsv> [row-limit]")
  process.exit(1)
}

function parseTSV(text) {
  const rows = []
  let row = []
  let cell = ""
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
    } else if (char === "\t" && !quoted) {
      row.push(cell)
      cell = ""
    } else if ((char === "\n" || char === "\r") && !quoted) {
      if (char === "\r" && next === "\n") {
        index += 1
      }
      row.push(cell)
      rows.push(row)
      row = []
      cell = ""
    } else {
      cell += char
    }
  }

  if (cell.length || row.length) {
    row.push(cell)
    rows.push(row)
  }

  return rows
}

const text = fs.readFileSync(inputPath, "utf8").replace(/^\uFEFF/, "")
const rows = parseTSV(text).filter((row) => row.some((cell) => String(cell).trim()))
const limit = Number(limitValue) || 80

console.log("logical rows", rows.length)
console.log("max cols", Math.max(...rows.map((row) => row.length)))

rows.slice(0, limit).forEach((row, index) => {
  const nonEmpty = row
    .map((cell, column) => [column, String(cell).trim().replace(/\s+/g, " ")])
    .filter(([, value]) => value)
    .slice(0, 18)

  console.log(`#${index} ${row[0] || ""}`)
  console.log(nonEmpty.map(([column, value]) => `${column}:${value.slice(0, 90)}`).join(" | "))
})
