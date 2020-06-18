import Vue from 'vue';
import Index from './showcase-index.vue';

// Vue.component('showcase-index', Index);

/* eslint-disable no-new, no-unused-vars */
new Vue({
  el: '#showcase',
  comments: true,
  components: {
    'showcase-index': Index,
  },
});
