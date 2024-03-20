<script setup>
import { ref, reactive, onMounted } from 'vue';
import { menuConvert } from '@/utils/common';
import MenuService from '@/service/MenuService';

const menuService = new MenuService();
const tableData = ref([]);
const tableLoading = ref(false);
const optionalMenu = ref([]);
const formDialog = reactive({
  visible: false,
  title: '',
  submitLoading: false
});
const baseData = () => {
  return {
    id: 0,
    title: '',
    parent_id: 0,
    menu_type: 1,
    page_path: '',
    dirname: '',
    file_route_name: '',
    uri: '',
    auth_alias: '',
    method: 'GET',
    icon: '',
    is_cache: 0,
    is_able: 1,
    sort: 1
  };
};
const menuFormRef = ref();
const formData = reactive(baseData());
// 记录懒加载的tree子级
const loadMap = new Map();

const menuRules = reactive({
  menu_type: [{ required: true, message: '请选择', trigger: 'blur' }],
  title: [{ required: true, message: '请输入', trigger: 'blur' }],
  uri: [{ required: true, message: '请输入接口地址', trigger: 'blur' }]
});

// 懒加载子级
const loadTableData = async (row, treeNode, resolve) => {
  loadMap.set(row.id, { row, treeNode, resolve });
  const { data } = await menuService.getMenuList({ parent_id: row.id});
  resolve(data);
}

/**
 * 重载父级数据
 * @todo 待完善 2023.12.31
 * @param int parent_id 
 */
const reloadTableTree = parent_id => {
  if (loadMap.has(parent_id)) {
    let { row, treeNode, resolve } = loadMap.get(parent_id);
    loadTableData(row, treeNode, resolve);
  } else {
    getTableData();
  }
}

// 请求表格数据
const getTableData = async () => {
  tableLoading.value = true;
  const { data } = await menuService.getMenuList();
  tableLoading.value = false;
  tableData.value = data;
}

// 获取可用父级菜单
const getOptionalMenus = async () => {
  const { data } = await menuService.getOptionalParentMenu();
  optionalMenu.value = data.list;
}

// 添加
const showAdd = () => {
  formDialog.visible = true;
  formDialog.title = '添加';
  getOptionalMenus();
}

// 编辑
const showEditDialog = async row => {
  formDialog.visible = true;
  formDialog.title = '编辑';
  Object.assign(formData, row);
  getOptionalMenus();
}

// 关闭dialog
const closeDialog = () => {
  formDialog.visible = false;
  formDialog.title = '';
  Object.assign(formData, baseData());
}

// 表单提交
const submitForm = async formEl => {
  if (!formEl) return;
  await formEl.validate(valid => {
    if (valid) {
      formDialog.submitLoading = true;
      menuService.addOrUpdateMenu(formData).then(res => {
        ElMessage.success(res.msg);
        formDialog.submitLoading = false;
        reloadTableTree(formData.parent_id);
        closeDialog();
      }).catch(() => {
        formDialog.submitLoading = false;
      });
    } else {
      console.log('error submit!');
    }
  });
}

// 选择上级菜单，最多支持选到3级
const handleChange = v => {
  if (v) {
    switch (v.length) {
      case 1:
        formData.parent_id = v[0];
        break;
      case 2:
        formData.parent_id = v[1];
        break;
      default:
        formData.parent_id = v[2];
        break;
    }
  } else {
    formData.parent_id = 0;
  }
}

// 删除
const handleDelete = row => {
  let msg1 = row.menu_type === 3 ? '接口' : '菜单';
  let msg2 = row.hasChildren ? `其子${msg1}也将一并被删除！` : '';
  ElMessageBox.confirm(`确定删除该${msg1}吗？${msg2}`, '提示', {
    type: 'warning'
  }).then(() => {
    menuService.deleteMenu(row.id).then(res => {
      ElMessage.success(res.msg);
      reloadTableTree(row.parent_id);
    });
  }).catch(() => {});
}

onMounted(() => {
  getTableData();
});
</script>

