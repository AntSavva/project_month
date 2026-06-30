import clsx from 'clsx'

export default (props) => {
  const { className, type = 'button', href, children, extraAttrs } = props

  const isLink = href !== undefined
  const Component = isLink ? 'a' : 'button'
  const linkAttributes = { href }
  const buttonAttributes = { type }
  const attributesByTag = isLink ? linkAttributes : buttonAttributes

  return (
    <Component
      className={clsx('button', className)}
      {...attributesByTag}
      {...extraAttrs}
    >
      {children}
    </Component>
  )
}