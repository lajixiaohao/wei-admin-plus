<script setup>
import { ref, reactive, computed, nextTick, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAppStore } from '@/stores/app';
import CommonService from '@/service/CommonService';
import { removeToken } from '@/utils/token';
import { IconPark } from '@icon-park/vue-next/es/all';
import { User, SwitchButton, CloseBold } from '@element-plus/icons-vue';

const commonService = new CommonService();
const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
// 首页文件名和路由名
const homeRouteName = 'Dashboard';
// 右键选中的标签
const selectedTag = ref({});
// 右键菜单默认数据
const contextmenu = reactive({
  visible: false,
  top: 0,
  left: 0
});

// 自动生成面包屑
const breadcrumbMenu = computed(() => {
  const firstElement = [];
  if (route.name !== homeRouteName) {
    firstElement.push({ file_route_name: route.name, title: route.meta.title || '' });
  }
  let current = appStore.menuInfo.filter(v => {
    return v.file_route_name === route.name;
  });
  if (current.length) {
    return getMenuById(current[0].parent_id, firstElement);
  }
  return firstElement;
});

// 通过菜单id获取菜单信息
const getMenuById = (id = 0, ele = []) => {
  for (let item of appStore.menuInfo) {
    if (item.id === id) {
      ele.unshift(item);
      if (item.parent_id > 0) {
        ele = getMenuById(item.parent_id, ele);
      }
      break;
    }
  }
  return ele;
}

// 退出登录
const logout = () => {
  ElMessageBox.confirm('确定注销并退出系统吗？', '提示', {
    type: 'warning'
  }).then(() => {
    commonService.doLogout();
    ElMessage.success('退出成功');
    setTimeout(() => {
      removeToken();
      window.location.reload();
    }, 1000);
  }).catch(() => {});
}

// 账号点击事件分发
const handleCommand = command => {
  switch (command) {
    case 'profile':
      router.push({ name: 'Profile' });
      break;
    case 'logout':
      logout();
      break;
    default:
      console.log(command);
  }
}

// 打开导航标签菜单
const openMenu = (tag, e) => {
  selectedTag.value = tag;
  contextmenu.visible = true;
  contextmenu.top = e.clientY;
  contextmenu.left = e.clientX;
}

// 关闭指定标签
const closeSelectedTag = tag => {
  if (appStore.cachedViews.includes(tag.name)) {
    appStore.cachedViews = appStore.cachedViews.filter(v => {
      return v !== tag.name;
    });
  }
  let j = appStore.navTabs.indexOf(tag);
  if (j > 0) {
    appStore.navTabs.splice(j, 1);
    if (tag.name === route.name) {
      router.push(appStore.navTabs[j - 1].fullPath);
    }
  }
}

// 刷新标签
const refreshSelectedTag = async () => {
  appStore.cachedViews = appStore.cachedViews.filter(v => {
    return v !== selectedTag.value.name;
  });
  appStore.isShowComponent = false;
  await nextTick();
  appStore.isShowComponent = true;
  router.replace(selectedTag.value.fullPath);
}

// 关闭其他标签
const closeOthersTags = () => {
  appStore.cachedViews = appStore.cachedViews.filter(v => {
    return v === homeRouteName || v === selectedTag.value.name;
  });
  appStore.navTabs = appStore.navTabs.filter(v => {
    return v.name === homeRouteName || v.name === selectedTag.value.name;
  });
  if (route.name !== selectedTag.value.name) {
    router.push(selectedTag.value.fullPath);
  }
}

// 关闭全部导航标签
const closeAllTags = () => {
  appStore.$patch(state => {
    state.cachedViews = state.cachedViews.filter(v => {
      return v === homeRouteName;
    });
    state.navTabs = state.navTabs.filter(v => {
      return v.name === homeRouteName;
    });
  });
  if (route.name !== homeRouteName) {
    router.push({ name: homeRouteName });
  }
}

// 关闭导航标签右键菜单
const closeContextMenu = () => {
  contextmenu.visible = false;
}

// 监听contextmenu.visible事件
watch(
  () => contextmenu.visible,
  (visible) => {
    if (visible) {
      document.body.addEventListener('click', closeContextMenu);
    } else {
      document.body.removeEventListener('click', closeContextMenu);
    }
  }
);
</script>