<template>
  <div>
    <div class="operate">
      <el-button size="small" type="primary" @click="showAdd">添加</el-button>
    </div>
    <el-table
      v-loading="tableLoading"
      :data="tableData"
      stripe
      style="width: 100%"
      row-key="id"
      lazy
      :load="loadTableData"
      :tree-props="{ hasChildren: 'hasChildren' }"
    >
      <el-table-column prop="id" label="ID" />
      <el-table-column prop="title" label="菜单名称" />
      <el-table-column prop="sort" label="排序" />
      <el-table-column label="菜单类型">
        <template #default="scope">
          <el-tag v-if="scope.row.menu_type === 1">普通</el-tag>
          <el-tag v-else-if="scope.row.menu_type === 2" type="success">动态</el-tag>
          <el-tag v-else-if="scope.row.menu_type === 3" type="info">接口</el-tag>
          <el-tag v-else type="danger">外链</el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="page_path" label="路由地址" />
      <el-table-column prop="uri" label="接口地址" />
      <el-table-column prop="auth_alias" label="权限别名" />
      <el-table-column prop="method" label="请求方式" />
      <el-table-column label="页面缓存">
        <template #default="scope">
          <el-tag v-if="scope.row.is_cache === 1" type="success">是</el-tag>
          <el-tag v-else type="danger">否</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="启用">
        <template #default="scope">
          <el-tag v-if="scope.row.is_able === 1" type="success">是</el-tag>
          <el-tag v-else type="danger">否</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="140">
        <template #default="scope">
          <el-button size="small" type="primary" @click="showEditDialog(scope.row)">编辑</el-button>
          <el-button size="small" type="danger" @click="handleDelete(scope.row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    <!-- form dialog -->
    <el-dialog
      v-model="formDialog.visible"
      :title="formDialog.title"
      width="30%"
      :before-close="closeDialog"
      destroy-on-close
      :close-on-click-modal="false">
      <el-form :model="formData" ref="menuFormRef" :rules="menuRules" label-width="80px">
        <el-form-item label="菜单类型" prop="menu_type">
          <el-select :disabled="formData.id > 0" v-model="formData.menu_type">
            <el-option label="普通" :value="1" />
            <el-option label="动态" :value="2" />
            <el-option label="接口" :value="3" />
            <el-option label="外链" :value="4" />
          </el-select>
        </el-form-item>
        <el-form-item label="上级菜单">
          <el-cascader
            :disabled="formData.id > 0"
            :options="menuConvert(optionalMenu)"
            :props="{ checkStrictly: true, value: 'id', label: 'title' }"
            @change="handleChange"
            placeholder="默认顶级"
            v-model="formData.parent_id"
            clearable />
        </el-form-item>
        <el-form-item :label="`${formData.menu_type === 3 ? '接口' : '菜单'}名称`" prop="title">
          <el-input v-model="formData.title" :placeholder="`请输入${formData.menu_type === 3 ? '接口' : '菜单'}名称`" clearable />
        </el-form-item>
        <!-- 接口 -->
        <template v-if="formData.menu_type === 3">
          <el-form-item label="接口地址" prop="uri">
            <el-input v-model="formData.uri" placeholder="请输入接口地址" clearable />
            <div class="form-tip">支持正则表达式，例：demo/index/\d+</div>
          </el-form-item>
          <el-form-item label="权限别名">
            <el-input v-model="formData.auth_alias" placeholder="请输入权限别名" clearable />
            <div class="form-tip">用于前端按钮级权限控制，动态菜单类型下的以及初始类接口（如列表）无需配置。</div>
          </el-form-item>
          <el-form-item label="请求方式">
            <el-select v-model="formData.method">
              <el-option label="GET" value="GET" />
              <el-option label="POST" value="POST" />
              <el-option label="PATCH" value="PATCH" />
              <el-option label="DELETE" value="DELETE" />
            </el-select>
          </el-form-item>
        </template>
        <!-- 普通、动态、外链 -->
        <template v-else>
          <el-form-item :label="`${formData.menu_type === 4 ? '外链' : '路由'}地址`">
            <el-input v-model="formData.page_path" :placeholder="`请输入${formData.menu_type === 4 ? '外链' : '路由'}地址`" clearable />
            <div class="form-tip" v-if="formData.menu_type !== 4">前端路由规则请参阅Vue Router4相关文档</div>
          </el-form-item>
        </template>
        <!-- 普通 -->
        <template v-if="[1,2].includes(formData.menu_type)">
          <el-form-item label="文件目录">
            <el-input v-model="formData.dirname" placeholder="请输入文件目录" clearable />
          </el-form-item>
          <el-form-item label="路由名称">
            <el-input v-model="formData.file_route_name" placeholder="请输入路由名称" clearable />
            <div class="form-tip">路由名也将作为文件名</div>
          </el-form-item>
        </template>
        <template v-if="[1,4].includes(formData.menu_type)">
          <el-form-item label="菜单图标">
            <el-input v-model="formData.icon" placeholder="请输入iconpark图标" clearable />
          </el-form-item>
          <el-form-item label="页面缓存">
            <el-radio-group v-model="formData.is_cache">
              <el-radio :value="1">开启</el-radio>
              <el-radio :value="0">关闭</el-radio>
            </el-radio-group>
          </el-form-item>
        </template>
        <el-form-item v-if="[1,2,4].includes(formData.menu_type)" label="启用状态">
          <el-radio-group v-model="formData.is_able">
            <el-radio :value="1">开启</el-radio>
            <el-radio :value="0">关闭</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="formData.sort" :min="1" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="closeDialog" auto-insert-space>取消</el-button>
          <el-button
            type="success"
            auto-insert-space
            @click="submitForm(menuFormRef)"
            :loading="formDialog.submitLoading">提交</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<style scoped>
.operate {
  background-color: #fff;
  padding: 10px;
}
.form-tip {
  font-size: 12px;
  color: #b1b3b8;
}
</style>