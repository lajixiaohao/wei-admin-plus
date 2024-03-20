<script setup>
import { ref } from 'vue';
import WangEditor from '@/components/WangEditor.vue';

const someText = ref('<p>我是初始化的内容</p>');
</script>

<template>
  <div class="container">
    <p>富文本编辑器：<el-link href="https://www.wangeditor.com/" target="_blank">WangEditor</el-link></p>
    <p>上传文件前，请务必先配置好php.ini文件<i style="color: red;">upload_max_filesize、post_max_size</i>的值。</p>
    <p>文件上传配置见：<em>src/config.js</em></p>
    <el-row :gutter="10">
      <el-col :span="12" style="z-index: 1;">
        <WangEditor :value-html="someText" height="600px" @set-editor-value="v => someText = v" />
      </el-col>
      <el-col :span="12">
        <div v-html="someText"></div>
      </el-col>
    </el-row>
  </div>
</template>

<style scoped>
p {
  line-height: 1.8;
  font-size: 14px;
}
</style>