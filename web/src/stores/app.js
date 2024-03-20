import { defineStore } from 'pinia';

export const useAppStore = defineStore('app', {
  state: () => ({
    isSuper: false,
    userInfo: [],
    menuInfo: [],
    loginLog: [],
    auth:[],
    navTabs: [{ name: 'Dashboard', title: '首页', fullPath: '/dashboard' }],
    cachedViews: ['Dashboard'],
    isCollapseMenu: false,
    isShowComponent: true
  }),
  getters: {
    adminName: state => state.userInfo.nickname || state.userInfo.account
  }
});
