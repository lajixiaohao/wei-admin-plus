<script setup>
import { BaseConfig } from '@/config';
import { useAppStore } from '@/stores/app';
import { getDurationYear } from '@/utils/common';
import Aside from '@/components/Aside.vue';
import Header from '@/components/Header.vue';

const appStore = useAppStore();
// 底部描述
const footer = `&copy; ${getDurationYear(BaseConfig.startYear)} ${BaseConfig.systemName} ${BaseConfig.version} ${BaseConfig.envText}`;
</script>

<template>
  <el-container>
    <el-aside :width="appStore.isCollapseMenu ? '64px' : '240px'">
      <Aside />
    </el-aside>
    <el-container>
      <el-header>
        <Header />
      </el-header>
      <el-main>
        <router-view v-slot="{ Component, route }">
          <transition name="slide-fade" mode="out-in">
            <keep-alive :include="appStore.cachedViews">
              <component v-if="appStore.isShowComponent" :key="route.name" :is="Component" />
            </keep-alive>
          </transition>
        </router-view>
      </el-main>
      <el-footer>
        <div v-html="footer"></div>
      </el-footer>
    </el-container>
  </el-container>
</template>

<style scoped>
.el-container {
  height: 100vh;
}
.el-aside {
  background-color: #304156;
  transition: width 0.3s ease-in-out;
}
.el-header {
  height: 80px;
  padding: 0;
}
.slide-fade-enter-active {
  transition: all 0.3s linear;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(-10px);
  opacity: 0;
}
.el-main {
  background-color: #F2F3F5;
}
.el-footer {
  height: 30px;
  display: flex;
  align-items: center;
  font-size: 12px;
  color: #808080;
}
</style>
