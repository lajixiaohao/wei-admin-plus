<script setup>
import { reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { menuConvert } from '@/utils/common';
import RoleService from '@/service/RoleService';

const roleService = new RoleService();
const route = useRoute();
const treeData = reactive({
  title: '',
  permission: [],
  loading: false,
  defaultChecked: [],
  checked: [],
  submitLoading: false,
  disabled: true
});

// 初始化权限
const loadAssignablePermission = async () => {
  treeData.loading = true;
  const { data } = await roleService.getAssignablePermission(route.params.role_id);
  treeData.loading = false;
  treeData.permission = menuConvert(data.permission);
  treeData.title = `为角色“${data.role_name}”，分配权限：`;
  if (data.default_checked) {
    treeData.defaultChecked = treeData.checked = data.default_checked.split(',');
  }
}

const defaultProps = {
  value: 'id',
  label: 'title'
};

// 是否禁止点击提交按钮
const isDisabled = computed(()=>{
  return treeData.checked.join(',') === treeData.defaultChecked.join(',');
});

const handleSubmit = () => {
  treeData.submitLoading = true;
  roleService.submitPermission({
    id: route.params.role_id,
    keys: treeData.checked.join(',')
  }).then(res => {
    ElMessage.success(res.msg);
    treeData.submitLoading = false;
    treeData.defaultChecked = treeData.checked;
  }).catch(() => {
    treeData.submitLoading = false;
  });
}

// 点击节点复选框之后触发
const handleCheckChange = (data, tree) => {
  treeData.checked = tree.checkedKeys;
}

onMounted(() => {
  loadAssignablePermission();
});
</script>
<template>
  <div class="container">
    <p>
      <el-text>{{ treeData.title }}</el-text>
    </p>
    <el-tree
      v-loading="treeData.loading"
      :data="treeData.permission"
      :default-checked-keys="treeData.defaultChecked"
      :props="defaultProps"
      node-key="id"
      show-checkbox
      check-strictly
      @check="handleCheckChange" />
    <div class="submit">
      <el-button
        type="primary"
        size="small"
        @click="handleSubmit"
        :loading="treeData.submitLoading"
        :disabled="isDisabled">立即提交</el-button>
    </div>
  </div>
</template>

<style scoped>
.el-tree {
  margin-top: 10px;
  border-left: 1px solid #e2e2e2;
}
.submit {
  margin: 20px 0 0 10px;
}
</style>