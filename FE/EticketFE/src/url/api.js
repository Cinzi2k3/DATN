import axios from 'axios';

axios.defaults.baseURL = 'http://192.168.0.105:8000/api';
// axios.defaults.timeout = 5000;
axios.defaults.headers.common['Content-Type'] = 'application/json';

export default axios;
    