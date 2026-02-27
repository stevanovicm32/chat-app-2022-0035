import React, { useState } from 'react'
import './Input.css'

/**
 * Reusable Input komponenta
 * @param {string} type - Tip inputa: 'text', 'email', 'password', 'number', 'tel', 'url'
 * @param {string} label - Label tekst
 * @param {string} placeholder - Placeholder tekst
 * @param {string} value - Vrednost inputa
 * @param {function} onChange - Funkcija koja se poziva pri promeni vrednosti
 * @param {string} error - Poruka o grešci
 * @param {boolean} required - Da li je polje obavezno
 * @param {boolean} disabled - Da li je polje onemogućeno
 * @param {string} className - Dodatne CSS klase
 * @param {ReactNode} icon - Ikonica koja se prikazuje levo
 * @param {ReactNode} iconRight - Ikonica koja se prikazuje desno
 * @param {function} onBlur - Funkcija koja se poziva kada polje izgubi fokus
 * @param {function} onFocus - Funkcija koja se poziva kada polje dobije fokus
 * @param {number} min - Minimalna vrednost (za number tip)
 * @param {number} max - Maksimalna vrednost (za number tip)
 * @param {string} pattern - Regex pattern za validaciju
 */
const Input = ({
  type = 'text',
  label,
  placeholder,
  value,
  onChange,
  error,
  required = false,
  disabled = false,
  className = '',
  icon,
  iconRight,
  onBlur,
  onFocus,
  min,
  max,
  pattern,
  ...props
}) => {
  const [isFocused, setIsFocused] = useState(false)

  const handleFocus = (e) => {
    setIsFocused(true)
    if (onFocus) onFocus(e)
  }

  const handleBlur = (e) => {
    setIsFocused(false)
    if (onBlur) onBlur(e)
  }

  const inputClasses = [
    'input-field',
    error && 'input-field--error',
    disabled && 'input-field--disabled',
    isFocused && 'input-field--focused',
    icon && 'input-field--with-icon',
    iconRight && 'input-field--with-icon-right',
    className
  ]
    .filter(Boolean)
    .join(' ')

  return (
    <div className="input-wrapper">
      {label && (
        <label className="input-label">
          {label}
          {required && <span className="input-label__required">*</span>}
        </label>
      )}
      <div className="input-container">
        {icon && <span className="input-icon input-icon--left">{icon}</span>}
        <input
          type={type}
          className={inputClasses}
          placeholder={placeholder}
          value={value}
          onChange={onChange}
          disabled={disabled}
          required={required}
          onFocus={handleFocus}
          onBlur={handleBlur}
          min={min}
          max={max}
          pattern={pattern}
          {...props}
        />
        {iconRight && (
          <span className="input-icon input-icon--right">{iconRight}</span>
        )}
      </div>
      {error && <span className="input-error">{error}</span>}
    </div>
  )
}

export default Input

