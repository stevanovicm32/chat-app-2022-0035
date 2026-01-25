import React, { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'
import './Chatovi.css'

const Chatovi = () => {
  const { user, logout } = useAuth()
  const [chatovi, setChatovi] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchChatovi()
  }, [])

  const fetchChatovi = async () => {
    try {
      const response = await axios.get('/api/chat')
      if (response.data.success) {
        setChatovi(response.data.data)
      }
    } catch (error) {
      setError('Greška pri učitavanju chatova')
      console.error('Error fetching chatovi:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleLogout = () => {
    logout()
  }

  return (
    <div className="chatovi-container">
      <header className="header">
        <h1>Moji Chatovi</h1>
        <div className="user-info">
          <span>Prijavljen kao: {user?.email}</span>
          <button onClick={handleLogout} className="logout-button">
            Odjavi se
          </button>
        </div>
      </header>

      <main className="main-content">
        {loading ? (
          <div className="loading">Učitavanje chatova...</div>
        ) : error ? (
          <div className="error">{error}</div>
        ) : chatovi.length === 0 ? (
          <div className="empty-state">
            <p>Nemate chatova. Kreirajte novi chat!</p>
          </div>
        ) : (
          <div className="chatovi-grid">
            {chatovi.map((chat) => (
              <div key={chat.idChat} className="chat-card">
                <h3>Chat #{chat.idChat}</h3>
                <p>ID: {chat.idChat}</p>
                <div className="chat-actions">
                  <button className="view-button">Otvori Chat</button>
                </div>
              </div>
            ))}
          </div>
        )}
      </main>
    </div>
  )
}

export default Chatovi

