import React, { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'
import { Avatar, getAvatarSeedForKorisnik } from '../components'
import './AdminPanel.css'

const ModeratorPanel = () => {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [korisnici, setKorisnici] = useState([])
  const [loading, setLoading] = useState(true)
  const [suspendingId, setSuspendingId] = useState(null)
  const [feedback, setFeedback] = useState('')

  const isModerator = user?.idUloga === 3

  useEffect(() => {
    fetchKorisnici()
  }, [])

  const fetchKorisnici = async () => {
    setLoading(true)
    try {
      const response = await axios.get('/api/korisnik')
      if (response.data.success) {
        setKorisnici(response.data.data)
      }
    } catch (error) {
      console.error('Error fetching korisnici:', error)
      setFeedback('Neuspešno učitavanje korisnika')
    } finally {
      setLoading(false)
    }
  }

  const handleSuspend = async (id) => {
    setFeedback('')
    setSuspendingId(id)
    try {
      const response = await axios.patch(`/api/korisnik/${id}/suspend`)
      if (response.data.success) {
        setFeedback(`Korisnik je suspendovan do ${new Date(response.data.data.suspendovan).toLocaleString('sr-RS')}`)
        fetchKorisnici()
      } else {
        setFeedback(response.data.message || 'Neuspešna suspenzija')
      }
    } catch (error) {
      console.error('Suspend error:', error)
      setFeedback(error.response?.data?.message || 'Došlo je do greške pri suspendovanju')
    } finally {
      setSuspendingId(null)
    }
  }

  if (!isModerator) {
    return (
      <div className="admin-container">
        <div className="access-denied">
          <h2>Pristup odbijen</h2>
          <p>Samo moderatori mogu pristupiti ovoj stranici.</p>
        </div>
      </div>
    )
  }

  return (
    <div className="admin-container">
      <header className="header">
        <h1>Moderator Panel</h1>
        <div className="user-info">
          <span>Moderator: {user?.email}</span>
          <button onClick={() => navigate('/chatovi')} className="logout-button">
            Nazad
          </button>
          <button onClick={logout} className="logout-button">
            Odjavi se
          </button>
        </div>
      </header>

      <main className="main-content">
        <div className="tabs">
          <button className="tab active">Korisnici</button>
        </div>

        <div className="content">
          {feedback && <p className="panel-feedback">{feedback}</p>}
          {loading ? (
            <div className="loading">Učitavanje korisnika...</div>
          ) : (
            <div className="table-container">
              <h2>Lista korisnika</h2>
              <table className="data-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Uloga</th>
                    <th>Suspendovan</th>
                    <th>Akcija</th>
                  </tr>
                </thead>
                <tbody>
                  {korisnici.map((korisnik) => (
                    <tr key={korisnik.idKorisnik}>
                      <td>{korisnik.idKorisnik}</td>
                      <td>
                        <Avatar seed={getAvatarSeedForKorisnik(korisnik)} size={32} alt="" />
                        <span className="admin-email-cell">{korisnik.email}</span>
                      </td>
                      <td>{korisnik.uloga?.naziv || 'N/A'}</td>
                      <td>
                        {korisnik.suspendovan
                          ? new Date(korisnik.suspendovan).toLocaleString('sr-RS')
                          : 'Ne'}
                      </td>
                      <td>
                        <button
                          className="suspend-button"
                          onClick={() => handleSuspend(korisnik.idKorisnik)}
                          disabled={suspendingId === korisnik.idKorisnik}
                        >
                          {suspendingId === korisnik.idKorisnik
                            ? 'Suspenzija...'
                            : 'Suspenduj na 3 dana'}
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </main>
    </div>
  )
}

export default ModeratorPanel

