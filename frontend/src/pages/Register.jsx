import React, { useState, useEffect } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'
import './Register.css'

const Register = () => {
  const [email, setEmail] = useState('')
  const [lozinka, setLozinka] = useState('')
  const [potvrdaLozinke, setPotvrdaLozinke] = useState('')
  const [idUloga, setIdUloga] = useState('')
  const [uloge, setUloge] = useState([])
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const { register, user } = useAuth()
  const navigate = useNavigate()

  useEffect(() => {
    if (user) {
      navigate('/chatovi')
    }
    // Učitaj uloge
    axios.get('/api/uloga')
      .then(response => {
        if (response.data.success) {
          setUloge(response.data.data)
          // Podrazumevano postavi na User (idUloga = 2)
          setIdUloga('2')
        }
      })
      .catch(error => {
        console.error('Error fetching roles:', error)
      })
  }, [user, navigate])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    
    if (lozinka !== potvrdaLozinke) {
      setError('Lozinke se ne poklapaju')
      return
    }

    if (lozinka.length < 6) {
      setError('Lozinka mora imati najmanje 6 karaktera')
      return
    }

    setLoading(true)

    const result = await register(email, lozinka, parseInt(idUloga))
    
    if (result.success) {
      navigate('/chatovi')
    } else {
      setError(result.message || 'Greška pri registraciji')
    }
    
    setLoading(false)
  }

  return (
    <div className="register-container">
      <div className="register-box">
        <h1>Chat Aplikacija</h1>
        <h2>Registracija</h2>
        <form onSubmit={handleSubmit}>
          {error && <div className="error-message">{error}</div>}
          <div className="form-group">
            <label htmlFor="email">Email:</label>
            <input
              type="email"
              id="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              placeholder="unesite@email.com"
            />
          </div>
          <div className="form-group">
            <label htmlFor="lozinka">Lozinka:</label>
            <input
              type="password"
              id="lozinka"
              value={lozinka}
              onChange={(e) => setLozinka(e.target.value)}
              required
              minLength={6}
              placeholder="Najmanje 6 karaktera"
            />
          </div>
          <div className="form-group">
            <label htmlFor="potvrdaLozinke">Potvrdi lozinku:</label>
            <input
              type="password"
              id="potvrdaLozinke"
              value={potvrdaLozinke}
              onChange={(e) => setPotvrdaLozinke(e.target.value)}
              required
              minLength={6}
              placeholder="Ponovi lozinku"
            />
          </div>
          <div className="form-group">
            <label htmlFor="idUloga">Uloga:</label>
            <select
              id="idUloga"
              value={idUloga}
              onChange={(e) => setIdUloga(e.target.value)}
              required
            >
              <option value="">Izaberi ulogu</option>
              {uloge.map(uloga => (
                <option key={uloga.idUloga} value={uloga.idUloga}>
                  {uloga.naziv}
                </option>
              ))}
            </select>
          </div>
          <button type="submit" disabled={loading} className="register-button">
            {loading ? 'Registracija...' : 'Registruj se'}
          </button>
          <div className="login-link">
            Već imaš nalog? <Link to="/login">Prijavi se</Link>
          </div>
        </form>
      </div>
    </div>
  )
}

export default Register

