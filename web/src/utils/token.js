/**
 * token管理
 *
 * @since 2023.7.20
 */

import { BaseConfig } from '../config';

export const getToken = () => window.localStorage.getItem(BaseConfig.tokenKey);

export const setToken = str => {
  window.localStorage.setItem(BaseConfig.tokenKey, str);
}

export const removeToken = () => {
  window.localStorage.removeItem(BaseConfig.tokenKey);
}
