import axios from 'axios';

const API = axios.create({
  baseURL: 'http://192.168.88.29:8000/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

// API.interceptors.request.use(async (config) => {
  // const token = await SecureStore.getItemAsync('token');
  // if (token) {
  //   config.headers.Authorization = `Bearer ${token}`;
  // }
//   return config;
// });

export default API;