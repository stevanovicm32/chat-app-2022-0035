import React, { useEffect } from 'react'
import './Modal.css'

/**
 * Reusable Modal komponenta
 * @param {boolean} isOpen - Da li je modal otvoren
 * @param {function} onClose - Funkcija koja se poziva pri zatvaranju modala
 * @param {string} title - Naslov modala
 * @param {ReactNode} children - Sadržaj modala
 * @param {ReactNode} footer - Footer sadržaj (obično dugmad)
 * @param {string} size - Veličina: 'small', 'medium', 'large', 'full'
 * @param {boolean} closeOnOverlayClick - Da li se modal zatvara klikom na overlay
 * @param {boolean} closeOnEscape - Da li se modal zatvara pritiskom na Escape
 * @param {boolean} showCloseButton - Da li se prikazuje dugme za zatvaranje
 * @param {string} className - Dodatne CSS klase
 */
const Modal = ({
  isOpen,
  onClose,
  title,
  children,
  footer,
  size = 'medium',
  closeOnOverlayClick = true,
  closeOnEscape = true,
  showCloseButton = true,
  className = '',
}) => {
  useEffect(() => {
    if (!isOpen) return

    const handleEscape = (e) => {
      if (closeOnEscape && e.key === 'Escape') {
        onClose()
      }
    }

    // Sprečava scroll dok je modal otvoren
    document.body.style.overflow = 'hidden'

    document.addEventListener('keydown', handleEscape)

    return () => {
      document.body.style.overflow = 'unset'
      document.removeEventListener('keydown', handleEscape)
    }
  }, [isOpen, closeOnEscape, onClose])

  if (!isOpen) return null

  const handleOverlayClick = (e) => {
    if (closeOnOverlayClick && e.target === e.currentTarget) {
      onClose()
    }
  }

  const modalClasses = [
    'modal',
    `modal--${size}`,
    className
  ]
    .filter(Boolean)
    .join(' ')

  return (
    <div className="modal-overlay" onClick={handleOverlayClick}>
      <div className={modalClasses} role="dialog" aria-modal="true" aria-labelledby={title ? "modal-title" : undefined}>
        {(title || showCloseButton) && (
          <div className="modal__header">
            {title && (
              <h2 id="modal-title" className="modal__title">{title}</h2>
            )}
            {showCloseButton && (
              <button
                className="modal__close"
                onClick={onClose}
                aria-label="Zatvori modal"
              >
                ×
              </button>
            )}
          </div>
        )}

        <div className="modal__content">
          {children}
        </div>

        {footer && (
          <div className="modal__footer">
            {footer}
          </div>
        )}
      </div>
    </div>
  )
}

export default Modal

