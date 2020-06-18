import 'babel-polyfill'; // for IE11
import 'es6-promise/auto'; // for IE11
import 'lazysizes'; // lazy-loaded images
import 'lazysizes/plugins/attrchange/ls.attrchange';

/* eslint-disable import/order  */
// These imports will be loaded immediately into site.js
// only components that are always loaded globally, or have
// a high possibility to be on most pages should be imported here
import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuelidate from 'vuelidate';
import store from '../store/store';
import Caption from '../../components/caption/caption.vue';
import Icon from '../../components/icon/icon.vue';
import Masthead from '../../components/masthead/masthead.vue';
import Navigation from '../../components/navigation/navigation.vue';
import Photo from '../../components/photo/photo.vue';
import Pullquote from '../../components/pullquote/pullquote.vue';
import Skip from '../../components/skip-nav/skip-nav.vue';
import ShareLinks from '../../components/share-links/share-links.vue';
import Switcher from '../../components/switcher/switcher.vue';
import { square, squareFullWidth } from './directives';

// page that uses the corresponding Vue component
// These dynamic imports are saved to bundled files and loaded on any
// page that includes the component
const UcAccordion = () => import(/* webpackChunkName: "accordion-lazy" */ '../../components/accordion/accordion.vue');
const UcAccordionGroup = () => import(/* webpackChunkName: "accordion-group-lazy" */ '../../components/accordion/accordion-group.vue');
const UcAlert = () => import(/* webpackChunkName: "alert-lazy" */ '../../components/alert/alert.vue');
const UcAlphalist = () => import(/* webpackChunkName: "alphalist-lazy" */ '../../components/alphalist/alphalist.vue');
const UcDropdown = () => import(/* webpackChunkName: "dropdown-lazy" */ '../../components/dropdown/dropdown.vue');
const UcEvents = () => import(/* webpackChunkName: "events-lazy" */ '../../components/events/events.vue');
const UcEventsFeatured = () => import(/* webpackChunkName: "events-featured-lazy" */ '../../components/events/events-featured.vue');
const UcEventsCallout = () => import(/* webpackChunkName: "events-featured-lazy" */ '../../components/events/events-callout.vue');
const UcFacultyCallout = () => import(/* webpackChunkName: "faculty-callout-lazy" */ '../../components/faculty/faculty-callout.vue');
const UcForm = () => import(/* webpackChunkName: "form-lazy" */ '../../components/form/form.vue');
const UcGallery = () => import(/* webpackChunkName: "gallery-lazy" */ '../../components/gallery/gallery.vue');
const UcGmap = () => import(/* webpackChunkName: "gmap-lazy" */ '../../components/gmap/gmap.vue');
const UcGrouplist = () => import(/* webpackChunkName: "grouplist-lazy" */ '../../components/grouplist/grouplist.vue');
const UcHeroRotator = () => import(/* webpackChunkName: "hero-rotator-lazy" */ '../../components/hero-rotator/hero-rotator.vue');
const UcLinkIndicator = () => import(/* webpackChunkName: "link-indicator-lazy" */ '../../components/link-indicator/link-indicator.vue');
const UcList = () => import(/* webpackChunkName: "list-lazy" */ '../../components/list/list.vue');
const UcMapHero = () => import(/* webpackChunkName: "map-hero-lazy" */ '../../components/map-hero/map-hero.vue');
const UcPubList = () => import(/* webpackChunkName: "publist-lazy" */ '../../components/pub-list/pub-list.vue');
const UcQuotator = () => import(/* webpackChunkName: "quotator-lazy" */ '../../components/quotator/quotator.vue');
const UcSearchform = () => import(/* webpackChunkName: "searchform-lazy" */ '../../components/searchform/searchform.vue');
const UcSelectorGroup = () => import(/* webpackChunkName: "selector-group-lazy" */ '../../components/selector-group/selector-group.vue');
const UcSelectorProgram = () => import(/* webpackChunkName: "selector-program-lazy" */ '../../components/selector-program/selector-program.vue');
const UcShowcaseIndex = () => import(/* webpackChunkName: "showcase-lazy" */ '../../it_showcase/js/showcase-index.vue');
const UcSlider = () => import(/* webpackChunkName: "slider-lazy" */ '../../components/slider/slider.vue');
const UcTeaser = () => import(/* webpackChunkName: "teaser-lazy" */ '../../components/teaser/teaser.vue');
const UcVideo = () => import(/* webpackChunkName: "video-lazy" */ '../../components/video/video.vue');
const UcVideoBackground = () => import(/* webpackChunkName: "video-background-lazy" */ '../../components/video-background/video-background.vue');

