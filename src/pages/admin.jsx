import { useEffect, useMemo, useState } from 'react'

const emptySettings = {
  phone: '',
  email: '',
  address: '',
  workingHours: '',
  legalInfo: '',
  socials: {
    vk: '',
    telegram: '',
    youtube: '',
    max: '',
  },
}

const emptyPage = {
  type: 'product',
  title: '',
  slug: '',
  menuDescription: '',
  seoTitle: '',
  seoDescription: '',
  status: 'draft',
  cover: '',
}

const emptyDocumentPageForm = {
  h1: '',
  text: '',
}

const emptyReview = {
  author: '',
  date: '',
  text: '',
  avatar: '',
  category: 'all',
  order: 0,
  status: 'published',
}

const emptyProductPageForm = {
  heroSubtitle: '',
  heroTitle: '',
  heroAccent: '',
  includesTitle: '',
  includesDescription: '',
  includesItems: [],
  materialsTitle: '',
  materialsItems: '',
  colorsTitle: '',
  colorsDescription: '',
  colorsItems: [],
  benefitsTitle: '',
  benefitsDescription: '',
  benefitsItems: [],
  plansTitle: '',
  plansItems: [],
  faqItems: [],
}

const emptyInteriorPageForm = {
  heroSubtitle: '',
  heroTitle: '',
  heroAccent: '',
  includesTitle: '',
  includesItems: '',
  roomSolutionsTitle: '',
  roomSolutionsItems: '',
  materialsTitle: '',
  materialsItems: '',
  advantagesTitle: '',
  advantagesDescription: '',
  advantagesItems: '',
  plansTitle: '',
  plansItems: '',
  faqItems: [],
}

const formatRows = (items = []) =>
  items
    .map(({ title, description, icon }) =>
      [title || '', description || '', icon || ''].filter(Boolean).join(' | ')
    )
    .join('\n')

const parseRows = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)
    .map((line) => {
      const [title, description = '', icon = ''] = line.split('|')

      return {
        title: title.trim(),
        description: description.trim(),
        icon: icon.trim(),
      }
    })
    .filter((item) => item.title)

const formatList = (items = []) => items.join('\n')

const parseList = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)

const normalizeMaterial = (item) => {
  if (typeof item === 'string') {
    return { title: item, image: '' }
  }

  return {
    title: item?.title || '',
    image: item?.image || '',
  }
}

const formatMaterials = (items = []) =>
  items
    .map(normalizeMaterial)
    .map(({ title, image }) => `${title || ''} | ${image || ''}`)
    .join('\n')

const parseMaterials = (value) =>
  value
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean)
    .map((line) => {
      const [title, ...imageParts] = line.split('|')

      return {
        title: title.trim(),
        image: imageParts.join('|').trim(),
      }
    })
    .filter((item) => item.title)

const formatPlans = (items = []) =>
  items
    .map((plan) => [plan.title, ...(plan.items || [])].filter(Boolean).join('\n'))
    .join('\n\n')

const parsePlans = (value) =>
  value
    .split(/\n\s*\n/)
    .map((block) => block.split('\n').map((line) => line.trim()).filter(Boolean))
    .filter((lines) => lines.length)
    .map(([title, ...items]) => ({ title, items }))

const cardIconOptions = [
  { value: '', label: 'Авто' },
  { value: 'box', label: 'Бокс' },
  { value: 'car', label: 'Грузовик' },
  { value: 'check', label: 'Галочка' },
  { value: 'color', label: 'Палитра' },
  { value: 'detail', label: 'Шестеренка' },
  { value: 'level', label: 'Уровень' },
  { value: 'lines', label: 'Стрелки по кругу' },
  { value: 'loop', label: 'Лупа' },
  { value: 'machine', label: 'Станок' },
  { value: 'medal', label: 'Медаль' },
  { value: 'person', label: 'Пользователь' },
  { value: 'personWithStar', label: 'Пользователь со звездой' },
  { value: 'roulette', label: 'Рулетка' },
  { value: 'rubles', label: 'Ценник' },
  { value: 'rulerAndPen', label: 'Карандаш и линейка' },
  { value: 'shield', label: 'Щит с медалью' },
  { value: 'shieldPlain', label: 'Щит' },
  { value: 'star', label: 'Звезда' },
  { value: 'target', label: 'Мишень' },
  { value: 'tree', label: 'Дерево' },
  { value: 'woods', label: 'Стопка досок' },
]

const normalizeCardItems = (items = []) => {
  if (typeof items === 'string') {
    return parseRows(items).map((item) => ({ ...item, icon: '' }))
  }

  if (!Array.isArray(items)) {
    return []
  }

  return items.map((item) => ({
    title: item?.title || '',
    description: item?.description || '',
    icon: item?.icon || '',
  }))
}

const normalizePlanItems = (items = []) => {
  if (typeof items === 'string') {
    return parsePlans(items).map((item) => ({ ...item, icon: '' }))
  }

  if (!Array.isArray(items)) {
    return []
  }

  return items.map((item) => ({
    title: item?.title || '',
    icon: item?.icon || '',
    items: Array.isArray(item?.items) ? item.items : parseList(item?.items || ''),
  }))
}

const normalizeFaqItems = (items = []) => {
  if (!Array.isArray(items)) {
    return []
  }

  return items.map((item) => ({
    question: item?.question || '',
    answer: item?.answer || '',
  }))
}

const toProductPageForm = (productPage = {}) => ({
  heroSubtitle: productPage.hero?.subtitle || '',
  heroTitle: productPage.hero?.title || '',
  heroAccent: productPage.hero?.accent || '',
  includesTitle: productPage.includes?.title || '',
  includesDescription: productPage.includes?.description || '',
  includesItems: normalizeCardItems(productPage.includes?.items),
  materialsTitle: productPage.materials?.title || '',
  materialsItems: formatMaterials(productPage.materials?.items),
  colorsTitle: productPage.colors?.title || '',
  colorsDescription: productPage.colors?.description || '',
  colorsItems: normalizeCardItems(productPage.colors?.items),
  benefitsTitle: productPage.benefits?.title || '',
  benefitsDescription: productPage.benefits?.description || '',
  benefitsItems: normalizeCardItems(productPage.benefits?.items),
  plansTitle: productPage.plans?.title || '',
  plansItems: normalizePlanItems(productPage.plans?.items),
  faqItems: normalizeFaqItems(productPage.faq?.items),
})

const fromProductPageForm = (form) => ({
  hero: {
    subtitle: form.heroSubtitle,
    title: form.heroTitle,
    accent: form.heroAccent,
  },
  includes: {
    title: form.includesTitle,
    description: form.includesDescription,
    items: normalizeCardItems(form.includesItems).filter(
      (item) => item.title.trim() || item.description.trim()
    ),
  },
  materials: {
    title: form.materialsTitle,
    items: parseMaterials(form.materialsItems),
  },
  colors: {
    title: form.colorsTitle,
    description: form.colorsDescription,
    items: normalizeCardItems(form.colorsItems).filter(
      (item) => item.title.trim() || item.description.trim()
    ),
  },
  benefits: {
    title: form.benefitsTitle,
    description: form.benefitsDescription,
    items: normalizeCardItems(form.benefitsItems).filter(
      (item) => item.title.trim() || item.description.trim()
    ),
  },
  plans: {
    title: form.plansTitle,
    items: normalizePlanItems(form.plansItems).filter(
      (item) => item.title.trim() || item.items.some((planItem) => planItem.trim())
    ),
  },
  faq: {
    items: normalizeFaqItems(form.faqItems).filter(
      (item) => item.question.trim() || item.answer.trim()
    ),
  },
})

const toInteriorPageForm = (interiorPage = {}) => ({
  heroSubtitle: interiorPage.hero?.subtitle || '',
  heroTitle: interiorPage.hero?.title || '',
  heroAccent: interiorPage.hero?.accent || '',
  includesTitle: interiorPage.includes?.title || '',
  includesItems: formatRows(interiorPage.includes?.items),
  roomSolutionsTitle: interiorPage.roomSolutions?.title || '',
  roomSolutionsItems: formatPlans(interiorPage.roomSolutions?.items),
  materialsTitle: interiorPage.materials?.title || '',
  materialsItems: formatMaterials(interiorPage.materials?.items),
  advantagesTitle: interiorPage.advantages?.title || '',
  advantagesDescription: interiorPage.advantages?.description || '',
  advantagesItems: formatRows(interiorPage.advantages?.items),
  plansTitle: interiorPage.plans?.title || '',
  plansItems: formatPlans(interiorPage.plans?.items),
  faqItems: normalizeFaqItems(interiorPage.faq?.items),
})

