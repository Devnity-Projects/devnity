import axios from 'axios'

// Configurar axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Configurar CSRF token
const token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content')
} else {
    console.error('CSRF token not found')
}

export default axios