// These dynamic imports are also bundled, but are loaded via
// rel="prefetch" to defer their loading until after other scripts
const UcTintEmbed = () => import(/* webpackChunkName: "tint-lazy" */ /* webpackPrefetch: true */ '../../components/tint-embed/tint-embed.vue');

window.Vue = Vue;

Vue.use(VueRouter);
Vue.use(Vuelidate);

const router = new VueRouter({
  mode: 'history',
});

// global data store
const ucStore = {
  tintLoaded: false,
};

// Register components
Vue.component('uc-caption', Caption);
Vue.component('uc-icon', Icon);
Vue.component('uc-masthead', Masthead);
Vue.component('uc-navigation', Navigation);
Vue.component('uc-photo', Photo);
Vue.component('uc-pullquote', Pullquote);
Vue.component('uc-skip-nav', Skip);
Vue.component('uc-share-links', ShareLinks);
Vue.component('uc-switcher', Switcher);


// global mixins
Vue.mixin({
  created() {
    // listen for any Tint component to
    // emit an 'embed-ready' event
    this.$on('embed-ready', () => {
      this.loadTintScript();
    });
  },
  methods: {
    debounce(delay, fn) {
      let timerId;
      return (...args) => {
        if (timerId) {
          clearTimeout(timerId);
        }
        timerId = setTimeout(() => {
          fn(...args);
          timerId = null;
        }, delay);
      };
    },
    loadTintScript() {
      // include Tint's js script, only if it's not
      // been included once before
      if (!ucStore.tintLoaded) {
        const tintScript = document.createElement('script');
        tintScript.src = 'https://cdn.hypemarks.com/pages/a5b5e5.js';
        tintScript.defer = 'true';
        document.head.appendChild(tintScript);
        ucStore.tintLoaded = true;
      }
    },
  },
});

// Polyfill for .prepend()
// https://developer.mozilla.org/en-US/docs/Web/API/ParentNode/prepend#Polyfill
(function prependPoly(arr) {
  arr.forEach((item) => {
    if (Object.prototype.hasOwnProperty.call(item, 'prepend')) {
      return;
    }
    Object.defineProperty(item, 'prepend', {
      configurable: true,
      enumerable: true,
      writable: true,
      value: function prepend(...argArr) {
        // const argArr = Array.prototype.slice.call(arguments);
        const docFrag = document.createDocumentFragment();

        argArr.forEach((argItem) => {
          const isNode = argItem instanceof Node;
          docFrag.appendChild(isNode ? argItem : document.createTextNode(String(argItem)));
        });

        this.insertBefore(docFrag, this.firstChild);
      },
    });
  });
}([Element.prototype, Document.prototype, DocumentFragment.prototype]));

Vue.directive('square', square);
Vue.directive('square-full-width', squareFullWidth);

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  comments: true,
  components: {
    UcAccordion,
    UcAccordionGroup,
    UcAlert,
    UcAlphalist,
    UcDropdown,
    UcEvents,
    UcEventsCallout,
    UcEventsFeatured,
    UcFacultyCallout,
    UcForm,
    UcGallery,
    UcGmap,
    UcGrouplist,
    UcHeroRotator,
    UcList,
    UcLinkIndicator,
    UcMapHero,
    UcPubList,
    UcQuotator,
    UcSearchform,
    UcSelectorGroup,
    UcSelectorProgram,
    UcShowcaseIndex,
    UcSlider,
    UcTeaser,
    UcVideo,
    UcVideoBackground,
    UcTintEmbed,
  },
});