const fromInteriorPageForm = (form) => ({
  hero: {
    subtitle: form.heroSubtitle,
    title: form.heroTitle,
    accent: form.heroAccent,
  },
  includes: {
    title: form.includesTitle,
    items: parseRows(form.includesItems),
  },
  roomSolutions: {
    title: form.roomSolutionsTitle,
    items: parsePlans(form.roomSolutionsItems),
  },
  materials: {
    title: form.materialsTitle,
    items: parseMaterials(form.materialsItems),
  },
  advantages: {
    title: form.advantagesTitle,
    description: form.advantagesDescription,
    items: parseRows(form.advantagesItems),
  },
  plans: {
    title: form.plansTitle,
    items: parsePlans(form.plansItems),
  },
  faq: {
    items: normalizeFaqItems(form.faqItems).filter(
      (item) => item.question.trim() || item.answer.trim()
    ),
  },
})

const toDocumentPageForm = (documentPage = {}) => ({
  h1: documentPage.h1 || '',
  text: documentPage.text || '',
})

const fromDocumentPageForm = (form) => ({
  h1: form.h1,
  text: form.text,
})

let refreshRequest = null

const refreshAdminSession = async () => {
  if (!refreshRequest) {
    refreshRequest = fetch('/api/admin/refresh', {
      method: 'POST',
      credentials: 'same-origin',
    }).finally(() => {
      refreshRequest = null
    })
  }

  return refreshRequest
}

const request = async (url, options = {}, retryOnUnauthorized = true) => {
  const response = await fetch(url, {
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
    ...options,
  })
  const data = await response.json()

  if (
    response.status === 401 &&
    retryOnUnauthorized &&
    !url.includes('/api/admin/login') &&
    !url.includes('/api/admin/logout') &&
    !url.includes('/api/admin/refresh')
  ) {
    const refreshResponse = await refreshAdminSession()

    if (refreshResponse.ok) {
      return request(url, options, false)
    }
  }

  if (!response.ok) {
    throw new Error(data.message || 'Ошибка запроса')
  }

  return data
}

const readFileAsDataUrl = (file) =>
  new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.addEventListener('load', () => resolve(reader.result))
    reader.addEventListener('error', () => reject(reader.error))
    reader.readAsDataURL(file)
  })

const requiredPageFields = [
  ['title', 'Название'],
  ['slug', 'URL / slug'],
  ['menuDescription', 'Описание в меню'],
  ['seoTitle', 'SEO title'],
  ['seoDescription', 'SEO description'],
  ['cover', 'Обложка'],
]

const getMissingPageFields = (page) =>
  requiredPageFields
    .filter(([field]) => {
      if (page.type === 'document' && (field === 'menuDescription' || field === 'cover')) {
        return false
      }

      return !page[field]?.toString().trim()
    })
    .map(([, label]) => label)

