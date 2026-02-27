import React, { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'
import { Avatar, getAvatarSeedForKorisnik } from '../components'
import StatsChart from '../components/StatsChart'
import './AdminPanel.css'

const AdminPanel = () => {
  const navigate = useNavigate()
  const { user, logout } = useAuth()
  const [korisnici, setKorisnici] = useState([])
  const [uloge, setUloge] = useState([])
  const [loading, setLoading] = useState(true)
  const [activeTab, setActiveTab] = useState('korisnici')
  const [roleSelections, setRoleSelections] = useState({})
  const [actionLoadingId, setActionLoadingId] = useState(null)
  const [deleteLoadingId, setDeleteLoadingId] = useState(null)
  const [panelFeedback, setPanelFeedback] = useState('')

  useEffect(() => {
    fetchKorisnici()
    fetchUloge()
  }, [])

  const fetchKorisnici = async () => {
    try {
      const response = await axios.get('/api/korisnik')
      if (response.data.success) {
      setKorisnici(response.data.data)
      const roleMap = {}
      (response.data.data || []).forEach((korisnik) => {
        if (korisnik.uloga?.idUloga) {
          roleMap[korisnik.idKorisnik] = korisnik.uloga.idUloga
        }
      })
      setRoleSelections(roleMap)
      }
    } catch (error) {
      console.error('Error fetching korisnici:', error)
    } finally {
      setLoading(false)
    }
  }

  const fetchUloge = async () => {
    try {
      const response = await axios.get('/api/uloga')
      if (response.data.success) {
      setUloge(response.data.data)
      }
    } catch (error) {
      console.error('Error fetching uloge:', error)
    }
  }

  const handleRoleUpdate = async (id) => {
    const idUloga = roleSelections[id]
    if (!idUloga) {
      setPanelFeedback('Izaberite ulogu pre čuvanja')
      return
    }

    setPanelFeedback('')
    setActionLoadingId(id)
    try {
      const response = await axios.patch(`/api/korisnik/${id}`, { idUloga })
      if (response.data.success) {
        setPanelFeedback('Uloga uspešno promenjena')
        fetchKorisnici()
      } else {
        setPanelFeedback(response.data.message || 'Neuspešna promena uloge')
      }
    } catch (error) {
      console.error('Role update error:', error)
      setPanelFeedback(error.response?.data?.message || 'Došlo je do greške pri promeni uloge')
    } finally {
      setActionLoadingId(null)
    }
  }

  const handleDeleteUser = async (id) => {
    setPanelFeedback('')
    setDeleteLoadingId(id)
    try {
      const response = await axios.delete(`/api/korisnik/${id}`)
      if (response.data.success) {
        setPanelFeedback('Korisnik je obrisan')
        fetchKorisnici()
      } else {
        setPanelFeedback(response.data.message || 'Neuspešno brisanje korisnika')
      }
    } catch (error) {
      console.error('Delete error:', error)
      setPanelFeedback(error.response?.data?.message || 'Došlo je do greške pri brisanju korisnika')
    } finally {
      setDeleteLoadingId(null)
    }
  }

  const handleLogout = () => {
    logout()
  }

  const isAdmin =
    (user?.idUloga && Number(user.idUloga) === 1) ||
    (user?.uloga?.idUloga && Number(user.uloga.idUloga) === 1) ||
    user?.uloga?.naziv === 'Admin'

  if (!isAdmin) {
    return (
      <div className="admin-container">
        <div className="access-denied">
          <h2>Pristup odbijen</h2>
          <p>Samo administratori mogu pristupiti ovoj stranici.</p>
        </div>
      </div>
    )
  }

  return (
    <div className="admin-container">
      <header className="header">
        <h1>Admin Panel</h1>
        <div className="user-info">
          <span>Admin: {user?.email}</span>
          <button onClick={() => navigate('/chatovi')} className="logout-button">
            Nazad
          </button>
          <button onClick={handleLogout} className="logout-button">
            Odjavi se
          </button>
        </div>
      </header>

      <main className="main-content">
        <div className="tabs">
          <button
            className={activeTab === 'korisnici' ? 'tab active' : 'tab'}
            onClick={() => setActiveTab('korisnici')}
          >
            Korisnici
          </button>
          <button
            className={activeTab === 'uloge' ? 'tab active' : 'tab'}
            onClick={() => setActiveTab('uloge')}
          >
            Uloge
          </button>
          <button
            className={activeTab === 'statistika' ? 'tab active' : 'tab'}
            onClick={() => setActiveTab('statistika')}
          >
            Statistika
          </button>
        </div>

        {loading ? (
          <div className="loading">Učitavanje...</div>
        ) : (
          <div className="content">
            {panelFeedback && <p className="panel-feedback">{panelFeedback}</p>}
            {activeTab === 'korisnici' && (
              <div className="table-container">
                <h2>Lista Korisnika</h2>
                <table className="data-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Email</th>
                      <th>Uloga</th>
                      <th>Suspendovan</th>
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
                      <td>
                        <div className="role-actions">
                          <select
                            value={roleSelections[korisnik.idKorisnik] ?? korisnik.uloga?.idUloga ?? ''}
                            onChange={(e) =>
                              setRoleSelections((prev) => ({
                                ...prev,
                                [korisnik.idKorisnik]: Number(e.target.value)
                              }))
                            }
                          >
                            <option value="">Izaberi ulogu</option>
                            {uloge.map((uloga) => (
                              <option key={uloga.idUloga} value={uloga.idUloga}>
                                {uloga.naziv}
                              </option>
                            ))}
                          </select>
                          <button
                            className="suspend-button"
                            onClick={() => handleRoleUpdate(korisnik.idKorisnik)}
                            disabled={
                              actionLoadingId === korisnik.idKorisnik ||
                              !roleSelections[korisnik.idKorisnik]
                            }
                          >
                            {actionLoadingId === korisnik.idKorisnik
                              ? 'Ažuriranje...'
                              : 'Sačuvaj'}
                          </button>
                        </div>
                      </td>
                      <td>{korisnik.suspendovan || 'Ne'}</td>
                      <td>
                        <button
                          className="suspend-button"
                          onClick={() => handleDeleteUser(korisnik.idKorisnik)}
                          disabled={deleteLoadingId === korisnik.idKorisnik}
                        >
                          {deleteLoadingId === korisnik.idKorisnik ? 'Brisanje...' : 'Obriši'}
                        </button>
                      </td>
                    </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}

            {activeTab === 'statistika' && (
              <StatsChart />
            )}

            {activeTab === 'uloge' && (
              <div className="table-container">
                <h2>Lista Uloga</h2>
                <table className="data-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Naziv</th>
                    </tr>
                  </thead>
                  <tbody>
                    {uloge.map((uloga) => (
                      <tr key={uloga.idUloga}>
                        <td>{uloga.idUloga}</td>
                        <td>{uloga.naziv}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        )}
      </main>
    </div>
  )
}

export default AdminPanel

