<script setup>
import { useRoute, useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import { BaseConfig } from '@/config';
import { menuConvert } from '@/utils/common';
import { IconPark } from '@icon-park/vue-next/es/all';
import logosvg from '@/assets/svg/logo.svg';

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
// 首页文件名和路由名
const homeRouteName = 'Dashboard';
// 转换为树型结构后的菜单
const menuTrees = menuConvert(appStore.menuInfo.filter(item => {
  return item.menu_type !== 2;
}));

// 默认激活的菜单
const defaultActiveMenu = () => {
  let id = '0';

  if (route.name && route.name !== homeRouteName) {
    for (let item of appStore.menuInfo) {
      if (item.file_route_name === route.name) {
        // 动态菜单取其上级菜单作为选中
        if (item.menu_type === 2) {
          let parent = appStore.menuInfo.filter(v => {
            return v.id === item.parent_id;
          });
          if (parent.length > 0) {
            id = parent[0].id.toString();
          }
        } else {
          id = item.id.toString();
        }
        break;
      }
    }
  }

  return id;
}

// menu-item点击事件
const menuItemClick = item => {
  if (item.menu_type === 1 && item.file_route_name !== route.name) {
    router.push({ name: item.file_route_name });
  }

  if (item.menu_type === 4) {
    window.open(item.page_path);
  }
}
</script>

<template>
  <div class="logo">
    <el-avatar :src="logosvg" />
    <span v-show="!appStore.isCollapseMenu">{{ BaseConfig.shortSystemName }}</span>
  </div>
  <el-scrollbar>
    <el-menu
      background-color="#304156"
      active-text-color="#FFCC33"
      text-color="#bfcbd9"
      unique-opened
      :default-active="defaultActiveMenu()"
      :collapse="appStore.isCollapseMenu"
      :collapse-transition="false"
    >
      <el-menu-item
        index="0"
        @click="menuItemClick({ file_route_name: homeRouteName, menu_type: 1 })"
      >
        <el-icon>
          <icon-park type="home" />
        </el-icon>
        <span>首页</span>
      </el-menu-item>
      <!-- 动态菜单区域，最多支持3级 -->
      <template v-for="f1 of menuTrees" :key="f1.id">
        <!-- 仅1级 -->
        <el-menu-item
          v-if="!f1.children.length"
          :index="f1.id.toString()"
          @click="menuItemClick(f1)"
        >
          <el-icon v-if="f1.icon">
            <icon-park :type="f1.icon" />
          </el-icon>
          <span>{{ f1.title }}</span>
        </el-menu-item>
        <!-- 至少2级 -->
        <el-sub-menu v-else :index="f1.id.toString()">
          <template #title>
            <el-icon v-if="f1.icon">
              <icon-park :type="f1.icon" />
            </el-icon>
            <span>{{ f1.title }}</span>
          </template>
          <template v-for="f2 of f1.children" :key="f2.id">
            <!-- 仅2级 -->
            <el-menu-item
              v-if="!f2.children.length"
              :index="f2.id.toString()"
              @click="menuItemClick(f2)"
            >
              <el-icon v-if="f2.icon">
                <icon-park :type="f2.icon" />
              </el-icon>
              <span>{{ f2.title }}</span>
            </el-menu-item>
            <!-- 至少3级 -->
            <el-sub-menu v-else :index="f2.id.toString()">
              <template #title>
                <el-icon v-if="f2.icon">
                  <icon-park :type="f2.icon" />
                </el-icon>
                <span>{{ f2.title }}</span>
              </template>
              <template v-for="f3 of f2.children" :key="f3.id">
                <!-- 仅3级 -->
                <el-menu-item
                  v-if="!f3.children.length"
                  :index="f3.id.toString()"
                  @click="menuItemClick(f3)"
                >
                  <el-icon v-if="f3.icon">
                    <icon-park :type="f3.icon" />
                  </el-icon>
                  <span>{{ f3.title }}</span>
                </el-menu-item>
                <!-- end 最多支持到3级菜单 -->
              </template>
            </el-sub-menu>
          </template>
        </el-sub-menu>
      </template>
    </el-menu>
  </el-scrollbar>
</template>

<style scoped>
.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60px;
}
.logo .el-avatar {
  vertical-align: middle;
}
.logo span {
  margin-left: 10px;
  white-space: nowrap;
  color: #fff;
  font-weight: 600;
}
.el-scrollbar {
  height: calc(100vh - 60px);
}
.el-menu {
  border-right: none;
}
.el-menu .el-sub-menu .el-menu-item {
  background-color: #1f2d3d !important;
}
.el-menu .el-sub-menu .el-menu-item:hover {
  background-color: #141e2a !important;
}
:deep(.el-menu .el-sub-menu .el-sub-menu__title:hover) {
  background-color: #141e2a !important;
}
</style>
