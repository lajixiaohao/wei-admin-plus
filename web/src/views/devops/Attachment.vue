<script setup>
import { reactive, onMounted } from 'vue';
import { Search } from '@element-plus/icons-vue';
import { byteConvert } from '@/utils/common'
import AttachmentService from '@/service/AttachmentService';

const attachmentService = new AttachmentService();
const tableData = reactive({
  list: [],
  count: 0,
  loading: false,
  page: 1,
  size: 10,
  keyword: '',
  type: ''
});

// 类型声明
const types = [
  { id: 1, name: '图片' },
  { id: 2, name: '视频' },
  { id: 3, name: '文档' },
  { id: 4, name: '其他' },
];

// 请求表格数据
const getTableData = async () => {
  tableData.loading = true;
  const { data } = await attachmentService.getAttachmentList({
    page: tableData.page,
    size: tableData.size,
    keyword: tableData.keyword,
    type: tableData.type
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

// 搜索
const handleSearch = () => {
  tableData.page = 1;
  getTableData();
}

// 自定义索引
const indexMethod = index => {
  return (tableData.page - 1) * tableData.size + index + 1;
}

// 返回文件类型
const getFileType = type => {
  let typeName = '';
  for (let item of types) {
    if (item.id === type) {
      typeName = item.name;
      break;
    }
  }
  return typeName;
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
        placeholder="请输入管理员账号或昵称"
        size="small"
        maxlength="50"
        clearable
        style="width: 200px; margin-right: 12px;"
      />
      <el-select v-model="tableData.type" placeholder="类型" clearable size="small" style="width: 100px; margin-right: 12px;">
        <el-option v-for="item in types" :key="item.id" :label="item.name" :value="item.id" />
      </el-select>
      <el-button size="small" type="primary" :icon="Search" @click="handleSearch">搜索</el-button>
    </div>
    <el-table
      v-loading="tableData.loading"
      :data="tableData.list"
      stripe
      style="width: 100%"
      row-key="id"
    >
      <el-table-column type="index" label="序号" width="55" :index="indexMethod" />
      <el-table-column prop="adminName" label="管理员" />
      <el-table-column prop="original_name" label="原始文件名" show-overflow-tooltip />
      <el-table-column label="类型">
        <template #default="scope">{{ getFileType(scope.row.type) }}</template>
      </el-table-column>
      <el-table-column label="预览" show-overflow-tooltip>
        <template #default="scope">
          <div v-if="scope.row.type === 1" style="display: flex;align-items: center;width: 60px;height: 60px;">
            <el-image :src="scope.row.url" :preview-src-list="[scope.row.url]" preview-teleported />
          </div>
          <el-link v-else :href="scope.row.url" target="_blank">{{ scope.row.url }}</el-link>
        </template>
      </el-table-column>
      <el-table-column label="大小">
        <template #default="scope">{{ byteConvert(scope.row.size) }}</template>
      </el-table-column>
      <el-table-column prop="created_at" label="上传时间" />
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
  </div>
</template>

<style scoped>
.el-pagination {
  margin-top: 10px;
}
</style>