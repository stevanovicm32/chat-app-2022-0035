import React from 'react'
import './Button.css'

/**
 * Reusable Button komponenta
 * @param {string} variant - Stil dugmeta: 'primary', 'secondary', 'danger', 'success', 'outline'
 * @param {string} size - Veličina: 'small', 'medium', 'large'
 * @param {function} onClick - Funkcija koja se poziva na klik
 * @param {boolean} disabled - Da li je dugme onemogućeno
 * @param {string} type - Tip dugmeta: 'button', 'submit', 'reset'
 * @param {ReactNode} icon - Ikonica koja se prikazuje pre teksta
 * @param {ReactNode} iconRight - Ikonica koja se prikazuje posle teksta
 * @param {boolean} fullWidth - Da li dugme zauzima punu širinu
 * @param {string} className - Dodatne CSS klase
 * @param {ReactNode} children - Sadržaj dugmeta
 */
const Button = ({
  variant = 'primary',
  size = 'medium',
  onClick,
  disabled = false,
  type = 'button',
  icon,
  iconRight,
  fullWidth = false,
  className = '',
  children,
  ...props
}) => {
  const buttonClasses = [
    'btn',
    `btn--${variant}`,
    `btn--${size}`,
    fullWidth && 'btn--full-width',
    disabled && 'btn--disabled',
    className
  ]
    .filter(Boolean)
    .join(' ')

  return (
    <button
      type={type}
      className={buttonClasses}
      onClick={onClick}
      disabled={disabled}
      {...props}
    >
      {icon && <span className="btn__icon">{icon}</span>}
      {children && <span className="btn__text">{children}</span>}
      {iconRight && <span className="btn__icon btn__icon--right">{iconRight}</span>}
    </button>
  )
}

export default Button

