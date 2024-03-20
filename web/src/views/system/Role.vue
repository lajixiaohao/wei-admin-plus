<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, View } from '@element-plus/icons-vue';
import { useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import RoleService from '@/service/RoleService';

const roleService = new RoleService();
const router = useRouter();
const appStore = useAppStore();
const tableData = reactive({
  list: [],
  count: 0,
  loading: false,
  page: 1,
  size: 10,
  role_name: ''
});
const formDialog = reactive({
  visible: false,
  title: '',
  showSubmitLoading: false
});
const baseData = () => {
  return {
    id: 0,
    role_name: '',
    introduce: '',
    is_able: 1
  };
};
const roleFormRef = ref();
const formData = reactive(baseData());

// 关闭dialog
const closeDialog = () => {
  formDialog.visible = false;
  formDialog.title = '';
  Object.assign(formData, baseData());
}

// 请求表格数据
const getTableData = async () => {
  tableData.loading = true;
  const { data } = await roleService.getRoleList({
    page: tableData.page,
    size: tableData.size,
    role_name: tableData.role_name
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
const showAdd = () => {
  formDialog.visible = true;
  formDialog.title = '添加角色';
}

// 编辑
const showEditDialog = row => {
  formDialog.visible = true;
  formDialog.title = '编辑角色';
  Object.assign(formData, row);
}

// 删除
const handleDelete = id => {
  ElMessageBox.confirm(`确定删除该角色吗？`, '提示', {
    type: 'warning'
  }).then(() => {
    roleService.deleteRole(id).then(res => {
      ElMessage.success(res.msg);
      getTableData();
    });
  }).catch(() => {});
}

// 搜索
const onSearch = () => {
  tableData.page = 1;
  getTableData();
}

// 自定义索引
const indexMethod = index => {
  return (tableData.page - 1) * tableData.size + index + 1;
}

// 预览下级
const roleTreeDialog = reactive({
  title: '预览下级',
  visible: false,
  loading: false
});
const loadChildrenOfRole = async (node, resolve) => {
  roleTreeDialog.loading = true;
  let id = 0;
  if (node.level > 0) {
    id = node.data.id;
  }
  const { data } = await roleService.getChildrenOfRole(id);
  roleTreeDialog.loading = false;
  resolve(data.list);
}

// dialog表单提交
const submitForm = async formEl => {
  if (!formEl) return;
  await formEl.validate(valid => {
    if (valid) {
      formDialog.showSubmitLoading = true;
      roleService.addOrEditRole(formData).then(res => {
        ElMessage.success(res.msg);
        formDialog.showSubmitLoading = false;
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

// 表单验证
const roleFormRule = reactive({
  role_name: [{ required: true, message: '请输入角色名称', trigger: 'blur' }]
});

onMounted(() => {
  getTableData();
});
</script>
<template>
  <div class="container">
    <div style="display: flex; margin-bottom: 10px;">
      <el-input
        v-model="tableData.role_name"
        placeholder="请输入角色名称"
        size="small"
        maxlength="50"
        clearable
        style="width: 200px; margin-right: 12px;"
        @keyup.enter.native="onSearch"
      />
      <el-button size="small" type="primary" :icon="Search" @click="onSearch">搜索</el-button>
      <el-button v-if="appStore.isSuper || appStore.auth.includes('sys.role.add')" size="small" type="primary" :icon="Plus" @click="showAdd">添加</el-button>
      <el-button v-if="appStore.isSuper || appStore.auth.includes('sys.role.view.children')" size="small" type="info" :icon="View" @click="roleTreeDialog.visible = true">预览下级</el-button>
    </div>
    <el-table
      v-loading="tableData.loading"
      :data="tableData.list"
      stripe
      style="width: 100%"
      row-key="id"
    >
      <el-table-column type="index" label="序号" width="55" :index="indexMethod" />
      <el-table-column prop="role_name" label="角色名称" />
      <el-table-column prop="introduce" label="角色介绍" />
      <el-table-column prop="adminNums" label="管理员数" />
      <el-table-column prop="childrenNums" label="直属下级数" />
      <el-table-column prop="authNums" label="权限节点数" />
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
          <el-button v-if="appStore.isSuper || appStore.auth.includes('sys.role.edit')" size="small" type="primary" @click="showEditDialog(scope.row)">编辑</el-button>
          <el-button v-if="appStore.isSuper || appStore.auth.includes('sys.role.auth.assign')" size="small" type="success" @click="router.push({ path: `/system/role/permission/${scope.row.id}` })">权限分配</el-button>
          <el-button v-if="!scope.row.childrenNums && !scope.row.adminNums && (appStore.isSuper || appStore.auth.includes('sys.role.delete'))" size="small" type="danger" @click="handleDelete(scope.row.id)">删除</el-button>
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
    <!-- form dialog -->
    <el-dialog
      v-model="formDialog.visible"
      :title="formDialog.title"
      width="30%"
      :before-close="closeDialog"
      destroy-on-close
      :close-on-click-modal="false">
      <el-form :model="formData" ref="roleFormRef" :rules="roleFormRule" label-width="78px">
        <el-form-item label="角色名称" prop="role_name">
          <el-input v-model="formData.role_name" placeholder="请输入角色名称" clearable />
        </el-form-item>
        <el-form-item label="角色介绍">
          <el-input type="textarea" v-model="formData.introduce" placeholder="请输入角色介绍" clearable />
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
            @click="submitForm(roleFormRef)"
            :loading="formDialog.showSubmitLoading">提交</el-button>
        </span>
      </template>
    </el-dialog>
    <!-- tree dialog -->
    <el-dialog
      v-model="roleTreeDialog.visible"
      :title="roleTreeDialog.title"
      width="30%"
      destroy-on-close
      :close-on-click-modal="false">
      <el-tree node-key="id" v-loading="roleTreeDialog.loading" lazy :load="loadChildrenOfRole" :props="{ label: 'role_name', isLeaf: 'leaf' }" />
    </el-dialog>
  </div>
</template>

<style scoped>
.el-pagination {
  margin-top: 10px;
}
</style>
