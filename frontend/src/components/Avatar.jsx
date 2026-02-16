import React from 'react'
import './Avatar.css'

/**
 * Avatar iz DiceBear API-ja (besplatan, bez API ključa).
 * @see https://www.dicebear.com/
 */
const DICEBEAR_BASE = 'https://api.dicebear.com/7.x/avataaars/svg'
const DEFAULT_SEED = 'unknown'

/** 20 fiksnih ikonica – korisnik bira broj 1–20 (ili prazno = default po emailu) */
export const PRESET_AVATAR_SEEDS = [
  'avatar_luna', 'avatar_max', 'avatar_felix', 'avatar_aneka', 'avatar_kiki',
  'avatar_mia', 'avatar_leo', 'avatar_zoe', 'avatar_finn', 'avatar_ella',
  'avatar_noah', 'avatar_ava', 'avatar_leon', 'avatar_lina', 'avatar_elias',
  'avatar_nora', 'avatar_ben', 'avatar_emma', 'avatar_luca', 'avatar_sophie'
]

const PRESET_COUNT = PRESET_AVATAR_SEEDS.length

/**
 * Vraća seed za DiceBear od korisnika: ako ima avatar_seed 1–20, preset; inače email.
 */
export const getAvatarSeedForKorisnik = (korisnik) => {
  if (!korisnik) return DEFAULT_SEED
  const v = korisnik.avatar_seed
  if (v != null && v !== '') {
    const idx = parseInt(v, 10)
    if (idx >= 1 && idx <= PRESET_COUNT) return PRESET_AVATAR_SEEDS[idx - 1]
  }
  return korisnik.email || String(korisnik.idKorisnik || DEFAULT_SEED)
}

export const getAvatarUrl = (seed, size = 64) => {
  const s = String(seed || DEFAULT_SEED).trim() || DEFAULT_SEED
  const params = new URLSearchParams({ seed: s, size: String(size) })
  return `${DICEBEAR_BASE}?${params.toString()}`
}

/**
 * @param {string} seed - email, idKorisnik ili drugi identifikator
 * @param {number} size - širina/visina u px
 * @param {string} className - dodatne CSS klase
 * @param {string} alt - alt tekst za sliku
 */
const Avatar = ({ seed, size = 64, className = '', alt = 'Avatar' }) => {
  const url = getAvatarUrl(seed, size)
  return (
    <img
      src={url}
      alt={alt}
      className={`avatar ${className}`.trim()}
      width={size}
      height={size}
      loading="lazy"
    />
  )
}

export default Avatar
