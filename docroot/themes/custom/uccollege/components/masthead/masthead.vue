<template>
  <div :class="{ 'c-masthead--navopen' : mastheadActive, 'c-masthead--nonav' : !showNav }"
       class="c-masthead">
    <div class="c-masthead__wrap">

      <a v-if="!mastheadActive"
         href="https://www.uchicago.edu"
         class="c-masthead__logo"
         aria-label="The University of Chicago web site">

        <img v-show="!isMobile"
             src="/themes/custom/uccollege/uc-logo.svg" />

        <uc-icon v-show="isMobile"
                 glyph="logo-crest"/>
      </a>
      <a v-else
         :aria-expanded="showMobileSearch"
         :class="{ 'active': showMobileSearch }"
         class="c-masthead__search"
         href=""
         aria-haspopup="true"
         @click.prevent="toggleSearch">
        <span>{{ (showMobileSearch) ? 'Close' : 'Search' }}</span>
        <uc-icon v-if="!showMobileSearch"
                 glyph="search"
                 aria-hidden="true"/>
        <uc-icon v-if="showMobileSearch"
                 glyph="x-close"
                 aria-hidden="true"/>
      </a>

      <a href="/"
         class="c-masthead__thecollege">
        <img :src="navData.site_logo"
             :alt="navData.site_logo_alt"
             class="c-masthead__site-logo">
      </a>

      <a v-if="isMobile"
         :class="{ 'active' : showMobileNav }"
         class="c-nav__toggle"
         href="#"
         @click.prevent="toggleMenu">
        <span>Menu Toggle</span>
      </a>

      <uc-switcher v-if="!isMobile"
                   class="c-masthead__nav">
        <template v-for="(type, index) in navData.switcher"
                  :slot="type.type">
          <ul :key="`type-${index}`">
            <li v-for="(link, index) in type.links"
                :key="`link-${index}`">
              <a :href="link.url"
                 :target="link.target">{{ link.title }}</a>
            </li>
          </ul>
        </template>
      </uc-switcher>
    </div>

    <uc-navigation v-if="showNav"/>
  </div>
</template>

<script>
import { mapState } from 'vuex';

const UcSwitcher = () => import(/* webpackChunkName: "switcher-lazy" */ '../switcher/switcher.vue');
const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');

export default {
  name: 'Masthead',
  components: {
    UcSwitcher,
    UcIcon,
  },
  props: {
    navData: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      prevWindowTop: 0,
      hideMasthead: false,
      breakpoint: 1100,
      isMobile: window.innerWidth < 1100,
      state: this.$store.state,
    };
  },
  computed: {
    mastheadActive() {
      return this.$store.getters['masthead/mastheadActive'];
    },
    ...mapState({
      showSearch: state => state.masthead.showSearch,
      showMobileSearch: state => state.masthead.showMobileSearch,
      showMobileNav: state => state.masthead.showMobileNav,
    }),
    showNav() {
      return this.navData.items instanceof Array && this.navData.items.length > 0;
    },
  },
  mounted() {
    const vm = this;
    if (!vm.isMobile) {
      window.addEventListener('scroll', this.debounce(200, vm.handleScroll));
    }
    window.addEventListener('resize', this.debounce(200, vm.checkMobile));
    vm.$on('onSearchFormToggle', vm.toggleMenu);

    if (!vm.showNav) {
      const body = document.getElementsByTagName('body')[0];
      body.classList.add('no-nav');
    }
  },
  methods: {
    checkMobile() {
      this.isMobile = (window.innerWidth < this.breakpoint);
    },
    toggleSearch() {
      this.$store.commit('masthead/toggleMobileSearch');
      if (this.showMobileSearch) {
        this.$nextTick(() => {
          document.querySelector('.c-nav-mobile__search .c-searchform__input_container input').focus();
        });
      }
    },
    toggleMenu() {
      this.$store.commit('masthead/toggleMobileNav');
    },
    handleScroll() {
      const windowScroll = window.scrollY;

      if (windowScroll > this.prevWindowTop && windowScroll > this.$el.offsetHeight) {
        this.hideMasthead = true;
      } else if ((windowScroll < this.prevWindowTop && windowScroll > this.$el.offsetHeight)
        || windowScroll === 0) {
        this.hideMasthead = false;
      }
      this.prevWindowTop = windowScroll;
    },
  },
};
</script>
