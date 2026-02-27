import React from 'react'
import ReactDOM from 'react-dom/client'
import axios from 'axios'
import App from './App'
import './index.css'

// U produkciji frontend i backend su na različitim domenima – API pozivi moraju ići na backend URL
const backendUrl = import.meta.env.VITE_BACKEND_URL
if (backendUrl) {
  axios.defaults.baseURL = backendUrl
}

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
)

