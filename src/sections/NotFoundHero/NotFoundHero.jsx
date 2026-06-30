import Button from '@/components/Button'

export default () => {
  return (
    <section className="not-found-hero" aria-labelledby="not-found-title">
      <div className="not-found-hero__inner container">
        <h1 className="not-found-hero__code" id="not-found-title">
          404
        </h1>
        <p className="not-found-hero__description">Страница не найдена</p>
        <Button className="not-found-hero__button" href="/">
          На главную
        </Button>
      </div>
    </section>
  )
}