<script setup>
import { reactive, onMounted } from 'vue';
import { useAppStore } from '@/stores/app';
import * as echarts from 'echarts/core';
import { GridComponent } from 'echarts/components';
import { BarChart } from 'echarts/charts';
import { CanvasRenderer } from 'echarts/renderers';

const loadChart = () => {
  echarts.use([GridComponent, BarChart, CanvasRenderer]);
  const chartDom = document.getElementById('my-echarts');
  const myChart = echarts.init(chartDom);
  const option = {
    xAxis: {
      type: 'category',
      data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
    },
    yAxis: {
      type: 'value'
    },
    series: [
      {
        data: [120, 200, 150, 80, 70, 110, 130],
        type: 'bar'
      }
    ]
  };
  myChart.setOption(option);
  window.addEventListener('resize', myChart.resize);
}

const appStore = useAppStore();
const timeData = reactive({
  datetime: '',
  second: ''
});

const setHello = () => {
  let hour = (new Date()).getHours();
  if (hour > 5 && hour < 11) {
    return '上午';
  } else if (hour > 10 && hour < 14) {
    return '中午';
  } else if (hour > 13 && hour < 19) {
    return '下午';
  } else {
    return '晚上';
  }
}

const updateTime = () => {
  setInterval(() => {
    let d = new Date();
    timeData.year = d.getFullYear();
    timeData.month = d.getMonth() + 1;
    timeData.day = d.getDate();
    let h = d.getHours();
    timeData.hour = h > 9 ? h : `0${h}`;
    let m = d.getMinutes();
    timeData.minute = m > 9 ? m : `0${m}`;
    timeData.second = d.getSeconds();
  }, 1000);
}

onMounted(()=>{
  loadChart();
  updateTime();
});
</script>

<template>
  <div>
    <div class="b1">
      <p style="color: #409EFF;font-size: 18px;">{{ setHello() }}好，{{ appStore.adminName }}！</p>
      <p style="margin-top: 20px;">
        <el-text>现在是北京时间：{{ timeData.year }}年{{ timeData.month }}月{{ timeData.day }}日，{{ timeData.hour }}时{{ timeData.minute }}分<span class="second">{{ timeData.second }}</span>秒</el-text>
      </p>
    </div>
    <div class="b2">
      <el-row :gutter="16">
        <!-- 天气 -->
        <el-col :span="5">
          <iframe frameborder="0" scrolling="no" hspace="0" src="https://i.tianqi.com/?c=code&a=getcode&id=55&icon=1"></iframe>
        </el-col>
        <!-- 图表 -->
        <el-col :span="19">
          <div id="my-echarts" style="background-color: #fff; height: 360px;box-shadow: var(--el-box-shadow-light);"></div>
        </el-col>
      </el-row>
    </div>
    <!-- 最近登录日志 -->
    <div class="b3">
      <p style="font-size: 14px; color: #409EFF;">最近登录信息</p>
      <el-table :data="appStore.loginLog" row-key="id" stripe>
        <el-table-column prop="adminName" label="管理员" />
        <el-table-column prop="ipv4" label="IPV4" />
        <el-table-column prop="location" label="登录地点" />
        <el-table-column prop="user_agent" label="用户代理" show-overflow-tooltip />
        <el-table-column prop="login_at" label="登录时间" />
        <el-table-column prop="logout_at" label="登出时间" />
      </el-table>
    </div>
  </div>
</template>

<style scoped>
.b1, .b3 {
  background-color: #fff;
  padding: 20px;
  box-shadow: var(--el-box-shadow-light);
}
.b1 .extra {
  font-size: 14px;
  color: #808080;
  margin-top: 16px;
}
.b2 {
  margin-top: 20px;
}
.b3 {
  margin-top: 12px;
}
iframe {
  background-color: #fff;
  padding: 20px;
  box-shadow: var(--el-box-shadow-light);
  width: 100%;
  height: 360px;
}
.second {
  color: #409EFF;
}
</style>
