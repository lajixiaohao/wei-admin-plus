<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { User, Lock, MoreFilled } from '@element-plus/icons-vue';
import { BaseConfig } from '@/config';
import { validateAccount, validatePassword } from '@/utils/validate';
import { encryptData } from '@/utils/rsaHelp';
import { setToken } from '@/utils/token';
import { getDurationYear } from '@/utils/common';
import CommonService from '@/service/CommonService';
import { v4 as uuidv4 } from 'uuid';
import logosvg from '@/assets/svg/logo.svg';
import loginBg from '@/assets/svg/loginBg.svg';

const router = useRouter();
const route = useRoute();
const commonService = new CommonService();

// form
const loginForm = reactive({
  account: '',
  pwd: '',
  code: '',
  codeImage: '',
  loading: false,
  uuid: ''
});

// show captcha
const showCaptcha = async () => {
  let uuid = uuidv4();
  const { data } = await commonService.getCaptcha({ uuid: uuid });
  loginForm.codeImage = data.img;
  loginForm.uuid = uuid;
}

const loginFormRef = ref();
// 表单验证
const loginFormRules = reactive({
  account: [{ validator: validateAccount, trigger: 'blur' }],
  pwd: [{ validator: validatePassword, trigger: 'blur' }],
  code: [
    { required: true, message: '请输入验证码', trigger: 'blur' },
    { min: 4, max: 4, message: '验证码输入有误', trigger: 'blur' }
  ]
});

// 登录失败时重置表单状态（部分）
const resetFormForFail = () => {
  loginForm.loading = false;
  loginForm.code = '';
  showCaptcha();
}

// 表单提交
const onSubmitLogin = async formEl => {
  if (!formEl) return;
  await formEl.validate(valid => {
    if (valid) {
      loginForm.loading = true;
      commonService.getLoginPublicKey({ uuid: loginForm.uuid }).then(res => {
        commonService.doLogin({
          uuid: loginForm.uuid,
          code: loginForm.code,
          account: loginForm.account,
          pwd: encryptData(loginForm.pwd, res.data.publickey)
        }).then(res => {
          ElMessage.success(res.msg);
          setToken(res.data.token);
          if (route.query.redirect) {
            router.push({ path: decodeURIComponent(route.query.redirect.toString()) });
          } else {
            router.push({ name: 'Dashboard' });
          }
        }).catch(() => {
          resetFormForFail();
        });
      }).catch(() => {
        resetFormForFail();
      });
    } else {
      console.log('error submit!');
    }
  });
}

onMounted(() => {
  showCaptcha();
});
</script>

<template>
  <el-container>
    <el-header height="70px">
      <div>
        <el-avatar :src="logosvg" shape="square" />
        <span class="sys-title">{{ BaseConfig.systemName }}</span>
        <span class="sys-environment">{{ BaseConfig.envText }}</span>
      </div>
    </el-header>
    <el-main>
      <div class="login-bg">
        <el-image style="height: 450px" :src="loginBg" fit="contain" />
      </div>
      <div class="login-box">
        <h3>请登录</h3>
        <el-form :model="loginForm" ref="loginFormRef" :rules="loginFormRules">
          <el-form-item prop="account">
            <el-input v-model="loginForm.account" placeholder="账号" size="large" :prefix-icon="User" clearable />
          </el-form-item>
          <el-form-item prop="pwd">
            <el-input type="password" v-model="loginForm.pwd" placeholder="密码" size="large" show-password :prefix-icon="Lock" clearable />
          </el-form-item>
          <el-form-item prop="code">
            <el-row :gutter="6">
              <el-col :span="16">
                <el-input maxlength="4" v-model="loginForm.code" placeholder="验证码" size="large" :prefix-icon="MoreFilled" @keyup.enter="onSubmitLogin(loginFormRef)" clearable />
              </el-col>
              <el-col :span="8">
                <el-image class="captcha" :src="loginForm.codeImage" @click="showCaptcha" />
              </el-col>
            </el-row>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" class="login-btn" size="large" @click="onSubmitLogin(loginFormRef)" :loading="loginForm.loading">登 录</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-main>
    <el-footer height="70px">
      <div>
        Copyright &copy;
        {{ getDurationYear(BaseConfig.startYear) }}
        <a href="https://gitee.com/lajixiaohao" target="_blank">垃圾小号</a>
        All Rights Reserved
      </div>
    </el-footer>
  </el-container>
</template>

<style scoped>
.el-header,
.el-main,
.el-footer {
  display: flex;
  align-items: center;
}
.el-header,
.el-main {
  padding: 0 10%;
}
.el-header .el-avatar {
  vertical-align: middle;
  margin-right: 10px;
}
.sys-title {
  color: #0068b7;
  font-size: 18px;
  font-weight: 600;
}
.sys-environment {
  font-size: 14px;
  color: #595959;
}
.el-main {
  height: calc(100vh - 140px);
  background-image: linear-gradient(to right, #2c73d2, #008e9b);
  justify-content: space-between;
}
.login-box {
  background-color: white;
  width: 360px;
  padding: 10px;
  border-radius: 4px;
}
.login-box h3 {
  margin: 0 auto;
  width: 200px;
  text-align: center;
  border-bottom: 1px solid #e6e6e6;
  padding: 10px 0;
  margin-bottom: 30px;
}
.login-box .el-form {
  padding: 0 20px;
}
.login-box .el-form .captcha {
  cursor: pointer;
}
.login-box .el-form .login-btn {
  width: 100%;
}
.el-footer {
  background-color: #e8ecee;
  justify-content: center;
  font-size: 14px;
  color: #595959;
}
.el-footer a {
  text-decoration: none;
  color: inherit;
}
.el-footer a:hover {
  color: #3399ff;
}
@media screen and (max-width: 1024px) {
  .el-main {
    justify-content: center;
  }
  .login-bg {
    display: none;
  }
}
</style>