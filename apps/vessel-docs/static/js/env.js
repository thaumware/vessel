// Prefer user override stored in localStorage, else use sensible default
const localApiUrl = localStorage.getItem('API_URL');
window.ENV = {
  API_URL: localApiUrl || 'http://localhost:8000/api'
};