<template>
  <div class="top">
    <div class="breadcrumb">
      <el-icon
        :size="18"
        color="#808080"
        style="margin-right: 10px; margin-top: 2px; cursor: pointer"
        @click="appStore.isCollapseMenu = !appStore.isCollapseMenu"
      >
        <icon-park v-if="appStore.isCollapseMenu" type="menu-fold-one" />
        <icon-park v-else type="menu-unfold-one" />
      </el-icon>
      <el-breadcrumb>
        <el-breadcrumb-item v-if="route.name === homeRouteName">首页</el-breadcrumb-item>
        <el-breadcrumb-item v-else :to="{ name: homeRouteName }">首页</el-breadcrumb-item>
        <template v-for="item of breadcrumbMenu" :key="item.file_route_name">
          <el-breadcrumb-item v-if="item.file_route_name && item.page_path" :to="{ name: item.file_route_name }">{{ item.title }}</el-breadcrumb-item>
          <el-breadcrumb-item v-else>
            <span class="breadcrumb-item">{{ item.title }}</span>
          </el-breadcrumb-item>
        </template>
      </el-breadcrumb>
    </div>
    <div class="info">
      <el-dropdown trigger="click" @command="handleCommand">
        <span class="el-dropdown-link">
          {{ appStore.adminName }}
          <el-icon class="el-icon--right">
            <icon-park type="down-one" theme="filled" />
          </el-icon>
        </span>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item command="profile" :icon="User">个人资料</el-dropdown-item>
            <el-dropdown-item command="logout" :icon="SwitchButton">退出登录</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </div>
  <div class="tags-view">
    <router-link
      v-for="tag of appStore.navTabs"
      :key="tag.name"
      :to="tag.fullPath"
      :class="{ active: route.name === tag.name }"
      class="tags-view-item"
      @contextmenu.prevent.native="openMenu(tag, $event)"
    >
      {{ tag.title }}
      <el-icon
        v-if="tag.name !== homeRouteName"
        class="el-icon-close"
        @click.prevent.stop="closeSelectedTag(tag)"
      >
        <CloseBold />
      </el-icon>
    </router-link>
    <ul
      v-show="contextmenu.visible"
      :style="{ left: contextmenu.left + 'px', top: contextmenu.top + 'px' }"
      class="contextmenu"
    >
      <li @click="refreshSelectedTag">刷新</li>
      <li v-show="selectedTag.name !== homeRouteName" @click="closeSelectedTag(selectedTag)">关闭</li>
      <li @click="closeOthersTags">关闭其他</li>
      <li @click="closeAllTags">关闭全部</li>
    </ul>
  </div>
</template>

<style scoped>
.top {
  box-shadow: 0 1px 1px #e6e6e6;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 30px 0 20px;
}
.breadcrumb,
.info {
  display: flex;
  align-items: center;
}
.el-breadcrumb {
  margin-left: 10px;
}
.breadcrumb-item {
  color: #909399;
}
.el-dropdown-link {
  cursor: pointer;
  display: flex;
}
.tags-view {
  height: 30px;
  display: flex;
  align-items: center;
}
.tags-view-item {
  font-size: 12px;
  padding: 3px 8px;
  margin-top: 2px;
  margin-right: 6px;
  cursor: pointer;
  text-decoration: none;
  color: #6b6b6b;
}
.tags-view-item:first-of-type {
  margin-left: 20px;
}
.tags-view .active {
  background-color: #3399CC;
  color: #f2f2f2;
}
.tags-view .active::before {
  content: '';
  display: inline-block;
  width: 8px;
  height: 8px;
  background-color: #fff;
  border-radius: 50%;
  margin-right: 6px;
}
.tags-view .el-icon-close {
  border-radius: 50%;
  display: inline-block;
  width: 16px;
  height: 16px;
  margin-left: 2px;
  vertical-align: middle;
  text-align: center;
  padding-top: 1px;
}
.tags-view .el-icon-close:hover {
  background-color: #ff6666;
  color: #fff;
}
.contextmenu {
  margin: 0;
  background: #fff;
  z-index: 1000;
  position: absolute;
  list-style-type: none;
  padding: 5px 0;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 400;
  color: #333;
  box-shadow: 2px 2px 3px 0 rgba(0, 0, 0, 0.3);
}
.contextmenu li {
  margin: 0;
  padding: 7px 16px;
  cursor: pointer;
}
.contextmenu li:hover {
  background: #eee;
}
</style>
