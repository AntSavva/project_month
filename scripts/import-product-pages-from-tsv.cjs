const fs = require('fs')
const path = require('path')

const [, , inputPath, siteDataPath = 'data/site.json'] = process.argv

if (!inputPath) {
  console.error('Usage: node scripts/import-product-pages-from-tsv.cjs <path-to-tsv> [site-json]')
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

const clean = (value) =>
  String(value || '')
    .replace(/\u00A0/g, ' ')
    .replace(/[ \t]+\n/g, '\n')
    .replace(/\n[ \t]+/g, '\n')
    .trim()

const compact = (value) => clean(value).replace(/\s+/g, ' ')
const defaultOptionsDescription =
  'Окрашиваем изделия в любые цвета по шкале RAL для идеального соответствия окружению или вашему вкусу'

const transliteration = {
  а: 'a',
  б: 'b',
  в: 'v',
  г: 'g',
  д: 'd',
  е: 'e',
  ё: 'e',
  ж: 'zh',
  з: 'z',
  и: 'i',
  й: 'j',
  к: 'k',
  л: 'l',
  м: 'm',
  н: 'n',
  о: 'o',
  п: 'p',
  р: 'r',
  с: 's',
  т: 't',
  у: 'u',
  ф: 'f',
  х: 'h',
  ц: 'c',
  ч: 'ch',
  ш: 'sh',
  щ: 'sch',
  ъ: '',
  ы: 'y',
 ь: '',
  э: 'e',
  ю: 'yu',
  я: 'ya',
}

const slugify = (title) => {
  const slug = compact(title)
    .toLowerCase()
    .split('')
    .map((char) => transliteration[char] ?? char)
    .join('')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

  return slug || `page-${Date.now()}`
}

const forcedSlugs = new Map([
  ['Наличники, подоконники', 'service'],
  ['Лестницы', 'ladder'],
])

const rowByLabel = (rows, label) => rows.find((row) => compact(row[0]) === label) || []
const cellAt = (row, column) => clean(row[column])

const groupValues = (row, start, end) => {
  const values = []

  for (let column = start; column < end; column += 1) {
    const value = cellAt(row, column)

    if (value) {
      values.push({ column, value })
    }
  }

  return values
}

const pairRows = (titlesRow, descriptionsRow, start, end) => {
  const titles = groupValues(titlesRow, start, end)

  return titles
    .map(({ column, value }) => ({
      title: value,
      description: cellAt(descriptionsRow, column),
    }))
    .filter((item) => item.title)
}

const buildMaterials = (row, start, end) =>
  groupValues(row, start, end).map(({ value }) => ({
    title: value,
    image: '',
  }))

const buildPlans = (titlesRow, conditionsRow, start, end) =>
  groupValues(titlesRow, start, end)
    .map(({ column, value }) => ({
      title: value,
      items: cellAt(conditionsRow, column)
        .split(/\n+/)
        .map((line) => compact(line))
        .filter(Boolean),
    }))
    .filter((item) => item.title)

const buildFaq = (questionsRow, answersRow, start, end) =>
  groupValues(questionsRow, start, end)
    .map(({ column, value }) => ({
      question: value,
      answer: cellAt(answersRow, column),
    }))
    .filter((item) => item.question || item.answer)

const createId = (type) =>
  `${type}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`

const tsvText = fs.readFileSync(inputPath, 'utf8').replace(/^\uFEFF/, '')
const rows = parseTSV(tsvText)
const firstRow = rows[0] || []
const pageStarts = firstRow
  .map((cell, column) => ({ column, title: clean(cell) }))
  .filter(({ column, title }) => column > 0 && title)
  .map((page, index, pages) => ({
    ...page,
    end: pages[index + 1]?.column || Math.max(...rows.map((row) => row.length)),
  }))

const sitePath = path.resolve(siteDataPath)
const site = JSON.parse(fs.readFileSync(sitePath, 'utf8').replace(/^\uFEFF/, ''))
const pages = Array.isArray(site.pages) ? site.pages : []

const labels = {
  heroSubtitle: rowByLabel(rows, 'Надзагловок'),
  heroTitle: rowByLabel(rows, 'Заголовок'),
  includesTitles: rowByLabel(rows, 'Что входит в услугу / Список'),
  includesDescriptions: rowByLabel(rows, 'Что входит в услугу / Подпись'),
  materialsTitle: rowByLabel(rows, 'Материалы / Заголовок'),
  materialsItems: rowByLabel(rows, 'Материалы / Список'),
  benefitsTitle: rowByLabel(rows, 'Преимущества / заголовок'),
  benefitsDescription: rowByLabel(rows, 'Преимущества / Позаголовок'),
  benefitsItems: rowByLabel(rows, 'Преимущества / Список'),
  benefitsDescriptions: rowByLabel(rows, 'Преимущества / Подпись'),
  plansTitles: rowByLabel(rows, 'Варианты сотрудничества / Список'),
  plansConditions: rowByLabel(rows, 'Варианты сотрудничества / Условия'),
  optionsTitles: rowByLabel(rows, 'Дополнительные опции и возможности / Список'),
  optionsDescriptions: rowByLabel(rows, 'Дополнительные опции и возможности / Подпись'),
  faqQuestions: rowByLabel(rows, 'Частые вопросы / Вопросы'),
  faqAnswers: rowByLabel(rows, 'Частые вопросы /Ответы'),
}

const now = new Date().toISOString()
const importedPages = []
const globalPlans = buildPlans(labels.plansTitles, labels.plansConditions, 1, pageStarts[0]?.end || 1)
const globalMaterials = buildMaterials(labels.materialsItems, 1, pageStarts[0]?.end || 1)

for (const group of pageStarts) {
  const title = group.title
  const slug = forcedSlugs.get(title) || slugify(title)
  const heroTitle = cellAt(labels.heroTitle, group.column)
  const heroParts = heroTitle.split(/\s+(?=для |из |—)/i)
  const content = {
    hero: {
      subtitle: cellAt(labels.heroSubtitle, group.column),
      title: heroParts[0] || heroTitle || title,
      accent: heroParts.slice(1).join(' ') || '',
    },
    includes: {
      title: 'Что входит в услугу',
      description: '',
      items: pairRows(labels.includesTitles, labels.includesDescriptions, group.column, group.end),
    },
    materials: {
      title: cellAt(labels.materialsTitle, group.column) || 'Материалы',
      items: buildMaterials(labels.materialsItems, group.column, group.end),
    },
    colors: {
      title: 'Дополнительные опции и возможности',
      description: defaultOptionsDescription,
      items: pairRows(labels.optionsTitles, labels.optionsDescriptions, group.column, group.end),
    },
    benefits: {
      title: cellAt(labels.benefitsTitle, group.column) || 'Преимущества',
      description: cellAt(labels.benefitsDescription, group.column),
      items: pairRows(labels.benefitsItems, labels.benefitsDescriptions, group.column, group.end),
    },
    plans: {
      title: 'Варианты сотрудничества',
      items: buildPlans(labels.plansTitles, labels.plansConditions, group.column, group.end),
    },
    faq: {
      items: buildFaq(labels.faqQuestions, labels.faqAnswers, group.column, group.end),
    },
  }

  if (!content.plans.items.length && globalPlans.length) {
    content.plans.items = globalPlans
  }

  if (!content.materials.items.length && globalMaterials.length) {
    content.materials.items = globalMaterials
  }

  const existingIndex = pages.findIndex(
    (page) => page.type === 'product' && (page.slug === slug || page.title === title)
  )
  const existingPage = existingIndex >= 0 ? pages[existingIndex] : null
  const page = {
    ...(existingPage || {}),
    id: existingPage?.id || createId('product'),
    type: 'product',
    title,
    slug,
    menuDescription: existingPage?.menuDescription || heroTitle || title,
    seoTitle: existingPage?.seoTitle || title,
    seoDescription: existingPage?.seoDescription || heroTitle || title,
    status: existingPage?.status || 'published',
    cover: existingPage?.cover || '',
    content,
    updatedAt: now,
  }

  if (existingIndex >= 0) {
    pages[existingIndex] = page
  } else {
    pages.push(page)
  }

  importedPages.push({ title, slug, created: existingIndex < 0 })
}

const nextSite = {
  ...site,
  pages,
}

fs.writeFileSync(sitePath, `${JSON.stringify(nextSite, null, 2)}\n`, 'utf8')

console.log(`Imported product pages: ${importedPages.length}`)
for (const page of importedPages) {
  console.log(`${page.created ? 'created' : 'updated'}: ${page.title} -> /${page.slug}`)
}
