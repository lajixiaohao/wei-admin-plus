import axios from 'axios';
import { getToken, removeToken } from '../utils/token';

const request = axios.create({
  baseURL: import.meta.env.VITE_BASE_URL,
  timeout: 5000
});

request.interceptors.request.use(c => {
  let token = getToken();
  if (token) {
    c.headers.Authorization = `Bearer ${token}`;
  }
  return c;
});

request.interceptors.response.use(response => {
  let res = response.data;
  if (res.code === 200) {
    return res;
  }
  if (res.code === 401) {
    ElMessageBox.confirm(res.msg, '提示', {
      confirmButtonText: '重新登录',
      cancelButtonText: '关闭',
      type: 'warning'
    }).then(() => {
      removeToken();
      window.location.reload();
    }).catch(() => {});
  } else {
    ElMessage.error(res.msg);
  }
  return Promise.reject(new Error(res.msg || 'Error'));
}, err => {
  ElMessage.error(err.message);
  return Promise.reject(err);
});

export default request;
