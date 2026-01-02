import React, { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'
import './AdminPanel.css'

const AdminPanel = () => {
  const { user, logout } = useAuth()
  const [korisnici, setKorisnici] = useState([])
  const [uloge, setUloge] = useState([])
  const [loading, setLoading] = useState(true)
  const [activeTab, setActiveTab] = useState('korisnici')

  useEffect(() => {
    fetchKorisnici()
    fetchUloge()
  }, [])

  const fetchKorisnici = async () => {
    try {
      const response = await axios.get('/api/korisnik')
      if (response.data.success) {
        setKorisnici(response.data.data)
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

  const handleLogout = () => {
    logout()
  }

  const isAdmin = user?.uloga?.naziv === 'Admin'

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
        </div>

        {loading ? (
          <div className="loading">Učitavanje...</div>
        ) : (
          <div className="content">
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
                        <td>{korisnik.email}</td>
                        <td>{korisnik.uloga?.naziv || 'N/A'}</td>
                        <td>{korisnik.suspendovan || 'Ne'}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
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

