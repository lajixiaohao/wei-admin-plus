<script setup>
import { reactive, onMounted } from 'vue';
import { Search } from '@element-plus/icons-vue';
import LogService from '@/service/LogService';

const tableData = reactive({
  list: [],
  count: 0,
  loading: false,
  page: 1,
  size: 10,
  keyword: ''
});

const logService = new LogService();

// 请求表格数据
const getTableData = async () => {
  tableData.loading = true;
  const { data } = await logService.getLoginLog({
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

// 搜索
const handleSearch = () => {
  tableData.page = 1;
  getTableData();
}

// 自定义索引
const indexMethod = index => {
  return (tableData.page - 1) * tableData.size + index + 1;
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
        @keyup.enter.native="handleSearch"
      />
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
      <el-table-column prop="ipv4" label="IPV4" />
      <el-table-column prop="location" label="登录地点" />
      <el-table-column prop="user_agent" label="设备" show-overflow-tooltip />
      <el-table-column prop="login_at" label="登录时间" />
      <el-table-column label="登出时间">
        <template #default="scope">
          <template v-if="scope.row.logout_at">{{ scope.row.logout_at }}</template>
          <template v-else>
            <el-tag v-if="scope.row.isCurrentDevice" type="success">当前设备在线中</el-tag>
            <el-tag v-else type="danger">异常或其他设备在线中</el-tag>
          </template>
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
  </div>
</template>

<style scoped>
.el-pagination {
  margin-top: 10px;
}
</style>