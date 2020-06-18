// Masthead Vuex module

export default {
  namespaced: true,
  state: {
    showMobileNav: false,
    showMobileSearch: false,
    showSearch: false,
  },
  getters: {
    mastheadActive: state => (state.showMobileNav || state.showMobileSearch),
  },
  mutations: {
    toggleSearch(state) {
      state.showSearch = !state.showSearch;
    },
    toggleMobileSearch(state) {
      state.showMobileSearch = !state.showMobileSearch;
      if (state.showMobileSearch) {
        state.showMobileNav = false;
      }
    },
    toggleMobileNav(state) {
      state.showMobileNav = !state.showMobileNav;
      if (state.showMobileNav) {
        state.showMobileSearch = false;
      }
    },
    resetNavs(state) {
      state.showMobileNav = false;
      state.showMobileSearch = false;
      state.showSearch = false;
    },
  },
};
