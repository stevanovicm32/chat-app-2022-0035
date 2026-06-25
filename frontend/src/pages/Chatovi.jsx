import React, { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'
import { sanitizeMessage } from '../utils/sanitize'
import { Avatar, Button, Card, Input, Modal, getAvatarSeedForKorisnik, PRESET_AVATAR_SEEDS } from '../components'
import './Chatovi.css'

const Chatovi = () => {
  const navigate = useNavigate()
  const { user, logout, refreshUser } = useAuth()
  const [chatovi, setChatovi] = useState([])
  const [filteredChats, setFilteredChats] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [searchTerm, setSearchTerm] = useState('')
  const [lastUpdated, setLastUpdated] = useState(null)
  const [selectedChat, setSelectedChat] = useState(null)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [isRefreshing, setIsRefreshing] = useState(false)
  const [messages, setMessages] = useState([])
  const [messagesLoading, setMessagesLoading] = useState(false)
  const [messageText, setMessageText] = useState('')
  const [messageError, setMessageError] = useState('')
  const [messageSending, setMessageSending] = useState(false)
  const [messageDeletingId, setMessageDeletingId] = useState(null)
  const [gifModalOpen, setGifModalOpen] = useState(false)
  const [gifSearch, setGifSearch] = useState('')
  const [gifResults, setGifResults] = useState([])
  const [gifLoading, setGifLoading] = useState(false)
  const [gifError, setGifError] = useState('')
  const [allUsers, setAllUsers] = useState([])
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false)
  const [selectedUserIds, setSelectedUserIds] = useState([])
  const [createLoading, setCreateLoading] = useState(false)
  const [createError, setCreateError] = useState('')
  const [deletingChat, setDeletingChat] = useState(false)
  const [chatActionError, setChatActionError] = useState('')
  const [isProfileModalOpen, setIsProfileModalOpen] = useState(false)
  const [profileForm, setProfileForm] = useState({ email: '', avatar_seed: '' })
  const [profileError, setProfileError] = useState('')
  const [profileSuccess, setProfileSuccess] = useState('')
  const [profileLoading, setProfileLoading] = useState(false)
  const [passwordForm, setPasswordForm] = useState({
    staraLozinka: '',
    novaLozinka: '',
    novaLozinkaPotvrda: ''
  })
  const [passwordError, setPasswordError] = useState('')
  const [passwordSuccess, setPasswordSuccess] = useState('')
  const [passwordLoading, setPasswordLoading] = useState(false)

  useEffect(() => {
    fetchChatovi({ showLoader: true })
    const intervalId = setInterval(() => {
      fetchChatovi()
    }, 45000)

    return () => clearInterval(intervalId)
  }, [])

  useEffect(() => {
    const loadUsers = async () => {
      try {
        const response = await axios.get('/api/korisnik')
        if (response.data.success) {
          const filtered = (response.data.data || []).filter(
            (korisnik) => korisnik.uloga?.idUloga !== 1
          )
          setAllUsers(filtered)
        }
      } catch (err) {
        console.error('Error fetching korisnici:', err)
      }
    }

    loadUsers()
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

  useEffect(() => {
    if (user?.idKorisnik) {
      setSelectedUserIds((prev) =>
        prev.includes(user.idKorisnik) ? prev : [user.idKorisnik, ...prev]
      )
    }
  }, [user])

  useEffect(() => {
    if (!isModalOpen) {
      setMessages([])
      setMessageText('')
      setMessageError('')
      setMessageSending(false)
    }
  }, [isModalOpen])

  useEffect(() => {
    setProfileForm((prev) => ({
      ...prev,
      email: user?.email || '',
      avatar_seed: user?.avatar_seed ?? ''
    }))
  }, [user])

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

  const suspendedUntil = user?.suspendovan ? new Date(user.suspendovan) : null
  const isSuspended =
    suspendedUntil && suspendedUntil.getTime() > new Date().getTime()
  const suspendedLabel = suspendedUntil
    ? suspendedUntil.toLocaleString('sr-RS', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    : ''

  const roleId = Number(user?.idUloga ?? user?.uloga?.idUloga ?? 0)
  const isAdmin = roleId === 1
  const isModerator = roleId === 3

  const handleCreateChat = () => {
    if (isSuspended) {
      setCreateError('Ne možete kreirati chat dok ste suspendovani')
      return
    }

    setCreateError('')
    setIsCreateModalOpen(true)
  }

  const openProfileModal = () => {
    setProfileError('')
    setProfileSuccess('')
    setPasswordError('')
    setPasswordSuccess('')
    setIsProfileModalOpen(true)
  }

  const closeProfileModal = () => {
    setIsProfileModalOpen(false)
    setProfileError('')
    setProfileSuccess('')
    setPasswordError('')
    setPasswordSuccess('')
    setProfileForm({ email: user?.email || '', avatar_seed: user?.avatar_seed ?? '' })
    setPasswordForm({
      staraLozinka: '',
      novaLozinka: '',
      novaLozinkaPotvrda: ''
    })
  }

  const handleProfileUpdate = async () => {
    if (!profileForm.email) {
      setProfileError('Email ne može biti prazan')
      return
    }

    const emailChanged = profileForm.email !== user?.email
    const avatarChanged = (profileForm.avatar_seed || '') !== (user?.avatar_seed ?? '')
    if (!emailChanged && !avatarChanged) {
      setProfileError('Nije bilo promena')
      return
    }

    setProfileLoading(true)
    setProfileError('')
    setProfileSuccess('')
    try {
      const payload = { email: profileForm.email }
      if (avatarChanged) payload.avatar_seed = profileForm.avatar_seed.trim() || null
      const response = await axios.patch(`/api/korisnik/${user?.idKorisnik}`, payload)
      if (response.data.success) {
        setProfileSuccess(emailChanged && avatarChanged ? 'Profil i ikonica ažurirani' : avatarChanged ? 'Ikona ažurirana' : 'Email uspešno ažuriran')
        refreshUser()
      } else {
        setProfileError(response.data.message || 'Neuspešno ažuriranje profila')
      }
    } catch (err) {
      console.error('Profile update error:', err)
      setProfileError(err.response?.data?.message || 'Došlo je do greške pri ažuriranju profila')
    } finally {
      setProfileLoading(false)
    }
  }

  const handlePasswordChange = async () => {
    if (!passwordForm.staraLozinka || !passwordForm.novaLozinka || !passwordForm.novaLozinkaPotvrda) {
      setPasswordError('Popunite sva polja')
      return
    }

    if (passwordForm.novaLozinka !== passwordForm.novaLozinkaPotvrda) {
      setPasswordError('Nove lozinke se ne poklapaju')
      return
    }

    if (passwordForm.novaLozinka.length < 6) {
      setPasswordError('Nova lozinka mora imati najmanje 6 karaktera')
      return
    }

    setPasswordLoading(true)
    setPasswordError('')
    setPasswordSuccess('')
    try {
      const response = await axios.patch(`/api/korisnik/${user?.idKorisnik}/lozinka`, {
        staraLozinka: passwordForm.staraLozinka,
        novaLozinka: passwordForm.novaLozinka,
        novaLozinka_confirmation: passwordForm.novaLozinkaPotvrda
      })
      if (response.data.success) {
        setPasswordSuccess('Lozinka uspešno promenjena')
        setPasswordForm({
          staraLozinka: '',
          novaLozinka: '',
          novaLozinkaPotvrda: ''
        })
      } else {
        setPasswordError(response.data.message || 'Neuspešna promena lozinke')
      }
    } catch (err) {
      console.error('Password change error:', err)
      setPasswordError(err.response?.data?.message || 'Došlo je do greške pri promeni lozinke')
    } finally {
      setPasswordLoading(false)
    }
  }

  const handlePanelNavigation = () => {
    if (isAdmin) {
      navigate('/admin')
    } else if (isModerator) {
      navigate('/moderator')
    }
  }

  const fetchMessages = async (chatId) => {
    setMessagesLoading(true)
    setMessageError('')

    try {
      const response = await axios.get('/api/poruka', {
        params: { idChat: chatId }
      })

      if (response.data.success) {
        setMessages(response.data.data || [])
      } else {
        setMessageError('Neuspešno učitavanje poruka')
      }
    } catch (err) {
      console.error('Message load error:', err)
      setMessageError('Došlo je do greške pri učitavanju poruka')
    } finally {
      setMessagesLoading(false)
    }
  }

  const toggleUserSelection = (idKorisnik) => {
    setSelectedUserIds((prev) => {
      if (prev.includes(idKorisnik)) {
        return prev.filter((value) => value !== idKorisnik)
      }
      return [...prev, idKorisnik]
    })
  }

  const closeCreateModal = () => {
    setIsCreateModalOpen(false)
    setCreateError('')
  }

  const openGifModal = () => {
    setGifError('')
    setGifModalOpen(true)
    if (import.meta.env.VITE_GIPHY_API_KEY) {
      searchGifs('trending')
    } else {
      setGifError('Nedostaje GIPHY API ključ. Dodaj VITE_GIPHY_API_KEY u frontend/.env (besplatan ključ na developers.giphy.com).')
    }
  }

  const closeGifModal = () => {
    setGifModalOpen(false)
    setGifResults([])
    setGifSearch('')
    setGifError('')
    setGifLoading(false)
  }

  const searchGifs = async (term = '') => {
    if (!import.meta.env.VITE_GIPHY_API_KEY) {
      setGifError('Nedostaje GIPHY API ključ')
      return
    }

    setGifLoading(true)
    setGifError('')
    try {
      const response = await fetch(
        `https://api.giphy.com/v1/gifs/search?api_key=${import.meta.env.VITE_GIPHY_API_KEY}&q=${encodeURIComponent(
          term || 'funny'
        )}&limit=18&rating=pg`
      )
      const data = await response.json()
      setGifResults(data.data || [])
    } catch (err) {
      console.error('GIF search error:', err)
      setGifError('Nije uspelo pretraživanje GIFova')
    } finally {
      setGifLoading(false)
    }
  }

  const sendGifMessage = async (url) => {
    if (!selectedChat?.idChat) return
    setMessageSending(true)
    setGifLoading(true)
    try {
      const response = await axios.post(`/api/chat/${selectedChat.idChat}/poruka`, {
        tekst: url
      })
      if (response.data.success) {
        setMessageText('')
        fetchMessages(selectedChat.idChat)
        closeGifModal()
      } else {
        setGifError('Neuspešno slanje GIF poruke')
      }
    } catch (err) {
      console.error('GIF send error:', err)
      setGifError(err.response?.data?.message || 'Došlo je do greške')
    } finally {
      setGifLoading(false)
      setMessageSending(false)
    }
  }

  const handleConfirmCreateChat = async () => {
    if (!selectedUserIds.length) {
      setCreateError('Odaberite bar jednog korisnika')
      return
    }

    const uniqueIds = Array.from(new Set(selectedUserIds))
    if (!uniqueIds.includes(user?.idKorisnik)) {
      uniqueIds.unshift(user.idKorisnik)
    }

    setCreateLoading(true)
    setCreateError('')
    try {
      const response = await axios.post('/api/chat', {
        idKorisnici: uniqueIds
      })

      if (response.data.success) {
        fetchChatovi({ showLoader: true })
        closeCreateModal()
      } else {
        setCreateError('Neuspešno kreiranje chata')
      }
    } catch (err) {
      console.error('Chat creation error:', err)
      setCreateError('Došlo je do greške pri kreiranju chata')
    } finally {
      setCreateLoading(false)
    }
  }

  const handleDeleteChat = async () => {
    if (!selectedChat?.idChat) return

    setChatActionError('')
    setDeletingChat(true)
    try {
      const response = await axios.delete(`/api/chat/${selectedChat.idChat}`)
      if (response.data.success) {
        fetchChatovi({ showLoader: true })
        handleCloseModal()
      } else {
        setChatActionError(response.data.message || 'Neuspešno brisanje chata')
      }
    } catch (err) {
      console.error('Chat delete error:', err)
      setChatActionError(err.response?.data?.message || 'Došlo je do greške pri brisanju chata')
    } finally {
      setDeletingChat(false)
    }
  }

  const canDeleteMessage = (msg) =>
    Boolean(msg) &&
    (msg.idKorisnik === user?.idKorisnik || isModerator)

  const handleDeleteMessage = async (msg) => {
    const messageId = msg.idPoruka ?? msg.id
    if (!messageId) return

    setMessageError('')
    setMessageDeletingId(messageId)
    try {
      const response = await axios.delete(`/api/poruka/${messageId}`)
      if (response.data.success) {
        fetchMessages(selectedChat?.idChat)
      } else {
        setMessageError(response.data.message || 'Neuspešno brisanje poruke')
      }
    } catch (err) {
      console.error('Message delete error:', err)
      setMessageError(err.response?.data?.message || 'Došlo je do greške pri brisanju poruke')
    } finally {
      setMessageDeletingId(null)
    }
  }

  const handleSendMessage = async () => {
    if (!messageText.trim()) {
      setMessageError('Unesite tekst poruke')
      return
    }

    if (!selectedChat?.idChat) {
      setMessageError('Neuspešno slanje poruke')
      return
    }

    if (isSuspended) {
      setMessageError('Ne možete slati poruke dok ste suspendovani')
      return
    }

    setMessageSending(true)
    setMessageError('')

    try {
      const response = await axios.post(`/api/chat/${selectedChat.idChat}/poruka`, {
        tekst: messageText.trim()
      })

      if (response.data.success) {
        setMessageText('')
        fetchMessages(selectedChat.idChat)
      } else {
        setMessageError('Neuspešno slanje poruke')
      }
    } catch (err) {
      console.error('Message send error:', err)
      const apiError = err.response?.data?.message
      const validation = err.response?.data?.errors
      if (validation) {
        setMessageError(Object.values(validation).flat().join('. '))
      } else if (apiError) {
        setMessageError(apiError)
      } else {
        setMessageError('Došlo je do greške pri slanju poruke')
      }
    } finally {
      setMessageSending(false)
    }
  }

  const getParticipants = (chat) => {
    if (!chat) return []
    return chat.korisnici ?? chat.participants ?? []
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
    setChatActionError('')
    fetchMessages(chat.idChat)
  }

  const handleCloseModal = () => {
    setIsModalOpen(false)
    setSelectedChat(null)
    setMessages([])
    setMessageText('')
    setMessageError('')
    setChatActionError('')
    setMessageDeletingId(null)
    setDeletingChat(false)
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
  const participantsList = getParticipants(selectedChat)
  const getParticipantSummary = (chat) => {
    const participants = getParticipants(chat)
    if (!participants.length) return 'Nema učesnika'
    return participants
      .map(
        (participant) =>
          participant.email || participant.ime || `Korisnik ${participant.idKorisnik ?? participant.id ?? 'N/A'}`
      )
      .join(', ')
  }
  const displayedParticipants = getParticipantSummary(selectedChat)
  const displayedMessages = [...messages].reverse()
  const isParticipantInChat = !!participantsList.find(
    (participant) => participant.idKorisnik === user?.idKorisnik
  )

  return (
    <div className="chatovi-container">
      <header className="header">
        <h1>Moji Chatovi</h1>
        <div className="user-info">
          <Avatar seed={getAvatarSeedForKorisnik(user)} size={36} className="header-avatar" alt="" />
          <button className="profile-trigger" onClick={openProfileModal}>
            Prijavljen kao: <strong>{user?.email}</strong>
          </button>
          <button onClick={handleLogout} className="logout-button">
            Odjavi se
          </button>
        </div>
      </header>

      <main className="main-content">
        {isSuspended && suspendedLabel && (
          <div className="suspend-banner">
            <strong>Upozorenje:</strong> suspendovani ste do {suspendedLabel}; ne možete kreirati chatove niti slati poruke.
          </div>
        )}
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
              <div className="control-actions">
                <Button
                  variant="primary"
                  size="medium"
                  onClick={handleCreateChat}
                  disabled={isSuspended}
                >
                  Novi chat
                </Button>
                {(isAdmin || isModerator) && (
                  <Button variant="secondary" size="medium" onClick={handlePanelNavigation}>
                    {isAdmin ? 'Admin panel' : 'Moderator panel'}
                  </Button>
                )}
                <Button
                  variant="outline"
                  size="medium"
                  onClick={handleManualRefresh}
                  disabled={isRefreshing}
                >
                  {isRefreshing ? 'Refreshing...' : 'Refresh chat list'}
                </Button>
              </div>
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
                      <strong>Učesnici:</strong> {getParticipantSummary(chat)}
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
          <div className="modal-participants">
            <strong>Učesnici:</strong>
            {participantsList.length ? (
              <div className="participant-list">
                {participantsList.map((participant) => (
                  <span key={participant.idKorisnik ?? participant.id} className="participant-chip">
                    <Avatar seed={getAvatarSeedForKorisnik(participant)} size={28} className="participant-avatar" alt="" />
                    {participant.email || participant.ime || 'Nepoznato'}
                    {participant.uloga?.naziv ? ` (${participant.uloga.naziv})` : ''}
                  </span>
                ))}
              </div>
            ) : (
              <p className="participant-placeholder">Nema dostupnih učesnika</p>
            )}
            {chatActionError && <p className="chat-action-error">{chatActionError}</p>}
            {isParticipantInChat && (
              <div className="participant-actions">
                <Button
                  variant="danger"
                  size="small"
                  onClick={handleDeleteChat}
                  disabled={deletingChat}
                >
                  {deletingChat ? 'Brisanje...' : 'Obriši chat'}
                </Button>
              </div>
            )}
          </div>
          <p>
            <strong>Summary:</strong> {selectedChat?.summary || 'No summary available'}
          </p>
          <div className="message-section">
            <h3>Poruke</h3>
            {messagesLoading ? (
              <p className="message-loading">Učitavanje poruka...</p>
            ) : displayedMessages.length ? (
              <div className="message-list">
                {displayedMessages.map((msg) => {
                  const isGif = String(msg.tekst || '').startsWith('https://') &&
                    (msg.tekst.includes('.gif') || msg.tekst.includes('giphy.com'));
                  return (
                    <div key={msg.idPoruka ?? msg.id} className="message-item">
                      <Avatar seed={getAvatarSeedForKorisnik(msg.korisnik)} size={40} className="message-avatar" alt="" />
                      <div className="message-item-body">
                        <div className="message-meta">
                          <span className="message-author">
                            {msg.korisnik?.email || 'Nepoznat'}
                          </span>
                          <span className="message-time">
                            {msg.created_at
                              ? new Date(msg.created_at).toLocaleString()
                              : '-'}
                          </span>
                        </div>
                        {isGif ? (
                          <div className="message-gif">
                            <img src={msg.tekst} alt="GIF poruka" />
                          </div>
                        ) : (
                          <p className="message-text">{sanitizeMessage(msg.tekst)}</p>
                        )}
                        {canDeleteMessage(msg) && (
                          <button
                            className="message-delete"
                            onClick={() => handleDeleteMessage(msg)}
                            disabled={messageDeletingId === (msg.idPoruka ?? msg.id)}
                          >
                            {messageDeletingId === (msg.idPoruka ?? msg.id)
                              ? 'Brisanje...'
                              : 'Obriši poruku'}
                          </button>
                        )}
                      </div>
                    </div>
                  )
                })}
              </div>
            ) : (
              <p className="message-placeholder">Nema poruka u ovom chatu.</p>
            )}
            {messageError && <p className="message-error">{messageError}</p>}
                <div className="message-form">
                  <textarea
                    value={messageText}
                    onChange={(e) => setMessageText(e.target.value)}
                    placeholder="Napiši poruku..."
                    rows={3}
                    disabled={isSuspended}
                  />
                  <div className="message-actions">
                    <Button
                      variant="secondary"
                      size="small"
                      onClick={openGifModal}
                      disabled={!selectedChat?.idChat}
                    >
                      Pošalji GIF
                    </Button>
                    <Button
                      variant="primary"
                      size="small"
                      onClick={handleSendMessage}
                      disabled={messageSending || isSuspended}
                    >
                      {messageSending ? 'Šaljem...' : 'Pošalji'}
                    </Button>
                  </div>
                </div>
          </div>
        </Modal>
        <Modal
          isOpen={isCreateModalOpen}
          onClose={closeCreateModal}
          title="Novi chat"
          footer={
            <>
              <Button variant="secondary" size="small" onClick={closeCreateModal}>
                Otkaži
              </Button>
              <Button
                variant="primary"
                size="small"
                onClick={handleConfirmCreateChat}
                disabled={createLoading}
              >
                {createLoading ? 'Kreiranje...' : 'Kreiraj chat'}
              </Button>
            </>
          }
        >
          {createError && <p className="modal-error">{createError}</p>}
          <div className="user-selection">
            {!allUsers.length && <p>Učitavanje korisnika...</p>}
            {allUsers.map((korisnik) => (
              <label key={korisnik.idKorisnik} className="user-item">
                <input
                  type="checkbox"
                  checked={selectedUserIds.includes(korisnik.idKorisnik)}
                  onChange={() => toggleUserSelection(korisnik.idKorisnik)}
                />
                <Avatar seed={getAvatarSeedForKorisnik(korisnik)} size={36} className="user-item-avatar" alt="" />
                <span>
                  {korisnik.email}
                  {korisnik.uloga?.naziv ? ` (${korisnik.uloga.naziv})` : ''}
                </span>
              </label>
            ))}
          </div>
        </Modal>
        <Modal
          isOpen={isProfileModalOpen}
          onClose={closeProfileModal}
          title="Moj profil"
          footer={
            <Button variant="secondary" size="small" onClick={closeProfileModal}>
              Zatvori
            </Button>
          }
        >
          <div className="profile-section">
            <h3>Podaci</h3>
            <Input
              label="Email"
              type="email"
              value={profileForm.email}
              onChange={(e) => setProfileForm((p) => ({ ...p, email: e.target.value }))}
              placeholder="unesi novi email"
            />
            {profileError && <p className="profile-error">{profileError}</p>}
            {profileSuccess && <p className="profile-success">{profileSuccess}</p>}
            <div className="profile-actions">
              <Button
                variant="primary"
                size="small"
                onClick={handleProfileUpdate}
                disabled={profileLoading}
              >
                {profileLoading ? 'Ažuriram...' : 'Sačuvaj'}
              </Button>
            </div>
          </div>
          <div className="profile-section">
            <h3>Ikonica (avatar)</h3>
            <p className="profile-hint">Izaberi jednu od 20 ikonica. Klik na „Default” koristi ikonicu po emailu.</p>
            <div className="profile-avatar-grid">
              <button
                type="button"
                className={`profile-avatar-option ${!profileForm.avatar_seed ? 'profile-avatar-option--selected' : ''}`}
                onClick={() => setProfileForm((p) => ({ ...p, avatar_seed: '' }))}
                title="Default (email)"
              >
                <Avatar seed={profileForm.email} size={48} alt="" />
                <span className="profile-avatar-label">Default</span>
              </button>
              {PRESET_AVATAR_SEEDS.map((seed, i) => {
                const value = String(i + 1)
                const selected = profileForm.avatar_seed === value
                return (
                  <button
                    key={seed}
                    type="button"
                    className={`profile-avatar-option ${selected ? 'profile-avatar-option--selected' : ''}`}
                    onClick={() => setProfileForm((p) => ({ ...p, avatar_seed: value }))}
                    title={`Ikonica ${i + 1}`}
                  >
                    <Avatar seed={seed} size={48} alt="" />
                  </button>
                )
              })}
            </div>
          </div>
          <div className="profile-section">
            <h3>Promena lozinke</h3>
            <Input
              label="Trenutna lozinka"
              type="password"
              value={passwordForm.staraLozinka}
              onChange={(e) => setPasswordForm((prev) => ({ ...prev, staraLozinka: e.target.value }))}
              placeholder="Unesi trenutnu lozinku"
            />
            <Input
              label="Nova lozinka"
              type="password"
              value={passwordForm.novaLozinka}
              onChange={(e) => setPasswordForm((prev) => ({ ...prev, novaLozinka: e.target.value }))}
              placeholder="Unesi novu lozinku"
            />
            <Input
              label="Potvrdi novu lozinku"
              type="password"
              value={passwordForm.novaLozinkaPotvrda}
              onChange={(e) =>
                setPasswordForm((prev) => ({ ...prev, novaLozinkaPotvrda: e.target.value }))
              }
              placeholder="Ponovo unesi novu lozinku"
            />
            {passwordError && <p className="profile-error">{passwordError}</p>}
            {passwordSuccess && <p className="profile-success">{passwordSuccess}</p>}
            <div className="profile-actions">
              <Button
                variant="primary"
                size="small"
                onClick={handlePasswordChange}
                disabled={passwordLoading}
              >
                {passwordLoading ? 'Menjam...' : 'Promeni lozinku'}
              </Button>
            </div>
          </div>
        </Modal>
        <Modal
          isOpen={gifModalOpen}
          onClose={closeGifModal}
          title="Izaberi GIF"
          footer={null}
        >
          <Input
            label="Pretraga GIFova"
            value={gifSearch}
            onChange={(e) => setGifSearch(e.target.value)}
            placeholder="Upiši temu"
          />
          <Button
            variant="primary"
            size="small"
            onClick={() => searchGifs(gifSearch)}
            disabled={gifLoading}
          >
            {gifLoading ? 'Tražim...' : 'Pretraži'}
          </Button>
          {gifError && <p className="profile-error">{gifError}</p>}
          <div className="gif-grid">
            {gifLoading ? (
              <p className="message-loading">Učitavanje GIFova...</p>
            ) : (
              gifResults.map((gif) => {
                const url = gif.images?.fixed_width?.url || gif.images?.downsized?.url || gif.images?.original?.url
                if (!url) return null
                return (
                  <button
                    key={gif.id}
                    type="button"
                    className="gif-card"
                    onClick={() => sendGifMessage(url)}
                  >
                    <img src={url} alt={gif.title || 'GIF'} loading="lazy" />
                  </button>
                )
              })
            )}
          </div>
        </Modal>
      </main>
    </div>
  )
}

export default Chatovi