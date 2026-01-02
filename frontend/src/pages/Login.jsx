import React, { useState, useEffect } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import './Login.css'

const Login = () => {
  const [email, setEmail] = useState('')
  const [lozinka, setLozinka] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const { login, user } = useAuth()
  const navigate = useNavigate()

  useEffect(() => {
    if (user) {
      navigate('/chatovi')
    }
  }, [user, navigate])

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)

    const result = await login(email, lozinka)
    
    if (result.success) {
      navigate('/chatovi')
    } else {
      setError(result.message)
    }
    
    setLoading(false)
  }

  return (
    <div className="login-container">
      <div className="login-box">
        <h1>Chat Aplikacija</h1>
        <h2>Prijava</h2>
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
              placeholder="Unesite lozinku"
            />
          </div>
          <button type="submit" disabled={loading} className="login-button">
            {loading ? 'Prijavljivanje...' : 'Prijavi se'}
          </button>
          <div className="register-link">
            Nemaš nalog? <Link to="/register">Registruj se</Link>
          </div>
        </form>
      </div>
    </div>
  )
}

export default Login

