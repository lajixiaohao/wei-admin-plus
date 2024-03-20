<script setup>
import '@wangeditor/editor/dist/css/style.css';
import { onBeforeUnmount, ref, shallowRef } from 'vue';
import { Editor, Toolbar } from '@wangeditor/editor-for-vue';
import { getToken } from '../utils/token';
import { BaseConfig } from '@/config';

const editorRef = shallowRef();
const props = defineProps({
  valueHtml: {
    type: String,
    default: '<p></p>'
  },
  height: {
    type: String,
    default: '500px'
  }
});
const emit = defineEmits(['setEditorValue']);

// 编辑器的值
const valueHtml = ref(props.valueHtml);

// 组件销毁时，也及时销毁编辑器
onBeforeUnmount(() => {
  const editor = editorRef.value;
  if (editor === null) return;
  editor.destroy();
});

const handleCreated = editor => {
  editorRef.value = editor;
}

const handleChange = editor => {
  emit('setEditorValue', editor.getHtml());
}

// 工具栏配置
const toolbarConfig = {};
// 编辑器配置
const editorConfig = {
  placeholder: '请输入内容...',
  autoFocus: false,
  MENU_CONF: {}
};

// 上传图片配置
editorConfig.MENU_CONF['uploadImage'] = {
  server: import.meta.env.VITE_BASE_URL + 'upload-image',
  fieldName: 'photo',
  timeout: 10 * 1000, // 10s
  headers: { Authorization: 'Bearer ' + getToken() },
  maxFileSize: BaseConfig.uploadImageMaxSize, // 10M
  base64LimitSize: 5 * 1024,
  onFailed: () => {
    ElMessage.error('上传失败');
  },
  onError: (file, err, res) => {
    ElMessage.error(err.message);
  },
  customInsert: (res, insertFn) => {
    if (res.code === 200) {
      insertFn(res.data.url, res.data.alt);
    } else {
      ElMessage.error(res.msg);
    }
  }
};

// 上传视频配置
editorConfig.MENU_CONF['uploadVideo'] = {
  server: import.meta.env.VITE_BASE_URL + 'upload-video',
  fieldName: 'video',
  maxFileSize: BaseConfig.uploadVideoMaxSize,
  timeout: 60 * 1000, // 60s
  headers: { Authorization: 'Bearer ' + getToken() },
  onFailed: () => {
    ElMessage.error('上传失败');
  },
  onError: (file, err, res) => {
    ElMessage.error(err.message);
  },
  customInsert: (res, insertFn) => {
    if (res.code === 200) {
      insertFn(res.data.url);
    } else {
      ElMessage.error(res.msg);
    }
  }
};

// 模式
const mode = 'default';

// 编辑器样式
const styleObject = {
  height: props.height,
  overflowY: 'hidden'
};
</script>

<template>
  <div style="border: 1px solid #ccc"> 
    <Toolbar
      style="border-bottom: 1px solid #ccc"
      :editor="editorRef"
      :defaultConfig="toolbarConfig"
      :mode="mode"
    />
    <Editor
      :style="styleObject"
      v-model="valueHtml"
      :defaultConfig="editorConfig"
      :mode="mode"
      @onCreated="handleCreated"
      @onChange="handleChange"
    />
  </div>
</template>