<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, View } from '@element-plus/icons-vue';
import { validateAccount, validatePassword, isValidPassword } from '@/utils/validate';
import { encryptData } from '@/utils/rsaHelp';
import { ElMessage } from 'element-plus';
import { useAppStore } from '@/stores/app';
import AdminService from '@/service/AdminService';
import CommonService from '@/service/CommonService';

const adminService = new AdminService();
const commonService = new CommonService();
const appStore = useAppStore();

const tableData = reactive({
  list: [],
  count: 0,
  loading: false,
  page: 1,
  size: 10,
  keyword: ''
});
const formDialog = reactive({
  visible: false,
  title: '',
  showSubmitLoading: false,
  optionalRoles: []
});
const baseData = () => {
  return {
    id: 0,
    account: '',
    nickname: '',
    pwd: '',
    role_id: '',
    is_able: 1
  };
};
const adminFormRef = ref();
const formData = reactive(baseData());
// 密码提示
const passwordTip = '必须包含大小写字母和数字，可以使用特殊字符，长度6-20';

// 字段验证
const adminFormRules = reactive({
  account:[
    { required: true, message: '请输入账号', trigger: 'blur' },
    { validator: validateAccount, trigger: 'blur' }
  ],
  pwd: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { validator: validatePassword, trigger: 'blur' }
  ]
});

// 请求表格数据
const getTableData = async () => {
  tableData.loading = true;
  const { data } = await adminService.getAdminList({
    page: tableData.page,
    size: tableData.size,
    keyword: tableData.keyword
  });
  tableData.loading = false;
  tableData.list = data.list;
  tableData.count = data.count;
}

// 当前页变化时
const handleCurrentChange = page => {
  tableData.page = page;
  getTableData();
}

// 每页显示条数变化时
const handleSizeChange = size => {
  tableData.size = size;
  getTableData();
}

// 添加
const showAdd = async () => {
  formDialog.visible = true;
  formDialog.title = '添加管理员';
  const { data } = await adminService.addOrEditAdmin({ id: 0, init: 1 });
  formDialog.optionalRoles = data.roles;
}

// 编辑
const showEditDialog = async row => {
  formDialog.visible = true;
  formDialog.title = '编辑管理员';
  row.role_id = row.role_id === 0 ? '' : row.role_id;
  Object.assign(formData, row);
  const { data } = await adminService.addOrEditAdmin({ id: row.id, init: 1 });
  formDialog.optionalRoles = data.roles;
}

// 删除
const handleDelete = id => {
  ElMessageBox.confirm(`确定删除该管理员吗？`, '提示', {
    type: 'warning'
  }).then(() => {
    adminService.deleteAdmin(id).then(res => {
      ElMessage.success(res.msg);
      getTableData();
    });
  }).catch(() => {});
}

// 搜索
const handleSearch = () => {
  tableData.page = 1;
  getTableData();
}

// 重置密码
const handleResetPassword = id => {
  ElMessageBox.prompt('请输入新密码', '提示', {
    inputValidator: isValidPassword,
    inputErrorMessage: passwordTip,
    closeOnClickModal: false
  }).then(async ({ value }) => {
    let { data } = await commonService.getLoggedPublicKey();
    adminService.resetPassword({
      id: id,
      pwd: encryptData(value, data.publickey)
    }).then(res => {
      ElMessage.success(res.msg);
    })
  }).catch(() => {});
}

// 自定义索引
const indexMethod = index => {
  return (tableData.page - 1) * tableData.size + index + 1;
}

// 关闭dialog
const closeDialog = () => {
  formDialog.visible = false;
  formDialog.title = '';
  Object.assign(formData, baseData());
}

// dialog表单提交
const submitForm = async formEl => {
  if (!formEl) return;
  await formEl.validate(async valid => {
    if (valid) {
      formDialog.showSubmitLoading = true;
      // 默认是编辑需要更新的字段
      let field = {
        id: formData.id,
        nickname: formData.nickname,
        role_id: formData.role_id !== '' ? formData.role_id : 0,
        is_able: formData.is_able
      };
      if (formData.id <= 0) {
        let { data } = await commonService.getLoggedPublicKey();
        field.pwd = encryptData(formData.pwd, data.publickey);
        field.account = formData.account;
      }
      adminService.addOrEditAdmin(field).then(res => {
        formDialog.showSubmitLoading = false;
        ElMessage.success(res.msg);
        closeDialog();
        getTableData();
      }).catch(() => {
        formDialog.showSubmitLoading = false;
      });
    } else {
      console.log('error submit!');
    }
  });
}

// 预览下级
const adminTreeDialog = reactive({
  title: '预览下级',
  visible: false,
  loading: false
});
const loadChildrenOfAdmin = async (node, resolve) => {
  adminTreeDialog.loading = true;
  let id = 0;
  if (node.level > 0) {
    id = node.data.id;
  }
  const { data } = await adminService.getChildrenOfAdmin(id);
  adminTreeDialog.loading = false;
  resolve(data.list);
}

