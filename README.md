# Kubera Next.js

Сайт компании «Кубэра» на Next.js. Сейчас проект содержит статичную верстку страниц без WordPress/CMS-интеграций; админ-панель и редактирование контента будут добавляться отдельным этапом.

## Стек

- Next.js 16
- React 18
- SCSS
- ESLint, Stylelint, Prettier

## Команды

```bash
npm install
npm run dev
npm run build
npm run start
```

Dev-сервер запускается на `http://localhost:3000`.

## Страницы

- `/`
- `/about`
- `/contacts`
- `/document`
- `/reviews`
- `/service`
- `/services`

## SEO

Базовая SEO-обвязка находится в `src/pages/_app.jsx`: title, description, Open Graph и Twitter Card. Язык документа задается в `src/pages/_document.jsx`.
