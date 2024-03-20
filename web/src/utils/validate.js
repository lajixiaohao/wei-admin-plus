/**
 * 账号验证，用以element-plus表单
 *
 * @param rule any
 * @param val string
 * @param callback any
 */
export const validateAccount = (rule, val, callback) => {
  if (!val) {
    callback(new Error('请输入账号'));
  }
  if (
    /^\d{11}$/.test(val) ||
    /^[a-zA-Z][a-zA-Z0-9_]{4,19}$/.test(val)
  ) {
    callback();
  }
  callback(new Error('账号输入有误'));
}

/**
 * 密码验证，用以element-plus表单
 *
 * @param rule any
 * @param val string
 * @param callback any
 */
export const validatePassword = (rule, val, callback) => {
  if (!val) {
    callback(new Error('请输入密码'));
  }
  if (!/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,20}$/.test(val)) {
    callback(new Error('密码输入有误'));
  }
  callback();
}

// 密码规则验证
export const isValidPassword = str => /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,20}$/.test(str);
