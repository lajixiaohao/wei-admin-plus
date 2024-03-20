import { JSEncrypt } from 'jsencrypt';

/**
 * 加密数据
 *
 * @param str string
 * @param publickey string
 * @returns string|boolean
 */
export const encryptData = (str, publickey) => {
  if (str) {
    const a = new JSEncrypt();
    a.setPublicKey(publickey);
    return a.encrypt(str);
  }
  return false;
}
