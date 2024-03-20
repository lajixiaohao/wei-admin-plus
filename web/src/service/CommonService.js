import request from '../utils/request';

export default class CommonService {
  static initialize = () => request.get('init');
  getCaptcha = params => request.get('captcha', { params });
  getLoginPublicKey = params => request.get('login/publickey', { params });
  getLoggedPublicKey = () => request.get('logged/publickey');
  doLogin = data => request.post('login', data);
  doLogout = () => request.get('logout');
  getProfile = () => request.get('profile');
  updateProfile = data => request.post('profile', data);
  updatePassword = data => request.post('password', data);
}