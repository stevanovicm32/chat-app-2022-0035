import React, { createContext, useState, useContext, useEffect } from 'react'
import axios from 'axios'

const AuthContext = createContext()

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null)
  const [token, setToken] = useState(localStorage.getItem('token'))
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      fetchUser()
    } else {
      setLoading(false)
    }
  }, [token])

  const fetchUser = async () => {
    try {
      const response = await axios.get('/api/user')
      if (response.data.success) {
        setUser(response.data.data)
      }
    } catch (error) {
      console.error('Error fetching user:', error)
      logout()
    } finally {
      setLoading(false)
    }
  }

  const login = async (email, lozinka) => {
    try {
      const response = await axios.post('/api/login', { email, lozinka })
      if (response.data.success) {
        const { token: newToken, korisnik } = response.data.data
        setToken(newToken)
        setUser(korisnik)
        localStorage.setItem('token', newToken)
        axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
        return { success: true }
      }
      return { success: false, message: response.data?.message || 'Greška pri prijavi' }
    } catch (error) {
      const msg = error.response?.data?.message || 
        (error.code === 'ERR_NETWORK' ? 'Backend nije dostupan. Proverite da li server radi i CORS.' : 'Greška pri prijavi')
      return { success: false, message: msg }
    }
  }

  const formatValidationErrors = (errors) => {
    if (!errors || typeof errors !== 'object') return null
    const parts = []
    for (const [field, msgs] of Object.entries(errors)) {
      const arr = Array.isArray(msgs) ? msgs : [msgs]
      parts.push(...arr.map(m => (field + ': ' + m)))
    }
    return parts.join('. ')
  }

  const register = async (email, lozinka, idUloga) => {
    try {
      const response = await axios.post('/api/register', { 
        email, 
        lozinka, 
        idUloga: typeof idUloga === 'number' ? idUloga : parseInt(idUloga, 10)
      })
      if (response.data.success) {
        // Automatski prijavi korisnika nakon registracije
        const loginResponse = await axios.post('/api/login', { email, lozinka })
        if (loginResponse.data.success) {
          const { token: newToken, korisnik } = loginResponse.data.data
          setToken(newToken)
          setUser(korisnik)
          localStorage.setItem('token', newToken)
          axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
          return { success: true }
        }
        return { success: true, message: 'Registracija uspešna, prijavite se' }
      }
      return { success: false, message: response.data?.message || 'Greška pri registraciji' }
    } catch (error) {
      const validation = formatValidationErrors(error.response?.data?.errors)
      const msg = error.response?.data?.message || 
        validation ||
        (error.code === 'ERR_NETWORK' ? 'Backend nije dostupan. Proverite da li server radi i CORS.' : 'Greška pri registraciji')
      return { success: false, message: msg }
    }
  }

  const logout = async () => {
    try {
      if (token) {
        await axios.post('/api/logout')
      }
    } catch (error) {
      console.error('Error logging out:', error)
    } finally {
      setToken(null)
      setUser(null)
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
    }
  }

  const refreshUser = () => fetchUser()

  const value = {
    user,
    token,
    login,
    register,
    logout,
    loading,
    refreshUser
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

