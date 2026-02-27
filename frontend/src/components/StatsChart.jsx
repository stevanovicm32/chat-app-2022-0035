import React, { useState, useEffect } from 'react'
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
} from 'recharts'
import axios from 'axios'
import './StatsChart.css'

const COLORS = ['#4a90d9', '#7ed56f', '#f39c12', '#e74c3c', '#9b59b6']

const StatsChart = () => {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const response = await axios.get('/api/stats')
        if (response.data.success) {
          setStats(response.data.data)
        } else {
          setError('Nije moguće učitati statistiku')
        }
      } catch (err) {
        console.error('Stats fetch error:', err)
        setError(err.response?.data?.message || 'Greška pri učitavanju podataka')
      } finally {
        setLoading(false)
      }
    }
    fetchStats()
  }, [])

  if (loading) return <div className="stats-loading">Učitavanje statistike...</div>
  if (error) return <div className="stats-error">{error}</div>
  if (!stats) return null

  const pieData = stats.korisniciPoUlozi?.map((r, i) => ({
    name: r.uloga,
    value: r.broj,
    fill: COLORS[i % COLORS.length],
  })) || []

  const barData = stats.korisniciPoUlozi || []

  return (
    <div className="stats-chart-container">
      <div className="stats-summary">
        <div className="stats-card">
          <span className="stats-card-value">{stats.ukupnoKorisnika}</span>
          <span className="stats-card-label">Korisnici</span>
        </div>
        <div className="stats-card">
          <span className="stats-card-value">{stats.ukupnoChatova}</span>
          <span className="stats-card-label">Chatovi</span>
        </div>
        <div className="stats-card">
          <span className="stats-card-value">{stats.ukupnoPoruka}</span>
          <span className="stats-card-label">Poruke</span>
        </div>
      </div>

      <div className="charts-row">
        <div className="chart-box">
          <h3>Korisnici po ulozi (stubičasti dijagram)</h3>
          <ResponsiveContainer width="100%" height={280}>
            <BarChart data={barData} margin={{ top: 10, right: 20, left: 10, bottom: 5 }}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="uloga" />
              <YAxis allowDecimals={false} />
              <Tooltip />
              <Legend />
              <Bar dataKey="broj" name="Broj korisnika" fill="#4a90d9" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="chart-box">
          <h3>Korisnici po ulozi (kružni dijagram)</h3>
          <ResponsiveContainer width="100%" height={280}>
            <PieChart>
              <Pie
                data={pieData}
                dataKey="value"
                nameKey="name"
                cx="50%"
                cy="50%"
                outerRadius={90}
                label={({ name, value }) => `${name}: ${value}`}
              >
                {pieData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.fill} />
                ))}
              </Pie>
              <Tooltip />
              <Legend />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>
    </div>
  )
}

export default StatsChart
