import './assets/css/main.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';

import App from './App.vue';
import router from './router';
import ElementPlus from 'element-plus';
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'

const app = createApp(App);

app.use(createPinia()).use(router).use(ElementPlus, { locale: zhCn }).mount('#app');
