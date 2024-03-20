<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAppStore } from '@/stores/app';
import { validatePassword } from '@/utils/validate';
import { encryptData } from '@/utils/rsaHelp';
import { removeToken } from '@/utils/token';
import CommonService from '@/service/CommonService';

const commonService = new CommonService();
const appStore = useAppStore();
const profileInfo = ref({
  account: '',
  nickname: '',
  roleName: '',
  superior: '',
  created_at: ''
});
const passwordFormRef = ref();
const pofileForm = reactive({
  nickname:'',
  loading: false
});
const passwordForm = reactive({
  oldPassword: '',
  newPassword: '',
  loading: false
});
const passwordFormRules = reactive({
  oldPassword: [
    { required: true, message: '请输入原密码', trigger: 'blur' },
    { validator: validatePassword, trigger: 'blur' }
  ],
  newPassword: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { validator: validatePassword, trigger: 'blur' }
  ]
});

const loadProfileInfo = async () => {
  const { data } = await commonService.getProfile();
  profileInfo.value = data;
  pofileForm.nickname = data.nickname;
}

// 修改个人资料
const onSubmitPofileForm = () => {
  pofileForm.loading = pofileForm.disabled = true;
  commonService.updateProfile({
    nickname: pofileForm.nickname
  }).then(res => {
    pofileForm.loading = false;
    appStore.userInfo.nickname = profileInfo.value.nickname = pofileForm.nickname;
    ElMessage.success(res.msg);
  }).catch(() => {
    pofileForm.loading = false;
  });
}

// 修改密码
const onSubmitPasswordForm = async formEl => {
  if (!formEl) return;
  await formEl.validate(async valid => {
    if (valid) {
      if (passwordForm.oldPassword === passwordForm.newPassword) {
        ElMessage.error('新密码不能与原密码相同');
        return;
      }
      passwordForm.loading = true;
      let { data } = await commonService.getLoggedPublicKey();
      commonService.updatePassword({
        oldPassword: encryptData(passwordForm.oldPassword, data.publickey),
        newPassword: encryptData(passwordForm.newPassword, data.publickey)
      }).then(res => {
        removeToken();
        ElMessage.success(res.msg);
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      }).catch(() => {
        passwordForm.loading = false;
      })
    } else {
      console.log('error submit!');
    }
  });
}

onMounted(() => {
  loadProfileInfo();
});
</script>
<template>
  <div class="container">
    <el-tabs tab-position="left" style="height: 400px;">
      <el-tab-pane label="基本信息">
        <el-descriptions :column="1">
          <el-descriptions-item label="账号">{{ profileInfo.account }}</el-descriptions-item>
          <el-descriptions-item label="昵称">{{ profileInfo.nickname }}</el-descriptions-item>
          <el-descriptions-item label="所属角色">{{ profileInfo.roleName }}</el-descriptions-item>
          <el-descriptions-item label="上级管理员">{{ profileInfo.superior }}</el-descriptions-item>
          <el-descriptions-item label="注册时间">{{ profileInfo.created_at }}</el-descriptions-item>
        </el-descriptions>
      </el-tab-pane>
      <el-tab-pane label="修改资料">
        <el-form ref="pofileFormRef" :model="pofileForm" label-position="top">
          <el-form-item label="昵称">
            <el-input v-model="pofileForm.nickname" placeholder="请输入昵称" maxlength="20" show-word-limit clearable />
          </el-form-item>
          <el-form-item>
            <el-button
              type="primary"
              size="small"
              @click="onSubmitPofileForm"
              :loading="pofileForm.loading"
              :disabled="appStore.userInfo.nickname === pofileForm.nickname">提交修改</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
      <el-tab-pane label="修改密码">
        <el-form ref="passwordFormRef" :model="passwordForm" label-position="top" :rules="passwordFormRules">
          <el-form-item label="原密码" prop="oldPassword">
            <el-input v-model="passwordForm.oldPassword" placeholder="请输入原密码" show-password clearable />
          </el-form-item>
          <el-form-item label="新密码" prop="newPassword">
            <el-input v-model="passwordForm.newPassword" placeholder="请输入新密码" show-password clearable />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" size="small" @click="onSubmitPasswordForm(passwordFormRef)" :loading="passwordForm.loading" :disabled="passwordForm.disabled">提交修改</el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>