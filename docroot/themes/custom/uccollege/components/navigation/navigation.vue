<template>
  <div>
    <!-- desktop nav -->
    <div v-if="!isMobile"
         :class="{'fixed': fixedNav}"
         class="c-nav">
      <div class="c-nav__wrap">
        <a :tabindex="(fixedNav) -1"
           class="c-nav__logo"
           href="/">
          <span>Home</span>
          <uc-icon glyph="logo-crest"
                   aria-hidden="true"/>
        </a>

        <nav>
          <ul>
            <li v-for="(section, index) in navData.items"
                :key="`section-${index}`">
              <a :href="section.link"
                 :target="section.target"
                 class="c-nav__section-link"
                 :aria-haspopup="!!section.links.length"
                 :aria-expanded="!!section.links.length ? 'false' : false"
                 @mouseenter="handleHover($event)"
                 @mouseleave="clearHoverTimer"
                 @keydown.enter.prevent="showDropdown($event)"
                 @focus="hideAllDropdowns()">
                <span>{{ section.section }}</span>
              </a>
              <div v-if="hasDropdown(section)"
                   class="c-nav__dropdown">
                <div class="c-nav__dropdown-wrap">
                  <div class="c-nav__dropdown-sectionLink">
                    <a :href="section.link"
                       :target="section.target">{{ section.section }}</a>
                  </div>

                  <div class="c-nav__dropdown-description">
                    {{ section.text }}
                  </div>

                  <div class="c-nav__dropdown-links" v-if="section.links.length">
                    <ul v-for="(col, index) in splitCols(section.links)"
                        :key="`col-${index}`">
                      <li v-for="(link, index) in col"
                          :key="`link-${index}`">
                        <a :href="link.url"
                           :target="link.target">{{ link.title }}</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </nav>

        <a :class="{ 'active': showSearch }"
           :aria-expanded="showSearch"
           class="c-nav__search"
           href=""
           aria-haspopup="true"
           @click.prevent="toggleSearch($event)">
          <span>{{ (showSearch) ? 'Close' : 'Search' }}</span>
          <uc-icon v-if="!showSearch"
                   glyph="search"
                   aria-hidden="true"/>
          <uc-icon v-if="showSearch"
                   glyph="x-close"
                   aria-hidden="true"/>
        </a>

        <div :class="{ 'c-nav__dropdown--visible': showSearch }"
             class="c-nav__dropdown c-nav__dropdown-search"
             @keyup.prevent.esc="toggleSearch">
          <div class="c-nav__dropdown-wrap">
            <uc-searchform placeholder="Search"
                           theme="inverted"/>
          </div>
        </div>
      </div>
    </div>
    <!-- / desktop nav-->

    <!--mobile search -->
    <div v-if="showMobileSearch && isMobile"
         :class="{ 'c-nav-mobile__search--open' : showMobileSearch }"
         class="c-nav-mobile__search">
      <uc-searchform placeholder="Search"
                     theme="inverted"/>
    </div>

    <!-- mobile nav-->
    <div v-if="showMobileNav"
         :class="{ 'c-nav-mobile--open' : showMobileNav }"
         class="c-nav-mobile">
      <nav>
        <ul class="c-nav-mobile__primary">
          <li v-for="(section, index) in navData.items"
              :key="`section-${index}`">
            <a :href="section.link"
               :target="section.target"
               class="c-nav-mobile__link">
              {{ section.section }}
            </a>
            <a class="c-nav-mobile__toggle"
               href=""
               aria-label="Expand menu"
               aria-haspopup="true"
               aria-expanded="false"
               @click.prevent="expandSubnav($event)"/>
            <ul class="c-nav-mobile__subnav">
              <li v-for="(link, index) in section.links"
                  :key="`link-${index}`">
                <a :href="link.url"
                   :target="link.target">{{ link.title }}</a>
              </li>
            </ul>
          </li>
        </ul>
      </nav>

      <uc-switcher class="c-nav-mobile__switcher">
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
      <div class="c-nav-mobile__spacer"/>
    </div>
    <!-- / mobile nav-->
  </div>
</template>

<script>
import _includes from 'lodash/includes';
import { mapState } from 'vuex';

const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');
const UcSearchform = () => import(/* webpackChunkName: "searchform-lazy" */ '../searchform/searchform.vue');

