import React, { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'
import { Button, Card, Input, Modal } from '../components'
import './Chatovi.css'

const Chatovi = () => {
  const { user, logout } = useAuth()
  const [chatovi, setChatovi] = useState([])
  const [filteredChats, setFilteredChats] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [searchTerm, setSearchTerm] = useState('')
  const [lastUpdated, setLastUpdated] = useState(null)
  const [selectedChat, setSelectedChat] = useState(null)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isRefreshing, setIsRefreshing] = useState(false)

  useEffect(() => {
    fetchChatovi({ showLoader: true })
    const intervalId = setInterval(() => {
      fetchChatovi()
    }, 45000)

    return () => clearInterval(intervalId)
  }, [])

  useEffect(() => {
    const sanitizedSearch = searchTerm.trim().toLowerCase()
    if (!sanitizedSearch) {
      setFilteredChats(chatovi)
      return
    }

    setFilteredChats(
      chatovi.filter((chat) => {
        const title = (chat?.name || chat?.title || `Chat #${chat.idChat}`)
          .toString()
          .toLowerCase()
        const status = (chat?.status || '').toString().toLowerCase()

        return (
          title.includes(sanitizedSearch) ||
          `${chat.idChat}`.startsWith(sanitizedSearch) ||
          status.includes(sanitizedSearch)
        )
      })
    )
  }, [searchTerm, chatovi])

  const fetchChatovi = async ({ showLoader = false } = {}) => {
    if (showLoader) {
      setLoading(true)
    }

    setError('')

    try {
      const response = await axios.get('/api/chat')
      if (response.data.success) {
        setChatovi(response.data.data || [])
        setLastUpdated(new Date())
      } else {
        setError('Chatovi trenutno nisu dostupni')
      }
    } catch (error) {
      setError('Greška pri učitavanju chatova')
      console.error('Error fetching chatovi:', error)
    } finally {
      if (showLoader) {
        setLoading(false)
      }
      setIsRefreshing(false)
    }
  }

  const handleLogout = () => {
    logout()
  }

  const handleManualRefresh = () => {
    setIsRefreshing(true)
    fetchChatovi()
  }

  const resolveStatus = (chat) => {
    const rawStatus = (chat?.status || '').toString().toLowerCase()
    if (rawStatus) return rawStatus

    if (chat?.isActive) return 'active'
    return chat?.idChat % 2 === 0 ? 'active' : 'waiting'
  }

  const getStatusLabel = (chat) => {
    const status = resolveStatus(chat)
    if (status === 'active') return 'Active participants'
    if (status === 'waiting' || status === 'pending') return 'Waiting for response'
    return status.charAt(0).toUpperCase() + status.slice(1)
  }

  const handleOpenChatDetails = (chat) => {
    setSelectedChat(chat)
    setIsModalOpen(true)
  }

  const handleCloseModal = () => {
    setIsModalOpen(false)
    setSelectedChat(null)
  }

  const totalChats = chatovi.length
  const activeChats = chatovi.filter((chat) => resolveStatus(chat) === 'active').length
  const waitingChats = totalChats - activeChats
  const visibleChats = filteredChats
  const lastUpdatedLabel = lastUpdated
    ? new Date(lastUpdated).toLocaleString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        day: '2-digit',
        month: 'short'
      })
    : 'Not synced yet'

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
        ) : (
          <>
            <section className="controls">
              <Input
                label="Pretraži chatove"
                placeholder="Ime, ID ili status"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="search-input"
              />
              <Button
                variant="outline"
                size="medium"
                onClick={handleManualRefresh}
                disabled={isRefreshing}
              >
                {isRefreshing ? 'Refreshing...' : 'Refresh chat list'}
              </Button>
            </section>

            <section className="stats-grid">
              <div className="stat-card">
                <span>Total chats</span>
                <strong>{totalChats}</strong>
              </div>
              <div className="stat-card">
                <span>Active threads</span>
                <strong>{activeChats}</strong>
              </div>
              <div className="stat-card">
                <span>Waiting replies</span>
                <strong>{waitingChats}</strong>
              </div>
              <div className="stat-card">
                <span>Visible after filter</span>
                <strong>{visibleChats.length}</strong>
              </div>
            </section>

            <div className="meta-row">
              <span>Last synced: {lastUpdatedLabel}</span>
              {isRefreshing && (
                <span className="refresh-indicator">Auto-refreshing every 45s</span>
              )}
            </div>

            {visibleChats.length === 0 ? (
              <div className="empty-state">
                <p>Nemate odgovarajućih chatova. Promenite kriterijum pretrage.</p>
              </div>
            ) : (
              <div className="chatovi-grid">
                {visibleChats.map((chat) => (
                  <Card
                    key={chat.idChat}
                    title={chat?.name || `Chat #${chat.idChat}`}
                    subtitle={`ID: ${chat.idChat}`}
                    hoverable
                    variant="elevated"
                    footer={
                      <Button variant="secondary" size="small" onClick={() => handleOpenChatDetails(chat)}>
                        View details
                      </Button>
                    }
                  >
                    <p className="chat-line">
                      Status: <strong>{getStatusLabel(chat)}</strong>
                    </p>
                    <p className="chat-line">
                      Participants: {chat?.participants?.length ?? 'N/A'}
                    </p>
                    <p className="chat-line">
                      Last message: {chat?.lastMessage || 'Not available'}
                    </p>
                  </Card>
                ))}
              </div>
            )}
          </>
        )}

        <Modal
          isOpen={isModalOpen}
          onClose={handleCloseModal}
          title={selectedChat ? `Chat #${selectedChat.idChat}` : 'Chat details'}
          footer={
            <Button variant="secondary" size="small" onClick={handleCloseModal}>
              Close
            </Button>
          }
        >
          <p>
            <strong>ID:</strong> {selectedChat?.idChat ?? 'N/A'}
          </p>
          <p>
            <strong>Status:</strong> {selectedChat ? getStatusLabel(selectedChat) : 'N/A'}
          </p>
          <p>
            <strong>Participants:</strong> {selectedChat?.participants?.length ?? 'N/A'}
          </p>
          <p>
            <strong>Summary:</strong> {selectedChat?.summary || 'No summary available'}
          </p>
        </Modal>
      </main>
    </div>
  )
}

export default Chatovi
