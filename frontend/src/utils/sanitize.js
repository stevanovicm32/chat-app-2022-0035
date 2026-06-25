/**
 * XSS zaštita — enkodiranje korisničkog unosa pre prikaza u DOM-u.
 */
export function escapeHtml(text) {
  if (text == null) return ''
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
}

export function sanitizeMessage(text) {
  return escapeHtml(text)
}
