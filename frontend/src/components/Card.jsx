import React from 'react'
import './Card.css'

/**
 * Reusable Card komponenta
 * @param {string} title - Naslov kartice
 * @param {string} subtitle - Podnaslov kartice
 * @param {ReactNode} image - Slika kartice (može biti img tag ili div sa background-image)
 * @param {string} imageUrl - URL slike (alternativa za image prop)
 * @param {ReactNode} children - Glavni sadržaj kartice
 * @param {ReactNode} footer - Footer sadržaj (obično dugmad)
 * @param {ReactNode} header - Custom header sadržaj
 * @param {function} onClick - Funkcija koja se poziva na klik kartice
 * @param {boolean} hoverable - Da li kartica ima hover efekat
 * @param {string} variant - Varijanta: 'default', 'outlined', 'elevated'
 * @param {string} className - Dodatne CSS klase
 */
const Card = ({
  title,
  subtitle,
  image,
  imageUrl,
  children,
  footer,
  header,
  onClick,
  hoverable = false,
  variant = 'default',
  className = '',
  ...props
}) => {
  const cardClasses = [
    'card',
    `card--${variant}`,
    hoverable && 'card--hoverable',
    onClick && 'card--clickable',
    className
  ]
    .filter(Boolean)
    .join(' ')

  const handleClick = () => {
    if (onClick && !props.disabled) {
      onClick()
    }
  }

  const handleKeyDown = (e) => {
    if (onClick && (e.key === 'Enter' || e.key === ' ')) {
      e.preventDefault()
      handleClick()
    }
  }

  return (
    <div
      className={cardClasses}
      onClick={handleClick}
      onKeyDown={handleKeyDown}
      role={onClick ? 'button' : undefined}
      tabIndex={onClick ? 0 : undefined}
      {...props}
    >
      {(image || imageUrl) && (
        <div className="card__image">
          {image || <img src={imageUrl} alt={title || 'Card image'} />}
        </div>
      )}

      {(header || title || subtitle) && (
        <div className="card__header">
          {header || (
            <>
              {title && <h3 className="card__title">{title}</h3>}
              {subtitle && <p className="card__subtitle">{subtitle}</p>}
            </>
          )}
        </div>
      )}

      {children && <div className="card__content">{children}</div>}

      {footer && <div className="card__footer">{footer}</div>}
    </div>
  )
}

export default Card