export default {
  name: 'UcNav',
  components: {
    UcIcon,
    UcSearchform,
  },
  data() {
    return {
      fixedNav: false,
      breakpoint: 1100,
      navData: this.$parent.navData,
      hoverTimer: null,
      hoverReady: false,
    };
  },
  computed: {
    isMobile() {
      return this.$parent.isMobile;
    },
    ...mapState({
      showSearch: state => state.masthead.showSearch,
      showMobileSearch: state => state.masthead.showMobileSearch,
      showMobileNav: state => state.masthead.showMobileNav,
    }),
  },
  mounted() {
    const vm = this;

    // when the page loads, start listening for mouse movement.
    // Until the mouse moves for the first time, disable nav hovers
    window.addEventListener('mouseover', vm.setHoverReady);

    if (!vm.$parent.isMobile) {
      vm.handleScroll();
      window.addEventListener('scroll', vm.handleScroll);
    }

    window.addEventListener('resize', this.debounce(200, vm.resetNav));
  },
  methods: {
    setHoverReady() {
      const vm = this;
      setTimeout(() => {
        vm.hoverReady = true;
        window.removeEventListener('mouseover', vm.setHoverReady);
      }, 100);
    },
    handleScroll() {
      const windowScroll = window.scrollY;
      const mastheadHeight = document.querySelector('.c-masthead__wrap').offsetHeight + this.$el.offsetHeight;
      this.fixedNav = (windowScroll >= (mastheadHeight - this.$el.offsetHeight));
    },
    // Split links into two columns
    splitCols(links) {
      const halfLength = Math.ceil(links.length / 2);
      const col1 = links.slice(0, halfLength);
      const col2 = links.slice(halfLength, links.length);

      return {
        col1,
        col2,
      };
    },
    toggleSearch(event) {
      this.$store.commit('masthead/toggleSearch');
      if (this.showSearch) {
        this.$nextTick(() => {
          document.querySelector('.c-nav__dropdown-wrap .c-searchform__input_container input').focus();
        });
      } else if (event) {
        event.target.parentElement.blur();
      }
    },
    // manually show a dropdown menu, from a keyboard event, etc.
    showDropdown(event) {
      event.target.classList.add('active');
      if (event.target.nextElementSibling) {
        event.target.setAttribute('aria-expanded', 'true');
        event.target.nextElementSibling.classList.add('c-nav__dropdown--visible');
      }
    },
    handleHover(event) {
      const vm = this;
      if(vm.hoverReady) {
        vm.hoverTimer = setTimeout(() => {
          vm.showDropdown(event);
        }, 250);
      }
    },
    clearHoverTimer() {
      clearTimeout(this.hoverTimer);
      setTimeout(() => {
        this.hideAllDropdowns();
      }, 250);
    },
    // manually hide a dropdown menu
    hideDropdown(event) {
      event.target.nextElementSibling.classList.remove('c-nav__dropdown--visible');
    },
    // hide any open dropdowns and remove active class on top-level links
    hideAllDropdowns() {
      const activeNav = document.querySelector('.c-nav__section-link.active');
      const visibleDropdown = document.querySelector('.c-nav__dropdown--visible');

      if (activeNav) {
        activeNav.classList.remove('active');
        activeNav.setAttribute('aria-expanded', 'false');
      }
      if (visibleDropdown) {
        visibleDropdown.classList.remove('c-nav__dropdown--visible');
      }
    },
    // toggle mobile nav
    toggleNav() {
      this.$store.commit('masthead/toggleMobileNav');

      // disable scrolling on the body while menu is open
      const overflow = (this.$store.masthead.showMobileNav) ? 'hidden' : 'auto';
      document.body.style.overflowY = overflow;
    },
    expandSubnav(event) {
      const toggledEl = event.target;
      const subnav = event.target.nextElementSibling;

      // if this subnav is already expanded
      if (_includes(toggledEl.classList, 'expanded')) {
        subnav.classList.remove('expanded');
        toggledEl.classList.remove('expanded');
        toggledEl.setAttribute('aria-expanded', 'false');
      } else {
        // remove expanded class from any other element
        const expanded = this.$el.querySelectorAll('.expanded, [aria-expanded="true"]');
        if (expanded.length !== 0) {
          for (let i = 0; i < expanded.length; i += 1) {
            expanded[i].classList.remove('expanded');
            expanded[i].setAttribute('aria-expanded', 'false');
          }
        }
        setTimeout(() => {
          subnav.classList.add('expanded');
          toggledEl.classList.add('expanded');
          toggledEl.setAttribute('aria-expanded', 'true');
        }, 150);
      }
    },
    resetNav() {
      // Android's keyboard triggers a resize,
      // so only do this on desktop
      setTimeout(() => {
        if (!this.isMobile) {
          this.$store.commit('masthead/resetNavs');
          this.hideAllDropdowns();
        }
      }, 200);
    },
    hasDropdown(section) {
      return section.links instanceof Array && section.links.length > 0;
    },
  },
};
</script>
