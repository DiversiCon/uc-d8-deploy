import Vue from 'vue';
import Vuex from 'vuex';
import masthead from './modules/masthead';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    masthead,
  },
});
