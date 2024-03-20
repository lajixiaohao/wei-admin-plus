<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const second = ref(3);

setInterval(() => {
  second.value--;
}, 1000);

const router = useRouter();
setTimeout(() => {
  router.push({ name: 'Dashboard' });
}, 3000);
</script>

<template>
  <p style="margin: 10px;">该页面不存在，<span style="color: red;">{{ second }}</span>秒后自动返回首页</p>
</template>
