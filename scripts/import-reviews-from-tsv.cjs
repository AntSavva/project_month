const fs = require("fs")
const path = require("path")

const [, , inputPath] = process.argv

if (!inputPath) {
  console.error("Usage: node scripts/import-reviews-from-tsv.js <path-to-tsv>")
  process.exit(1)
}

const sitePath = path.join(process.cwd(), "data", "site.json")
const quote = '"'

function parseTSV(text) {
  const rows = []
  let row = []
  let cell = ""
  let quoted = false

  for (let index = 0; index < text.length; index += 1) {
    const char = text[index]
    const next = text[index + 1]

    if (char === quote) {
      if (quoted && next === quote) {
        cell += quote
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

  if (cell.length > 0 || row.length > 0) {
    row.push(cell)
    rows.push(row)
  }

  return rows
}

function clean(value = "") {
  return String(value).replace(/\r/g, "").trim()
}

function slugify(value) {
  return String(value)
    .toLowerCase()
    .normalize("NFKD")
    .replace(/[^\wа-яё]+/gi, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 48)
}

const rawInput = fs.readFileSync(inputPath, "utf8").replace(/^\uFEFF/, "")
const rows = parseTSV(rawInput).filter((row) => row.some((cell) => clean(cell)))
const [, ...reviewRows] = rows

const importedReviews = reviewRows
  .map((row, index) => {
    const [product, author, avatar, date, text] = row
    const reviewText = clean(text)
    const reviewAuthor = clean(author)

    if (!reviewText || !reviewAuthor) {
      return null
    }

    return {
      id: `review-import-${slugify(reviewAuthor) || "client"}-${Date.now()}-${index}`,
      author: reviewAuthor,
      date: clean(date),
      text: reviewText,
      avatar: clean(avatar),
      category: clean(product) || "general",
      status: "published",
      order: 0,
    }
  })
  .filter(Boolean)

const siteRaw = fs.readFileSync(sitePath, "utf8").replace(/^\uFEFF/, "")
const siteData = JSON.parse(siteRaw)
const existingReviews = Array.isArray(siteData.reviews) ? siteData.reviews : []
const existingKeys = new Set(
  existingReviews.map((review) => `${clean(review.author)}|${clean(review.date)}|${clean(review.text)}`),
)

const maxOrder = existingReviews.reduce((max, review) => Math.max(max, Number(review.order) || 0), 0)
const newReviews = importedReviews
  .filter((review) => !existingKeys.has(`${review.author}|${review.date}|${review.text}`))
  .map((review, index, list) => ({
    ...review,
    order: maxOrder + list.length - index,
  }))

siteData.reviews = [...newReviews, ...existingReviews]

fs.writeFileSync(sitePath, `${JSON.stringify(siteData, null, 2)}\n`, "utf8")

console.log(`Parsed reviews: ${importedReviews.length}`)
console.log(`Added reviews: ${newReviews.length}`)
console.log(`Total reviews: ${siteData.reviews.length}`)