onMounted(() => {
  getTableData();
});
</script>
<template>
  <div class="container">
    <div style="display: flex; margin-bottom: 10px;">
      <el-input
        v-model="tableData.keyword"
        placeholder="请输入账号或昵称"
        size="small"
        maxlength="50"
        clearable
        style="width: 200px; margin-right: 12px;"
        @keyup.enter.native="handleSearch"
      />
      <el-button size="small" type="primary" :icon="Search" @click="handleSearch">搜索</el-button>
      <el-button v-if="appStore.isSuper || appStore.auth.includes('sys.admin.add')" size="small" type="primary" :icon="Plus" @click="showAdd">添加</el-button>
      <el-button v-if="appStore.isSuper || appStore.auth.includes('sys.admin.view.children')" size="small" type="info" :icon="View" @click="adminTreeDialog.visible = true">预览下级</el-button>
    </div>
    <el-table
      v-loading="tableData.loading"
      :data="tableData.list"
      stripe
      style="width: 100%"
      row-key="id"
    >
      <el-table-column type="index" label="序号" width="55" :index="indexMethod" />
      <el-table-column prop="account" label="账号" />
      <el-table-column prop="nickname" label="昵称" />
      <el-table-column prop="childrenNum" label="直属下级数" />
      <el-table-column prop="role_name" label="所属角色" />
      <el-table-column label="启用状态">
        <template #default="scope">
          <el-tag v-if="scope.row.is_able === 1" type="success">启用</el-tag>
          <el-tag v-else type="danger">禁用</el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="created_at" label="创建时间" />
      <el-table-column prop="updated_at" label="最后更新时间" />
      <el-table-column label="操作" width="220">
        <template #default="scope">
          <el-button v-if="appStore.isSuper || appStore.auth.includes('sys.admin.edit')" size="small" type="primary" @click="showEditDialog(scope.row)">编辑</el-button>
          <el-button v-if="appStore.isSuper || appStore.auth.includes('sys.admin.edit.password')" size="small" type="success" @click="handleResetPassword(scope.row.id)">重置密码</el-button>
          <el-button v-if="!scope.row.childrenNum && (appStore.isSuper || appStore.auth.includes('sys.admin.delete'))" size="small" type="danger" @click="handleDelete(scope.row.id)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination
      background
      small
      layout="total, sizes, prev, pager, next"
      :total="tableData.count"
      :page-size="tableData.size"
      :page-sizes="[5, 10, 15, 20]"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange" />
    <!-- dialog -->
    <el-dialog
      v-model="formDialog.visible"
      :title="formDialog.title"
      width="30%"
      :before-close="closeDialog"
      destroy-on-close
      :close-on-click-modal="false">
      <el-form :model="formData" ref="adminFormRef" :rules="adminFormRules" label-width="80px">
        <el-form-item label="账号" prop="account">
          <el-input :disabled="formData.id > 0" v-model="formData.account" placeholder="请输入账号" clearable />
          <div v-if="!formData.id" class="form-tip">由大小写字母和数字、下划线组成，长度5-20，支持手机号</div>
        </el-form-item>
        <el-form-item v-if="!formData.id" label="密码" prop="pwd">
          <el-input type="password" v-model="formData.pwd" placeholder="请输入密码" show-password clearable />
          <div class="form-tip">{{ passwordTip }}</div>
        </el-form-item>
        <el-form-item label="昵称">
          <el-input v-model="formData.nickname" placeholder="请输入昵称" clearable />
        </el-form-item>
        <el-form-item label="所属角色" prop="role_id">
          <el-select v-model="formData.role_id" placeholder="请选择" clearable>
            <el-option
              v-for="item in formDialog.optionalRoles"
              :key="item.id"
              :label="item.role_name"
              :value="item.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="启用状态">
          <el-radio-group v-model="formData.is_able">
            <el-radio :value="1">开启</el-radio>
            <el-radio :value="0">关闭</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="closeDialog" auto-insert-space>取消</el-button>
          <el-button
            type="success"
            auto-insert-space
            @click="submitForm(adminFormRef)"
            :loading="formDialog.showSubmitLoading">提交</el-button>
        </span>
      </template>
    </el-dialog>
    <!-- tree dialog -->
    <el-dialog
      v-model="adminTreeDialog.visible"
      :title="adminTreeDialog.title"
      width="30%"
      destroy-on-close
      :close-on-click-modal="false">
      <el-tree node-key="id" v-loading="adminTreeDialog.loading" lazy :load="loadChildrenOfAdmin" :props="{ label: 'account', isLeaf: 'leaf' }">
        <template #default="{ node, data }">
          <span>{{ node.label }}{{ data.nickname ? `（${data.nickname}）` : '' }}</span>
        </template>
      </el-tree>
    </el-dialog>
  </div>
</template>

<style scoped>
.el-pagination {
  margin-top: 10px;
}
.form-tip {
  font-size: 12px;
  color: #b1b3b8;
}
</style>