import { createRouter, createWebHashHistory } from 'vue-router';
import { getToken } from '../utils/token';
import { useAppStore } from '../stores/app';
import { BaseConfig } from '../config';
import CommonService from '@/service/CommonService';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/home/Home.vue'),
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        meta: {
          title: '首页',
          is_cache: 1
        },
        component: () => import('../views/home/Dashboard.vue')
      },
      {
        path: 'profile',
        name: 'Profile',
        meta: {
          title: '个人资料',
          is_cache: 1
        },
        component: () => import('../views/home/Profile.vue')
      }
    ]
  },
  {
    path: '/login',
    name: 'Login',
    meta: { title: '登录' },
    component: () => import('../views/Login.vue')
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'PageNotFound',
    meta: { title: '404' },
    component: () => import('../views/error/PageNotFound.vue')
  }
];

const router = createRouter({
  history: createWebHashHistory(),
  routes: routes,
  scrollBehavior: () => ({ left: 0, top: 0 })
});

router.beforeEach(async to => {
  window.document.title = `${to.meta.title || ''} | ${BaseConfig.shortSystemName}`;
  NProgress.start();
  const token = getToken();
  if (token) {
    if (to.name === 'Login') {
      return { name: 'Dashboard' };
    }
    const appStore = useAppStore();
    if (appStore.userInfo.length <= 0) {
      const { data } = await CommonService.initialize();
      appStore.$patch(state => {
        state.isSuper = data.isSuper;
        state.auth = data.auth;
        state.userInfo = data.userInfo;
        state.menuInfo = data.menuInfo;
        state.loginLog = data.loginLog;
      });
      const types = [1,2];
      for (let item of data.menuInfo) {
        if (types.includes(item.menu_type) && item.page_path) {
          router.addRoute('Home', {
            path: item.page_path,
            name: item.file_route_name,
            component: () => import(`../views/${item.dirname}/${item.file_route_name}.vue`),
            meta: {
              title: item.title,
              is_cache: item.is_cache
            }
          });
        }
      }
      return to.fullPath;
    }
  }
  if (!token && to.name !== 'Login') {
    return {
      name: 'Login',
      query: { redirect: encodeURIComponent(to.path) }
    };
  }
})

const notCacheComponents = ['Login', 'PageNotFound'];

router.afterEach(to => {
  if (to.name && !notCacheComponents.includes(to.name)) {
    const appStore = useAppStore();
    if (!appStore.cachedViews.includes(to.name) && to.meta.is_cache === 1) {
      appStore.cachedViews.push(to.name);
    }
    let currentFullPath = to.fullPath, tag = null;
    let isExist = appStore.navTabs.some(v => {
      if (v.name === to.name) {
        currentFullPath = v.fullPath;
        tag = v;
        return true;
      }
    });
    if (!isExist) {
      appStore.navTabs.push({
        name: to.name,
        title: to.meta.title || '',
        fullPath: to.fullPath
      });
    } else {
      if (to.fullPath !== currentFullPath) {
        let i = appStore.navTabs.indexOf(tag);
        if (i > 0) {
          appStore.navTabs[i].fullPath = to.fullPath;
        }
      }
    }
  }
  NProgress.done();
})

export default router;
