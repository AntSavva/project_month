import Head from 'next/head'

export default function DocumentPageTemplate(props) {
  const { page, content } = props
  const seoTitle = page?.seoTitle || page?.title || content?.h1 || 'Документ'
  const seoDescription = page?.seoDescription || ''
  const title = content?.h1 || page?.title || 'Документ'
  const text = content?.text || ''

  return (
    <>
      <Head>
        <title>{seoTitle}</title>
        {seoDescription && <meta name="description" content={seoDescription} />}
      </Head>
      <section className="document-hero container" aria-labelledby="document-hero-title">
        <p className="document-hero__subtitle">Документ</p>
        <h1 className="document-hero__title h1" id="document-hero-title">
          {title}
        </h1>
      </section>
      <section className="document-content" aria-label="Содержание документа">
        <div className="document-content__inner container">
          <article className="document-content__section">
            <div className="document-content__text">
              {text.split('\n').map((line, index) => (
                <p key={`${line}-${index}`}>{line || '\u00A0'}</p>
              ))}
            </div>
          </article>
        </div>
      </section>
    </>
  )
}