const formatDate = (value) => {
  if (!value) {
    return ''
  }

  return new Intl.DateTimeFormat('ru-RU', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

const getLeadPhone = (value) => {
  const phone = String(value || '').trim()

  return ['phone', 'tel', 'telephone'].includes(phone.toLowerCase()) ? '' : phone
}

const renderLeadPhone = (value) => {
  const phone = getLeadPhone(value)

  return phone ? (
    <a href={`tel:${phone}`}>{phone}</a>
  ) : (
    <span>Телефон не указан</span>
  )
}

const AdminPage = () => {
  const [isReady, setIsReady] = useState(false)
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [password, setPassword] = useState('')
  const [activeTab, setActiveTab] = useState('pages')
  const [settings, setSettings] = useState(emptySettings)
  const [productPageForm, setProductPageForm] = useState(emptyProductPageForm)
  const [interiorPageForm, setInteriorPageForm] = useState(emptyInteriorPageForm)
  const [documentPageForm, setDocumentPageForm] = useState(emptyDocumentPageForm)
  const [editingProductPageId, setEditingProductPageId] = useState('')
  const [editingInteriorPageId, setEditingInteriorPageId] = useState('')
  const [editingDocumentPageId, setEditingDocumentPageId] = useState('')
  const [selectedPageId, setSelectedPageId] = useState('')
  const [pages, setPages] = useState([])
  const [leads, setLeads] = useState([])
  const [reviews, setReviews] = useState([])
  const [newReview, setNewReview] = useState(emptyReview)
  const [newPage, setNewPage] = useState(emptyPage)
  const [expandedPageIds, setExpandedPageIds] = useState([])
  const [message, setMessage] = useState('')

  const groupedPages = useMemo(
    () => ({
      product: pages.filter((page) => page.type === 'product'),
      interior: pages.filter((page) => page.type === 'interior'),
      document: pages.filter((page) => page.type === 'document'),
    }),
    [pages]
  )

  const selectedPage = useMemo(
    () => pages.find((page) => page.id === selectedPageId) || null,
    [pages, selectedPageId]
  )

  const loadAdminData = async () => {
    const [settingsData, pagesData, leadsData, reviewsData] = await Promise.all([
      request('/api/admin/settings'),
      request('/api/admin/pages'),
      request('/api/admin/leads'),
      request('/api/admin/reviews'),
    ])
    const productPages = pagesData.pages.filter((page) => page.type === 'product')
    const interiorPages = pagesData.pages.filter((page) => page.type === 'interior')
    const documentPages = pagesData.pages.filter((page) => page.type === 'document')
    const selectedPage =
      productPages.find((page) => page.slug === 'service') || productPages[0] || null
    const selectedInteriorPage =
      interiorPages.find((page) => page.slug === 'interior') || interiorPages[0] || null
    const selectedDocumentPage = documentPages[0] || null

    setSettings(settingsData.settings)
    setEditingProductPageId(selectedPage?.id || '')
    setEditingInteriorPageId(selectedInteriorPage?.id || '')
    setEditingDocumentPageId(selectedDocumentPage?.id || '')
    setProductPageForm(toProductPageForm(selectedPage?.content))
    setInteriorPageForm(toInteriorPageForm(selectedInteriorPage?.content))
    setDocumentPageForm(toDocumentPageForm(selectedDocumentPage?.content))
    setPages(pagesData.pages)
    setLeads(leadsData.leads)
    setReviews(reviewsData.reviews)
  }

  useEffect(() => {
    const init = async () => {
      try {
        const data = await request('/api/admin/me')
        setIsAuthenticated(data.isAuthenticated)

        if (data.isAuthenticated) {
          await loadAdminData()
        }
      } finally {
        setIsReady(true)
      }
    }

    init()
  }, [])

  const updateProductPageField = (field, value) => {
    setProductPageForm((currentForm) => ({ ...currentForm, [field]: value }))
  }

  const updateInteriorPageField = (field, value) => {
    setInteriorPageForm((currentForm) => ({ ...currentForm, [field]: value }))
  }

  const updateDocumentPageField = (field, value) => {
    setDocumentPageForm((currentForm) => ({ ...currentForm, [field]: value }))
  }

  const onProductPageSelect = (pageId) => {
    const page = pages.find((item) => item.id === pageId)

    setEditingProductPageId(pageId)
    setSelectedPageId(pageId)
    setProductPageForm(toProductPageForm(page?.content))
  }

  const onInteriorPageSelect = (pageId) => {
    const page = pages.find((item) => item.id === pageId)

    setEditingInteriorPageId(pageId)
    setSelectedPageId(pageId)
    setInteriorPageForm(toInteriorPageForm(page?.content))
  }

  const onDocumentPageSelect = (pageId) => {
    const page = pages.find((item) => item.id === pageId)

    setEditingDocumentPageId(pageId)
    setSelectedPageId(pageId)
    setDocumentPageForm(toDocumentPageForm(page?.content))
  }

  const onOpenPageEditor = (page) => {
    setSelectedPageId(page.id)
    setActiveTab(
      page.type === 'product'
        ? 'productPage'
        : page.type === 'interior'
          ? 'interiorPage'
          : 'documentPage'
    )

    if (page.type === 'product') {
      setEditingProductPageId(page.id)
      setProductPageForm(toProductPageForm(page.content))
    } else if (page.type === 'interior') {
      setEditingInteriorPageId(page.id)
      setInteriorPageForm(toInteriorPageForm(page.content))
    } else if (page.type === 'document') {
      setEditingDocumentPageId(page.id)
      setDocumentPageForm(toDocumentPageForm(page.content))
    }
  }

  const updatePageDraft = (pageId, patch) => {
    setPages((currentPages) =>
      currentPages.map((item) => (item.id === pageId ? { ...item, ...patch } : item))
    )
  }

  const togglePageExpanded = (pageId) => {
    setExpandedPageIds((currentIds) =>
      currentIds.includes(pageId)
        ? currentIds.filter((id) => id !== pageId)
        : [...currentIds, pageId]
    )
  }

  const onLoginSubmit = async (event) => {
    event.preventDefault()
    setMessage('')

    try {
      await request('/api/admin/login', {
        method: 'POST',
        body: JSON.stringify({ password }),
      })
      setIsAuthenticated(true)
      await loadAdminData()
    } catch (error) {
      setMessage(error.message)
    }
  }

  const onLogout = async () => {
    await request('/api/admin/logout', { method: 'POST' })
    setIsAuthenticated(false)
    setPassword('')
  }

  const onSettingsSubmit = async (event) => {
    event.preventDefault()
    setMessage('')

    const data = await request('/api/admin/settings', {
      method: 'PUT',
      body: JSON.stringify(settings),
    })

    setSettings(data.settings)
    setMessage('Настройки сохранены')
  }

  const onProductPageSubmit = async (event) => {
    event.preventDefault()
    setMessage('')
    const page = pages.find((item) => item.id === editingProductPageId)

    if (!page) {
      setMessage('Выберите страницу продукции')
      return
    }

    const data = await request('/api/admin/pages', {
      method: 'PUT',
      body: JSON.stringify({
        ...page,
        content: fromProductPageForm(productPageForm),
      }),
    })

    setPages((currentPages) =>
      currentPages.map((item) => (item.id === data.page.id ? data.page : item))
    )
    setProductPageForm(toProductPageForm(data.page.content))
    setMessage('Контент страницы сохранен')
  }

  const onInteriorPageSubmit = async (event) => {
    event.preventDefault()
    setMessage('')
    const page = pages.find((item) => item.id === editingInteriorPageId)

    if (!page) {
      setMessage('Выберите страницу отделки интерьера')
      return
    }

    const data = await request('/api/admin/pages', {
      method: 'PUT',
      body: JSON.stringify({
        ...page,
        content: fromInteriorPageForm(interiorPageForm),
      }),
    })

    setPages((currentPages) =>
      currentPages.map((item) => (item.id === data.page.id ? data.page : item))
    )
    setInteriorPageForm(toInteriorPageForm(data.page.content))
    setMessage('Контент страницы сохранен')
  }

  const onDocumentPageSubmit = async (event) => {
    event.preventDefault()
    setMessage('')
    const page = pages.find((item) => item.id === editingDocumentPageId)

    if (!page) {
      setMessage('Выберите страницу документа')
      return
    }

    const data = await request('/api/admin/pages', {
      method: 'PUT',
      body: JSON.stringify({
        ...page,
        content: fromDocumentPageForm(documentPageForm),
      }),
    })

    setPages((currentPages) =>
      currentPages.map((item) => (item.id === data.page.id ? data.page : item))
    )
    setDocumentPageForm(toDocumentPageForm(data.page.content))
    setMessage('Документ сохранен')
  }

  const onCreatePage = async (event) => {
    event.preventDefault()
    setMessage('')

    const missingFields = getMissingPageFields(newPage)

    if (missingFields.length) {
      setMessage(`Заполните поля: ${missingFields.join(', ')}`)
      return
    }

    const data = await request('/api/admin/pages', {
      method: 'POST',
      body: JSON.stringify(newPage),
    })

    setPages((currentPages) => [data.page, ...currentPages])
    setNewPage(emptyPage)
    onOpenPageEditor(data.page)
    setMessage('Страница добавлена')
  }

  const onUpdatePage = async (page) => {
    const data = await request('/api/admin/pages', {
      method: 'PUT',
      body: JSON.stringify(page),
    })

    setPages((currentPages) =>
      currentPages.map((item) => (item.id === page.id ? data.page : item))
    )
    setMessage('Страница сохранена')
  }

  const onDeletePage = async (page) => {
    const title = page.title || page.slug || 'страницу'

    if (!window.confirm(`Удалить "${title}"?`)) {
      return
    }

    await request(`/api/admin/pages?id=${encodeURIComponent(page.id)}`, {
      method: 'DELETE',
    })

    setPages((currentPages) => currentPages.filter((item) => item.id !== page.id))

    if (selectedPageId === page.id) {
      setSelectedPageId('')
      setActiveTab('pages')
    }

    if (editingProductPageId === page.id) {
      const nextProductPage = pages.find(
        (item) => item.id !== page.id && item.type === 'product'
      )
      setEditingProductPageId(nextProductPage?.id || '')
      setProductPageForm(toProductPageForm(nextProductPage?.content))
    }

    if (editingInteriorPageId === page.id) {
      const nextInteriorPage = pages.find(
        (item) => item.id !== page.id && item.type === 'interior'
      )
      setEditingInteriorPageId(nextInteriorPage?.id || '')
      setInteriorPageForm(toInteriorPageForm(nextInteriorPage?.content))
    }

    if (editingDocumentPageId === page.id) {
      const nextDocumentPage = pages.find(
        (item) => item.id !== page.id && item.type === 'document'
      )
      setEditingDocumentPageId(nextDocumentPage?.id || '')
      setDocumentPageForm(toDocumentPageForm(nextDocumentPage?.content))
    }

    setMessage('Страница удалена')
  }

  const uploadCoverFile = async (file) => {
    const dataUrl = await readFileAsDataUrl(file)
    const uploadData = await request('/api/admin/upload', {
      method: 'POST',
      body: JSON.stringify({
        fileName: file.name,
        dataUrl,
      }),
    })

    return uploadData.url
  }

  const onNewPageCoverUpload = async (file) => {
    if (!file) {
      return
    }

    setMessage('')
    const cover = await uploadCoverFile(file)
    setNewPage((currentPage) => ({ ...currentPage, cover }))
    setMessage('Обложка загружена')
  }

  const onCoverUpload = async (page, file) => {
    if (!file) {
      return
    }

    setMessage('')

    const cover = await uploadCoverFile(file)
    const data = await request('/api/admin/pages', {
      method: 'PUT',
      body: JSON.stringify({ ...page, cover }),
    })

    setPages((currentPages) =>
      currentPages.map((item) => (item.id === data.page.id ? data.page : item))
    )
    setMessage('Обложка загружена')
  }

  const onNewReviewAvatarUpload = async (file) => {
    if (!file) {
      return
    }

    setMessage('')
    const avatar = await uploadCoverFile(file)
    setNewReview((currentReview) => ({ ...currentReview, avatar }))
    setMessage('Аватарка отзыва загружена')
  }

  const onReviewAvatarUpload = async (review, file) => {
    if (!file) {
      return
    }

    setMessage('')
    const avatar = await uploadCoverFile(file)
    const data = await request('/api/admin/reviews', {
      method: 'PUT',
      body: JSON.stringify({ ...review, avatar }),
    })

    setReviews(data.reviews)
    setMessage('Аватарка отзыва загружена')
  }

  const onCreateReview = async (event) => {
    event.preventDefault()
    setMessage('')

    const data = await request('/api/admin/reviews', {
      method: 'POST',
      body: JSON.stringify(newReview),
    })

    setReviews(data.reviews)
    setNewReview(emptyReview)
    setMessage('Отзыв добавлен')
  }

  const onUpdateReview = async (review) => {
    setMessage('')

    const data = await request('/api/admin/reviews', {
      method: 'PUT',
      body: JSON.stringify(review),
    })

    setReviews(data.reviews)
    setMessage('Отзыв сохранен')
  }

  const onDeleteReview = async (review) => {
    if (!window.confirm(`Удалить отзыв: ${review.author || 'без имени'}?`)) {
      return
    }

    setMessage('')
    const data = await request(`/api/admin/reviews?id=${encodeURIComponent(review.id)}`, {
      method: 'DELETE',
    })

    setReviews(data.reviews)
    setMessage('Отзыв удален')
  }

  const updateReviewDraft = (reviewId, patch) => {
    setReviews((currentReviews) =>
      currentReviews.map((review) =>
        review.id === reviewId ? { ...review, ...patch } : review
      )
    )
  }

  const getProductMaterials = () => parseMaterials(productPageForm.materialsItems)

  const setProductMaterials = (materials) => {
    updateProductPageField('materialsItems', formatMaterials(materials))
  }

  const onMaterialChange = (index, patch) => {
    const materials = getProductMaterials()
    const nextMaterials = materials.map((material, materialIndex) =>
      materialIndex === index ? { ...material, ...patch } : material
    )

    setProductMaterials(nextMaterials)
  }

  const onAddMaterial = () => {
    setProductMaterials([...getProductMaterials(), { title: '', image: '' }])
  }

  const onRemoveMaterial = (index) => {
    setProductMaterials(getProductMaterials().filter((_, materialIndex) => materialIndex !== index))
  }

  const onMaterialImageUpload = async (index, file) => {
    if (!file) {
      return
    }

    setMessage('')
    const image = await uploadCoverFile(file)
    onMaterialChange(index, { image })
    setMessage('Фотография материала загружена')
  }

  const getInteriorMaterials = () => parseMaterials(interiorPageForm.materialsItems)

  const setInteriorMaterials = (materials) => {
    updateInteriorPageField('materialsItems', formatMaterials(materials))
  }

  const onInteriorMaterialChange = (index, patch) => {
    const materials = getInteriorMaterials()
    const nextMaterials = materials.map((material, materialIndex) =>
      materialIndex === index ? { ...material, ...patch } : material
    )

    setInteriorMaterials(nextMaterials)
  }

  const onAddInteriorMaterial = () => {
    setInteriorMaterials([...getInteriorMaterials(), { title: '', image: '' }])
  }

  const onRemoveInteriorMaterial = (index) => {
    setInteriorMaterials(
      getInteriorMaterials().filter((_, materialIndex) => materialIndex !== index)
    )
  }

  const onInteriorMaterialImageUpload = async (index, file) => {
    if (!file) {
      return
    }

    setMessage('')
    const image = await uploadCoverFile(file)
    onInteriorMaterialChange(index, { image })
    setMessage('Фотография материала загружена')
  }

  const updateProductCardItem = (fieldName, index, patch) => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      [fieldName]: normalizeCardItems(currentForm[fieldName]).map((item, itemIndex) =>
        itemIndex === index ? { ...item, ...patch } : item
      ),
    }))
  }

  const addProductCardItem = (fieldName) => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      [fieldName]: [
        ...normalizeCardItems(currentForm[fieldName]),
        { title: '', description: '', icon: '' },
      ],
    }))
  }

  const removeProductCardItem = (fieldName, index) => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      [fieldName]: normalizeCardItems(currentForm[fieldName]).filter(
        (_, itemIndex) => itemIndex !== index
      ),
    }))
  }

  const updateProductPlanItem = (index, patch) => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      plansItems: normalizePlanItems(currentForm.plansItems).map((item, itemIndex) =>
        itemIndex === index ? { ...item, ...patch } : item
      ),
    }))
  }

  const addProductPlanItem = () => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      plansItems: [...normalizePlanItems(currentForm.plansItems), { title: '', icon: '', items: [] }],
    }))
  }

  const removeProductPlanItem = (index) => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      plansItems: normalizePlanItems(currentForm.plansItems).filter(
        (_, itemIndex) => itemIndex !== index
      ),
    }))
  }

  const renderIconSelect = (value, onChange) => (
    <label className="admin-field">
      <span>Иконка</span>
      <select
        className="admin-input"
        value={value || ''}
        onChange={(event) => onChange(event.target.value)}
      >
        {cardIconOptions.map((option) => (
          <option key={option.value || 'auto'} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    </label>
  )

  const renderProductCardsEditor = (fieldName, addLabel = 'Добавить карточку') => (
    <div className="admin-cards-editor">
      {normalizeCardItems(productPageForm[fieldName]).map((item, index) => (
        <article className="admin-content-card" key={`${fieldName}-${index}`}>
          <label className="admin-field">
            <span>Заголовок</span>
            <input
              className="admin-input"
              value={item.title}
              onChange={(event) =>
                updateProductCardItem(fieldName, index, { title: event.target.value })
              }
            />
          </label>
          <label className="admin-field">
            <span>Подзаголовок</span>
            <textarea
              className="admin-input admin-input--textarea"
              value={item.description}
              onChange={(event) =>
                updateProductCardItem(fieldName, index, { description: event.target.value })
              }
            />
          </label>
          {renderIconSelect(item.icon, (icon) => updateProductCardItem(fieldName, index, { icon }))}
          <button
            className="admin-button admin-button--danger admin-button--wide"
            type="button"
            onClick={() => removeProductCardItem(fieldName, index)}
          >
            Удалить карточку
          </button>
        </article>
      ))}
      <button
        className="admin-button admin-button--wide"
        type="button"
        onClick={() => addProductCardItem(fieldName)}
      >
        {addLabel}
      </button>
    </div>
  )

  const renderProductPlansEditor = () => (
    <div className="admin-cards-editor">
      {normalizePlanItems(productPageForm.plansItems).map((item, index) => (
        <article className="admin-content-card" key={`plan-${index}`}>
          <label className="admin-field">
            <span>Название тарифа</span>
            <input
              className="admin-input"
              value={item.title}
              onChange={(event) => updateProductPlanItem(index, { title: event.target.value })}
            />
          </label>
          {renderIconSelect(item.icon, (icon) => updateProductPlanItem(index, { icon }))}
          <label className="admin-field">
            <span>Пункты тарифа</span>
            <textarea
              className="admin-input admin-input--textarea admin-input--large"
              value={formatList(item.items)}
              onChange={(event) =>
                updateProductPlanItem(index, { items: parseList(event.target.value) })
              }
            />
          </label>
          <button
            className="admin-button admin-button--danger admin-button--wide"
            type="button"
            onClick={() => removeProductPlanItem(index)}
          >
            Удалить тариф
          </button>
        </article>
      ))}
      <button className="admin-button admin-button--wide" type="button" onClick={addProductPlanItem}>
        Добавить тариф
      </button>
    </div>
  )

  const onFaqChange = (index, patch) => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      faqItems: normalizeFaqItems(currentForm.faqItems).map((item, itemIndex) =>
        itemIndex === index ? { ...item, ...patch } : item
      ),
    }))
  }

  const onAddFaqItem = () => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      faqItems: [...normalizeFaqItems(currentForm.faqItems), { question: '', answer: '' }],
    }))
  }

  const onRemoveFaqItem = (index) => {
    setProductPageForm((currentForm) => ({
      ...currentForm,
      faqItems: normalizeFaqItems(currentForm.faqItems).filter(
        (_, itemIndex) => itemIndex !== index
      ),
    }))
  }

  const onInteriorFaqChange = (index, patch) => {
    setInteriorPageForm((currentForm) => ({
      ...currentForm,
      faqItems: normalizeFaqItems(currentForm.faqItems).map((item, itemIndex) =>
        itemIndex === index ? { ...item, ...patch } : item
      ),
    }))
  }

  const onAddInteriorFaqItem = () => {
    setInteriorPageForm((currentForm) => ({
      ...currentForm,
      faqItems: [...normalizeFaqItems(currentForm.faqItems), { question: '', answer: '' }],
    }))
  }

  const onRemoveInteriorFaqItem = (index) => {
    setInteriorPageForm((currentForm) => ({
      ...currentForm,
      faqItems: normalizeFaqItems(currentForm.faqItems).filter(
        (_, itemIndex) => itemIndex !== index
      ),
    }))
  }

  const onLeadStatusChange = async (lead, status) => {
    const data = await request('/api/admin/leads', {
      method: 'PATCH',
      body: JSON.stringify({ id: lead.id, status }),
    })

    setLeads((currentLeads) =>
      currentLeads.map((item) => (item.id === lead.id ? data.lead : item))
    )
  }

  if (!isReady) {
    return <main className="admin-page">Загрузка...</main>
  }

  if (!isAuthenticated) {
    return (
      <main className="admin-page admin-page--login">
        <form className="admin-login" onSubmit={onLoginSubmit}>
          <h1 className="admin-login__title">Админ-панель</h1>
          <p className="admin-login__text">Введите пароль для управления сайтом.</p>
          <input
            className="admin-input"
            type="password"
            value={password}
            placeholder="Пароль"
            onChange={(event) => setPassword(event.target.value)}
          />
          <button className="admin-button" type="submit">
            Войти
          </button>
          {message && <p className="admin-message">{message}</p>}
        </form>
      </main>
    )
  }

  const adminNavItems = [
    ['pages', 'Страницы'],
    ['reviews', `Отзывы${reviews.length ? ` (${reviews.length})` : ''}`],
    ['leads', `Заявки${leads.length ? ` (${leads.length})` : ''}`],
    ['settings', 'Контакты и реквизиты'],
  ]

  return (
    <main className="admin-page">
      <header className="admin-header">
        <div>
          <p className="admin-header__eyebrow">Кубэра</p>
          <h1 className="admin-header__title">Админ-панель сайта</h1>
        </div>
        <button className="admin-button admin-button--ghost" type="button" onClick={onLogout}>
          Выйти
        </button>
      </header>

      <nav className="admin-tabs" aria-label="Разделы админки">
        {[
          ['leads', 'Заявки'],
          ['reviews', 'Отзывы'],
          ['settings', 'Контакты и реквизиты'],
          ['pages', 'Страницы'],
        ].map(([tab, label]) => (
          <button
            className={`admin-tabs__button${
              activeTab === tab ||
              (tab === 'pages' &&
                (activeTab === 'productPage' ||
                  activeTab === 'interiorPage' ||
                  activeTab === 'documentPage'))
                ? ' is-active'
                : ''
            }`}
            type="button"
            onClick={() => setActiveTab(tab)}
            key={tab}
          >
            {label}
          </button>
        ))}
      </nav>

      {message && <p className="admin-message admin-message--floating">{message}</p>}

      {activeTab === 'leads' && (
        <section className="admin-section">
          <div className="admin-section__header">
            <h2 className="admin-section__title">Заявки клиентов</h2>
            <span className="admin-section__counter">{leads.length}</span>
          </div>

          <div className="admin-table">
            {leads.length === 0 ? (
              <p className="admin-empty">Заявок пока нет.</p>
            ) : (
              leads.map((lead) => (
                <article className="admin-lead" key={lead.id}>
                  <div>
                    <h3 className="admin-lead__title">{lead.name || 'Без имени'}</h3>
                    <p className="admin-lead__meta">{formatDate(lead.createdAt)}</p>
                    <p className="admin-lead__text">{lead.comment || 'Без комментария'}</p>
                  </div>
                  <div className="admin-lead__contacts">
                    {renderLeadPhone(lead.phone)}
                    {lead.email && <a href={`mailto:${lead.email}`}>{lead.email}</a>}
                    {lead.contactMethod && <span>{lead.contactMethod}</span>}
                    <span>{lead.source || 'Источник не указан'}</span>
                  </div>
                  <select
                    className="admin-input"
                    value={lead.status}
                    onChange={(event) => onLeadStatusChange(lead, event.target.value)}
                  >
                    <option value="new">Новая</option>
                    <option value="in-progress">В работе</option>
                    <option value="done">Закрыта</option>
                  </select>
                </article>
              ))
            )}
          </div>
        </section>
      )}

      {activeTab === 'reviews' && (
        <section className="admin-section">
          <div className="admin-section__header">
            <h2 className="admin-section__title">Отзывы клиентов</h2>
            <span className="admin-section__counter">{reviews.length}</span>
          </div>

          <form className="admin-form admin-form--compact" onSubmit={onCreateReview}>
            <h3 className="admin-subtitle">Добавить отзыв</h3>
            <div className="admin-review-form">
              <label className="admin-field">
                <span>Имя клиента</span>
                <input
                  className="admin-input"
                  value={newReview.author}
                  onChange={(event) =>
                    setNewReview((currentReview) => ({
                      ...currentReview,
                      author: event.target.value,
                    }))
                  }
                  required
                />
              </label>
              <label className="admin-field">
                <span>Дата</span>
                <input
                  className="admin-input"
                  value={newReview.date}
                  onChange={(event) =>
                    setNewReview((currentReview) => ({
                      ...currentReview,
                      date: event.target.value,
                    }))
                  }
                  placeholder="10 апреля 2023"
                  required
                />
              </label>
              <label className="admin-field">
                <span>Категория</span>
                <select
                  className="admin-input"
                  value={newReview.category}
                  onChange={(event) =>
                    setNewReview((currentReview) => ({
                      ...currentReview,
                      category: event.target.value,
                    }))
                  }
                >
                  <option value="all">Все</option>
                  <option value="trim">Наличники</option>
                  <option value="frames">Обсады</option>
                  <option value="sills">Подоконники</option>
                  <option value="stairs">Лестницы</option>
                  <option value="small-forms">Малые формы</option>
                </select>
              </label>
              <label className="admin-field">
                <span>Порядок</span>
                <input
                  className="admin-input"
                  type="number"
                  value={newReview.order}
                  onChange={(event) =>
                    setNewReview((currentReview) => ({
                      ...currentReview,
                      order: event.target.value,
                    }))
                  }
                />
              </label>
              <label className="admin-field admin-field--wide">
                <span>Текст отзыва</span>
                <textarea
                  className="admin-input admin-input--textarea"
                  value={newReview.text}
                  onChange={(event) =>
                    setNewReview((currentReview) => ({
                      ...currentReview,
                      text: event.target.value,
                    }))
                  }
                  required
                />
              </label>
              <label className="admin-cover-upload">
                <span>Аватарка</span>
                {newReview.avatar && (
                  <img className="admin-cover-upload__preview" src={newReview.avatar} alt="" />
                )}
                <input
                  className="admin-cover-upload__input"
                  type="file"
                  accept="image/*"
                  onChange={(event) => onNewReviewAvatarUpload(event.target.files?.[0])}
                />
              </label>
              <label className="admin-field">
                <span>Статус</span>
                <select
                  className="admin-input"
                  value={newReview.status}
                  onChange={(event) =>
                    setNewReview((currentReview) => ({
                      ...currentReview,
                      status: event.target.value,
                    }))
                  }
                >
                  <option value="published">Опубликован</option>
                  <option value="draft">Черновик</option>
                </select>
              </label>
            </div>
            <button className="admin-button admin-button--wide" type="submit">
              Добавить отзыв
            </button>
          </form>

          <h3 className="admin-subtitle">Список отзывов</h3>
          <div className="admin-reviews-list">
            {reviews.length === 0 ? (
              <p className="admin-empty">Отзывы пока не добавлены.</p>
            ) : (
              reviews.map((review) => (
                <article className="admin-review-card" key={review.id}>
                  <div className="admin-review-card__header">
                    {review.avatar && (
                      <img className="admin-review-card__avatar" src={review.avatar} alt="" />
                    )}
                    <div>
                      <h4 className="admin-review-card__title">
                        {review.author || 'Отзыв без имени'}
                      </h4>
                      <p className="admin-review-card__meta">{review.date || 'Дата не указана'}</p>
                    </div>
                  </div>

                  <div className="admin-review-form">
                    <label className="admin-field">
                      <span>Имя клиента</span>
                      <input
                        className="admin-input"
                        value={review.author}
                        onChange={(event) =>
                          updateReviewDraft(review.id, { author: event.target.value })
                        }
                      />
                    </label>
                    <label className="admin-field">
                      <span>Дата</span>
                      <input
                        className="admin-input"
                        value={review.date}
                        onChange={(event) =>
                          updateReviewDraft(review.id, { date: event.target.value })
                        }
                      />
                    </label>
                    <label className="admin-field">
                      <span>Категория</span>
                      <select
                        className="admin-input"
                        value={review.category || 'all'}
                        onChange={(event) =>
                          updateReviewDraft(review.id, { category: event.target.value })
                        }
                      >
                        <option value="all">Все</option>
                        <option value="trim">Наличники</option>
                        <option value="frames">Обсады</option>
                        <option value="sills">Подоконники</option>
                        <option value="stairs">Лестницы</option>
                        <option value="small-forms">Малые формы</option>
                      </select>
                    </label>
                    <label className="admin-field">
                      <span>Порядок</span>
                      <input
                        className="admin-input"
                        type="number"
                        value={review.order || 0}
                        onChange={(event) =>
                          updateReviewDraft(review.id, { order: event.target.value })
                        }
                      />
                    </label>
                    <label className="admin-field admin-field--wide">
                      <span>Текст отзыва</span>
                      <textarea
                        className="admin-input admin-input--textarea"
                        value={review.text}
                        onChange={(event) =>
                          updateReviewDraft(review.id, { text: event.target.value })
                        }
                      />
                    </label>
                    <label className="admin-cover-upload">
                      <span>Аватарка</span>
                      {review.avatar && (
                        <img className="admin-cover-upload__preview" src={review.avatar} alt="" />
                      )}
                      <input
                        className="admin-cover-upload__input"
                        type="file"
                        accept="image/*"
                        onChange={(event) =>
                          onReviewAvatarUpload(review, event.target.files?.[0])
                        }
                      />
                    </label>
                    <label className="admin-field">
                      <span>Статус</span>
                      <select
                        className="admin-input"
                        value={review.status || 'published'}
                        onChange={(event) =>
                          updateReviewDraft(review.id, { status: event.target.value })
                        }
                      >
                        <option value="published">Опубликован</option>
                        <option value="draft">Черновик</option>
                      </select>
                    </label>
                  </div>

                  <div className="admin-review-card__actions">
                    <button
                      className="admin-button"
                      type="button"
                      onClick={() => onUpdateReview(review)}
                    >
                      Сохранить
                    </button>
                    <button
                      className="admin-button admin-button--danger"
                      type="button"
                      onClick={() => onDeleteReview(review)}
                    >
                      Удалить
                    </button>
                  </div>
                </article>
              ))
            )}
          </div>
        </section>
      )}

      {activeTab === 'settings' && (
        <section className="admin-section">
          <h2 className="admin-section__title">Контакты, соцсети и юридическая информация</h2>

          <form className="admin-form" onSubmit={onSettingsSubmit}>
            <label className="admin-field">
              <span>Телефон</span>
              <input
                className="admin-input"
                value={settings.phone}
                onChange={(event) => setSettings({ ...settings, phone: event.target.value })}
              />
            </label>
            <label className="admin-field">
              <span>Email</span>
              <input
                className="admin-input"
                value={settings.email}
                onChange={(event) => setSettings({ ...settings, email: event.target.value })}
              />
            </label>
            <label className="admin-field admin-field--wide">
              <span>Адрес</span>
              <input
                className="admin-input"
                value={settings.address}
                onChange={(event) => setSettings({ ...settings, address: event.target.value })}
              />
            </label>
            <label className="admin-field">
              <span>График работы</span>
              <input
                className="admin-input"
                value={settings.workingHours}
                onChange={(event) =>
                  setSettings({ ...settings, workingHours: event.target.value })
                }
              />
            </label>
            <label className="admin-field admin-field--wide">
              <span>Юридическая информация</span>
              <textarea
                className="admin-input admin-input--textarea"
                value={settings.legalInfo}
                onChange={(event) => setSettings({ ...settings, legalInfo: event.target.value })}
              />
            </label>

            {Object.keys(settings.socials || {}).map((name) => (
              <label className="admin-field" key={name}>
                <span>{name}</span>
                <input
                  className="admin-input"
                  value={settings.socials[name]}
                  onChange={(event) =>
                    setSettings({
                      ...settings,
                      socials: { ...settings.socials, [name]: event.target.value },
                    })
                  }
                />
              </label>
            ))}

            <button className="admin-button" type="submit">
              Сохранить настройки
            </button>
          </form>
        </section>
      )}

      {activeTab === 'productPage' && (
        <section className="admin-section">
          <div className="admin-editor-bar">
            <button
              className="admin-button admin-button--ghost admin-button--wide"
              type="button"
              onClick={() => {
                setActiveTab('pages')
                setSelectedPageId('')
              }}
            >
              Назад к списку страниц
            </button>
            {selectedPage && <span>Редактируется: {selectedPage.title}</span>}
          </div>
          <h2 className="admin-section__title">Контент страниц продукции</h2>
          <p className="admin-help">
            Сначала создайте страницу продукции во вкладке «Страницы», затем выберите ее здесь.
            Для карточек используйте формат: Название | описание. Для тарифов отделяйте тарифы
            пустой строкой: первая строка - название, следующие строки - пункты.
          </p>

          <form className="admin-form admin-form--product" onSubmit={onProductPageSubmit}>
            <label className="admin-field">
              <span>Редактируемая страница</span>
              <select
                className="admin-input"
                value={editingProductPageId}
                onChange={(event) => onProductPageSelect(event.target.value)}
              >
                <option value="">Выберите страницу</option>
                {groupedPages.product.map((page) => (
                  <option value={page.id} key={page.id}>
                    {page.title || page.slug}
                  </option>
                ))}
              </select>
            </label>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Первый экран</h3>
              <label className="admin-field">
                <span>Надзаголовок</span>
                <input
                  className="admin-input"
                  value={productPageForm.heroSubtitle}
                  onChange={(event) => updateProductPageField('heroSubtitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={productPageForm.heroTitle}
                  onChange={(event) => updateProductPageField('heroTitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Акцентная строка</span>
                <input
                  className="admin-input"
                  value={productPageForm.heroAccent}
                  onChange={(event) => updateProductPageField('heroAccent', event.target.value)}
                />
              </label>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Что входит в услугу</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={productPageForm.includesTitle}
                  onChange={(event) =>
                    updateProductPageField('includesTitle', event.target.value)
                  }
                />
              </label>
              <label className="admin-field">
                <span>Подзаголовок</span>
                <textarea
                  className="admin-input admin-input--textarea"
                  value={productPageForm.includesDescription}
                  onChange={(event) =>
                    updateProductPageField('includesDescription', event.target.value)
                  }
                />
              </label>
              <div className="admin-field admin-field--full">
                <span>Карточки</span>
                {renderProductCardsEditor('includesItems')}
              </div>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Материалы</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={productPageForm.materialsTitle}
                  onChange={(event) =>
                    updateProductPageField('materialsTitle', event.target.value)
                  }
                />
              </label>
              <div className="admin-materials-editor">
                {getProductMaterials().map((material, index) => (
                  <article className="admin-material-card" key={`${material.title}-${index}`}>
                    <label className="admin-field">
                      <span>Название материала</span>
                      <input
                        className="admin-input"
                        value={material.title}
                        onChange={(event) =>
                          onMaterialChange(index, { title: event.target.value })
                        }
                      />
                    </label>
                    <label className="admin-cover-upload">
                      <span>Фотография материала</span>
                      {material.image && (
                        <img
                          className="admin-cover-upload__preview"
                          src={material.image}
                          alt=""
                        />
                      )}
                      <input
                        className="admin-cover-upload__input"
                        type="file"
                        accept="image/*"
                        onChange={(event) =>
                          onMaterialImageUpload(index, event.target.files?.[0])
                        }
                      />
                    </label>
                    <button
                      className="admin-button admin-button--danger"
                      type="button"
                      onClick={() => onRemoveMaterial(index)}
                    >
                      Удалить материал
                    </button>
                  </article>
                ))}
                <button className="admin-button admin-button--wide" type="button" onClick={onAddMaterial}>
                  Добавить материал
                </button>
              </div>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Цветовые решения</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={productPageForm.colorsTitle}
                  onChange={(event) => updateProductPageField('colorsTitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Описание рядом с заголовком</span>
                <textarea
                  className="admin-input admin-input--textarea"
                  value={productPageForm.colorsDescription}
                  onChange={(event) =>
                    updateProductPageField('colorsDescription', event.target.value)
                  }
                />
              </label>
              <div className="admin-field admin-field--full">
                <span>Карточки</span>
                {renderProductCardsEditor('colorsItems')}
              </div>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Преимущества</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={productPageForm.benefitsTitle}
                  onChange={(event) => updateProductPageField('benefitsTitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Описание рядом с заголовком</span>
                <textarea
                  className="admin-input admin-input--textarea"
                  value={productPageForm.benefitsDescription}
                  onChange={(event) =>
                    updateProductPageField('benefitsDescription', event.target.value)
                  }
                />
              </label>
              <div className="admin-field admin-field--full">
                <span>Карточки</span>
                {renderProductCardsEditor('benefitsItems')}
              </div>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Варианты сотрудничества</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={productPageForm.plansTitle}
                  onChange={(event) => updateProductPageField('plansTitle', event.target.value)}
                />
              </label>
              <div className="admin-field admin-field--full">
                <span>Тарифы</span>
                {renderProductPlansEditor()}
              </div>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">FAQ / Частые вопросы</h3>
              <div className="admin-faq-editor">
                {normalizeFaqItems(productPageForm.faqItems).map((item, index) => (
                  <article className="admin-faq-card" key={`faq-${index}`}>
                    <label className="admin-field">
                      <span>Вопрос</span>
                      <input
                        className="admin-input"
                        value={item.question}
                        onChange={(event) => onFaqChange(index, { question: event.target.value })}
                      />
                    </label>
                    <label className="admin-field">
                      <span>Ответ</span>
                      <textarea
                        className="admin-input admin-input--textarea"
                        value={item.answer}
                        onChange={(event) => onFaqChange(index, { answer: event.target.value })}
                      />
                    </label>
                    <button
                      className="admin-button admin-button--danger admin-button--wide"
                      type="button"
                      onClick={() => onRemoveFaqItem(index)}
                    >
                      Удалить вопрос
                    </button>
                  </article>
                ))}
                <button className="admin-button admin-button--wide" type="button" onClick={onAddFaqItem}>
                  Добавить еще вопрос
                </button>
              </div>
            </div>

            <button className="admin-button admin-button--wide" type="submit">
              Сохранить страницу продукции
            </button>
          </form>
        </section>
      )}

      {activeTab === 'interiorPage' && (
        <section className="admin-section">
          <div className="admin-editor-bar">
            <button
              className="admin-button admin-button--ghost admin-button--wide"
              type="button"
              onClick={() => {
                setActiveTab('pages')
                setSelectedPageId('')
              }}
            >
              Назад к списку страниц
            </button>
            {selectedPage && <span>Редактируется: {selectedPage.title}</span>}
          </div>
          <h2 className="admin-section__title">Контент страниц отделки интерьера</h2>
          <p className="admin-help">
            Для карточек используйте формат: Название | описание. Для блоков со списками:
            первая строка - название карточки, следующие строки - пункты, пустая строка отделяет карточки.
          </p>

          <form className="admin-form admin-form--product" onSubmit={onInteriorPageSubmit}>
            <label className="admin-field">
              <span>Редактируемая страница</span>
              <select
                className="admin-input"
                value={editingInteriorPageId}
                onChange={(event) => onInteriorPageSelect(event.target.value)}
              >
                <option value="">Выберите страницу</option>
                {groupedPages.interior.map((page) => (
                  <option value={page.id} key={page.id}>
                    {page.title || page.slug}
                  </option>
                ))}
              </select>
            </label>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Первый экран</h3>
              <label className="admin-field">
                <span>Надзаголовок</span>
                <input
                  className="admin-input"
                  value={interiorPageForm.heroSubtitle}
                  onChange={(event) => updateInteriorPageField('heroSubtitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={interiorPageForm.heroTitle}
                  onChange={(event) => updateInteriorPageField('heroTitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Акцентная строка</span>
                <input
                  className="admin-input"
                  value={interiorPageForm.heroAccent}
                  onChange={(event) => updateInteriorPageField('heroAccent', event.target.value)}
                />
              </label>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Что входит в услугу</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={interiorPageForm.includesTitle}
                  onChange={(event) => updateInteriorPageField('includesTitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Карточки</span>
                <textarea
                  className="admin-input admin-input--textarea"
                  value={interiorPageForm.includesItems}
                  onChange={(event) => updateInteriorPageField('includesItems', event.target.value)}
                />
              </label>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Специальные решения</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={interiorPageForm.roomSolutionsTitle}
                  onChange={(event) =>
                    updateInteriorPageField('roomSolutionsTitle', event.target.value)
                  }
                />
              </label>
              <label className="admin-field">
                <span>Карточки со списками</span>
                <textarea
                  className="admin-input admin-input--textarea admin-input--large"
                  value={interiorPageForm.roomSolutionsItems}
                  onChange={(event) =>
                    updateInteriorPageField('roomSolutionsItems', event.target.value)
                  }
                />
              </label>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Материалы</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={interiorPageForm.materialsTitle}
                  onChange={(event) => updateInteriorPageField('materialsTitle', event.target.value)}
                />
              </label>
              <div className="admin-materials-editor">
                {getInteriorMaterials().map((material, index) => (
                  <article className="admin-material-card" key={`${material.title}-${index}`}>
                    <label className="admin-field">
                      <span>Название материала</span>
                      <input
                        className="admin-input"
                        value={material.title}
                        onChange={(event) =>
                          onInteriorMaterialChange(index, { title: event.target.value })
                        }
                      />
                    </label>
                    <label className="admin-cover-upload">
                      <span>Фотография материала</span>
                      {material.image && (
                        <img
                          className="admin-cover-upload__preview"
                          src={material.image}
                          alt=""
                        />
                      )}
                      <input
                        className="admin-cover-upload__input"
                        type="file"
                        accept="image/*"
                        onChange={(event) =>
                          onInteriorMaterialImageUpload(index, event.target.files?.[0])
                        }
                      />
                    </label>
                    <button
                      className="admin-button admin-button--danger"
                      type="button"
                      onClick={() => onRemoveInteriorMaterial(index)}
                    >
                      Удалить материал
                    </button>
                  </article>
                ))}
                <button
                  className="admin-button admin-button--wide"
                  type="button"
                  onClick={onAddInteriorMaterial}
                >
                  Добавить материал
                </button>
              </div>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Преимущества</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={interiorPageForm.advantagesTitle}
                  onChange={(event) => updateInteriorPageField('advantagesTitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Описание рядом с заголовком</span>
                <textarea
                  className="admin-input admin-input--textarea"
                  value={interiorPageForm.advantagesDescription}
                  onChange={(event) =>
                    updateInteriorPageField('advantagesDescription', event.target.value)
                  }
                />
              </label>
              <label className="admin-field">
                <span>Карточки</span>
                <textarea
                  className="admin-input admin-input--textarea"
                  value={interiorPageForm.advantagesItems}
                  onChange={(event) =>
                    updateInteriorPageField('advantagesItems', event.target.value)
                  }
                />
              </label>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Варианты сотрудничества</h3>
              <label className="admin-field">
                <span>Заголовок</span>
                <input
                  className="admin-input"
                  value={interiorPageForm.plansTitle}
                  onChange={(event) => updateInteriorPageField('plansTitle', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Тарифы</span>
                <textarea
                  className="admin-input admin-input--textarea admin-input--large"
                  value={interiorPageForm.plansItems}
                  onChange={(event) => updateInteriorPageField('plansItems', event.target.value)}
                />
              </label>
            </div>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">FAQ / Частые вопросы</h3>
              <div className="admin-faq-editor">
                {normalizeFaqItems(interiorPageForm.faqItems).map((item, index) => (
                  <article className="admin-faq-card" key={`interior-faq-${index}`}>
                    <label className="admin-field">
                      <span>Вопрос</span>
                      <input
                        className="admin-input"
                        value={item.question}
                        onChange={(event) =>
                          onInteriorFaqChange(index, { question: event.target.value })
                        }
                      />
                    </label>
                    <label className="admin-field">
                      <span>Ответ</span>
                      <textarea
                        className="admin-input admin-input--textarea"
                        value={item.answer}
                        onChange={(event) =>
                          onInteriorFaqChange(index, { answer: event.target.value })
                        }
                      />
                    </label>
                    <button
                      className="admin-button admin-button--danger admin-button--wide"
                      type="button"
                      onClick={() => onRemoveInteriorFaqItem(index)}
                    >
                      Удалить вопрос
                    </button>
                  </article>
                ))}
                <button
                  className="admin-button admin-button--wide"
                  type="button"
                  onClick={onAddInteriorFaqItem}
                >
                  Добавить еще вопрос
                </button>
              </div>
            </div>

            <button className="admin-button admin-button--wide" type="submit">
              Сохранить страницу отделки интерьера
            </button>
          </form>
        </section>
      )}

      {activeTab === 'documentPage' && (
        <section className="admin-section">
          <div className="admin-editor-bar">
            <button
              className="admin-button admin-button--ghost admin-button--wide"
              type="button"
              onClick={() => {
                setActiveTab('pages')
                setSelectedPageId('')
              }}
            >
              Назад к списку страниц
            </button>
            {selectedPage && <span>Редактируется: {selectedPage.title}</span>}
          </div>
          <h2 className="admin-section__title">Контент страницы документа</h2>
          <p className="admin-help">
            Этот шаблон подходит для договоров, политик и соглашений. На сайте будет
            показан H1 и текст документа с сохранением переносов строк.
          </p>

          <form className="admin-form admin-form--product" onSubmit={onDocumentPageSubmit}>
            <label className="admin-field">
              <span>Редактируемая страница</span>
              <select
                className="admin-input"
                value={editingDocumentPageId}
                onChange={(event) => onDocumentPageSelect(event.target.value)}
              >
                <option value="">Выберите документ</option>
                {groupedPages.document.map((page) => (
                  <option value={page.id} key={page.id}>
                    {page.title || page.slug}
                  </option>
                ))}
              </select>
            </label>

            <div className="admin-product-block">
              <h3 className="admin-product-block__title">Основной контент</h3>
              <label className="admin-field">
                <span>Заголовок H1</span>
                <input
                  className="admin-input"
                  value={documentPageForm.h1}
                  onChange={(event) => updateDocumentPageField('h1', event.target.value)}
                />
              </label>
              <label className="admin-field">
                <span>Текст договора</span>
                <textarea
                  className="admin-input admin-input--textarea admin-input--large"
                  value={documentPageForm.text}
                  onChange={(event) => updateDocumentPageField('text', event.target.value)}
                />
              </label>
            </div>

            <button className="admin-button admin-button--wide" type="submit">
              Сохранить документ
            </button>
          </form>
        </section>
      )}

      {activeTab === 'pages' && (
        <section className="admin-section">
          <h2 className="admin-section__title">Продукция, интерьер и документы</h2>

          <form className="admin-form admin-form--compact" onSubmit={onCreatePage}>
            <select
              className="admin-input"
              value={newPage.type}
              required
              onChange={(event) => {
                const type = event.target.value

                setNewPage({
                  ...newPage,
                  type,
                  status: type === 'document' ? 'published' : newPage.status,
                })
              }}
            >
              <option value="product">Продукция</option>
              <option value="interior">Отделка интерьера</option>
              <option value="document">Документ</option>
            </select>
            <input
              className="admin-input"
              value={newPage.title}
              required
              placeholder="Название"
              onChange={(event) => setNewPage({ ...newPage, title: event.target.value })}
            />
            <input
              className="admin-input"
              value={newPage.slug}
              required
              placeholder="URL / slug"
              onChange={(event) => setNewPage({ ...newPage, slug: event.target.value })}
            />
            <input
              className="admin-input"
              value={newPage.menuDescription}
              required={newPage.type !== 'document'}
              placeholder="Описание в меню"
              onChange={(event) =>
                setNewPage({ ...newPage, menuDescription: event.target.value })
              }
            />
            <input
              className="admin-input"
              value={newPage.seoTitle}
              required
              placeholder="SEO title"
              onChange={(event) => setNewPage({ ...newPage, seoTitle: event.target.value })}
            />
            <input
              className="admin-input"
              value={newPage.seoDescription}
              required
              placeholder="SEO description"
              onChange={(event) =>
                setNewPage({ ...newPage, seoDescription: event.target.value })
              }
            />
            {newPage.type !== 'document' && (
              <label className="admin-cover-upload admin-cover-upload--compact">
              <span>Обложка</span>
              {newPage.cover && (
                <img className="admin-cover-upload__preview" src={newPage.cover} alt="" />
              )}
              <input
                className="admin-cover-upload__input"
                type="file"
                accept="image/*"
                required={!newPage.cover}
                onChange={(event) => onNewPageCoverUpload(event.target.files?.[0])}
              />
              </label>
            )}
            <button className="admin-button" type="submit">
              Добавить
            </button>
          </form>

          <div className="admin-pages-list-heading">
            <h3>Созданные страницы</h3>
            <span>{pages.length}</span>
          </div>

          {[
            ['product', 'Продукция'],
            ['interior', 'Отделка интерьера'],
            ['document', 'Документы'],
          ].map(([type, title]) => (
            <div className="admin-pages-group" key={type}>
              <h3 className="admin-pages-group__title">{title}</h3>
              {groupedPages[type].map((page) => (
                <article className="admin-page-card" key={page.id}>
                  <div className="admin-page-card__summary">
                    <div>
                      <h4>{page.title || 'Без названия'}</h4>
                      <p>
                        /{page.slug || 'bez-url'} ·{' '}
                        {page.status === 'published' ? 'Опубликована' : 'Черновик'}
                      </p>
                    </div>
                    <div className="admin-page-card__summary-actions">
                      <button
                        className="admin-button admin-button--ghost"
                        type="button"
                        onClick={() => togglePageExpanded(page.id)}
                      >
                        {expandedPageIds.includes(page.id) ? 'Свернуть' : 'Развернуть'}
                      </button>
                      <button
                        className="admin-button"
                        type="button"
                        onClick={() => onOpenPageEditor(page)}
                      >
                        Редактировать
                      </button>
                    </div>
                  </div>
                  {expandedPageIds.includes(page.id) && (
                    <div className="admin-page-card__details">
                  <input
                    className="admin-input"
                    value={page.title}
                    onChange={(event) =>
                      setPages((currentPages) =>
                        currentPages.map((item) =>
                          item.id === page.id ? { ...item, title: event.target.value } : item
                        )
                      )
                    }
                  />
                  <input
                    className="admin-input"
                    value={page.slug}
                    onChange={(event) =>
                      setPages((currentPages) =>
                        currentPages.map((item) =>
                          item.id === page.id ? { ...item, slug: event.target.value } : item
                        )
                      )
                    }
                  />
                  <input
                    className="admin-input"
                    value={page.menuDescription}
                    placeholder="Описание в меню"
                    onChange={(event) =>
                      setPages((currentPages) =>
                        currentPages.map((item) =>
                          item.id === page.id
                            ? { ...item, menuDescription: event.target.value }
                            : item
                        )
                      )
                    }
                  />
                  <input
                    className="admin-input"
                    value={page.seoTitle}
                    placeholder="SEO title"
                    onChange={(event) =>
                      setPages((currentPages) =>
                        currentPages.map((item) =>
                          item.id === page.id ? { ...item, seoTitle: event.target.value } : item
                        )
                      )
                    }
                  />
                  <input
                    className="admin-input"
                    value={page.seoDescription}
                    placeholder="SEO description"
                    onChange={(event) =>
                      setPages((currentPages) =>
                        currentPages.map((item) =>
                          item.id === page.id
                            ? { ...item, seoDescription: event.target.value }
                            : item
                        )
                      )
                    }
                  />
                  <label className="admin-cover-upload">
                    <span>Обложка</span>
                    {page.cover && (
                      <img className="admin-cover-upload__preview" src={page.cover} alt="" />
                    )}
                    <input
                      className="admin-cover-upload__input"
                      type="file"
                      accept="image/*"
                      onChange={(event) => onCoverUpload(page, event.target.files?.[0])}
                    />
                  </label>
                  <select
                    className="admin-input"
                    value={page.status}
                    onChange={(event) =>
                      setPages((currentPages) =>
                        currentPages.map((item) =>
                          item.id === page.id ? { ...item, status: event.target.value } : item
                        )
                      )
                    }
                  >
                    <option value="draft">Черновик</option>
                    <option value="published">Опубликована</option>
                  </select>
                  <button
                    className="admin-button admin-button--ghost"
                    type="button"
                    onClick={() => onOpenPageEditor(page)}
                  >
                    Редактировать
                  </button>
                  <button className="admin-button" type="button" onClick={() => onUpdatePage(page)}>
                    Сохранить
                  </button>
                  <button
                    className="admin-button admin-button--danger"
                    type="button"
                    onClick={() => onDeletePage(page)}
                  >
                    Удалить
                  </button>
                    </div>
                  )}
                </article>
              ))}
            </div>
          ))}
        </section>
      )}
    </main>
  )
}

AdminPage.isAdminPage = true

export default AdminPage
