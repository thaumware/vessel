// Load API URL from environment or use default
window.ENV = {
  API_URL: 'http://localhost:8016/api'
};

// Override with local storage if set
const localApiUrl = localStorage.getItem('API_URL');
if (localApiUrl) {
  window.ENV.API_URL = localApiUrl;
}
