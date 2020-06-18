<template>
  <div class="c-list">

    <div v-if="noResults"
         class="c-list__noresults">
      <h1>No results</h1>
    </div>

    <div :class="{ 'c-content--sidebar-right' : sidebar }"
         class="c-list__content-wrap c-content">
      <div v-if="filtered && !isMobile"
           class="c-list__skip">
        <a href="#list-filters">Skip to filters</a>
      </div>

      <section id="list-results"
               class="c-list__content">

        <uc-sorter
          v-if="ready && isSearch && !noResults"
          :sort-by="sortBy"
          :start="sorterData.start"
          :end="sorterData.end"
          :total="sorterData.total"/>

        <div v-if="!noResults">
          <div v-for="item in results"
               :key="`list-${item.nid}`"
               class="c-list__item">

            <div class="c-list__item-image">
              <a :href="item.url"
                 :target="item.url_target"
                 class="c-list__item-imagelink">
                <div class="c-list__item-imagewrap">
                  <svg class="c-icon">
                    <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix" />
                  </svg>
                  <img v-if="item.image"
                       :data-src="item.image"
                       :alt="getAlt(item)"
                       class="lazyload" >
                </div>
              </a>
            </div>

            <div class="c-list__item-text">
              <h3 class="c-list__item-title">
                <a :href="item.url"
                   :target="item.url_target"
                   class="c-list__item-titlelink">{{ item.title }}</a>
              </h3>

              <div v-if="item.description"
                   class="c-list__item-description"
                   v-html="item.description"/>

              <p v-if="item.source || item.date"
                 class="c-list__item-meta">
                <span v-if="item.author"
                      class="c-list__item-source">
                  By <a :href="item.authorurl">{{ item.author }}</a></span>
                <span v-if="item.author && item.date"
                      class="c-list__separator"> | </span>
                <time v-if="item.date"
                      :datetime="item.datetime"
                      class="c-list__item-date">{{ item.date }}</time>
              </p>
            </div>
          </div>
        </div>
      </section>

      <uc-pager
        v-if="ready && paginate && !noResults"
        :on-page="handlePage"
        :page="pagerData.current"
        :total="pagerData.total"
        class="c-list__pager"/>

      <div v-if="sidebar"
           class="c-list__sidebar c-content__sidebar">
        <uc-filter
          v-if="ready && filtered && !isMobile"
          id="list-filters"
          :on-select="handleSelect"
          :categories="categories"
          :pre-selected="preSelected"
          :title="filterTitle"
          class="show-for-large"/>
        <slot/>
      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';

const UcFilter = () => import(/* webpackChunkName: "filter-lazy" */ '../filter/filter.vue');
const UcPager = () => import(/* webpackChunkName: "pager-lazy" */ '../pager/pager.vue');
const UcSorter = () => import(/* webpackChunkName: "sorter-lazy" */ '../sorter/sorter.vue');

export default {
  name: 'UcList',
  components: {
    UcFilter,
    UcPager,
    UcSorter,
  },
  props: {
    datasource: {
      type: String,
      required: true,
    },
    categorysource: String,
    itemsPerPage: {
      type: Number,
      default: 10,
    },
    filtered: {
      type: Boolean,
      default: true,
    },
    sidebar: {
      type: Boolean,
      default: true,
    },
    paginate: {
      type: Boolean,
      default: true,
    },
    sorted: {
      type: Boolean,
      default: false,
    },
    isSearch: {
      type: Boolean,
      default: false,
    },
    preSelected: {
      type: String,
    },
    filterTitle: String,
  },
  data() {
    return {
      results: null,
      categories: null,
      current: 1,
      ready: false,
      endpoint: this.datasource,
      pagerData: null,
      selected: this.preSelected,
      noResults: false,
      scrollTo: null,
      firstScroll: true,
      sortBy: 'rank',
      isMobile: window.innerWidth < 1024,
    };
  },
  computed: {
    sorterData() {
      const output = {};
      output.start = (this.itemsPerPage * this.pagerData.current - this.itemsPerPage) + 1;
      output.end = output.start + (this.results.length - 1);
      output.total = this.pagerData.totalResults;
      return output;
    },
  },
  watch: {
    endpoint() {
      this.getData();
    },
  },
  mounted() {
    const vm = this;
    vm.scrollTo = vm.$el.offsetTop - 40;

    window.addEventListener('resize', () => {
      vm.isMobile = window.innerWidth < 1024;
    });

    if (vm.isSearch) {
      // get search query from url
      if (vm.$route.query.query) {
        vm.getData();
      } else {
        vm.noResults = true;
      }

      // when the user clicks a new sort order,
      // change the value and re-fetch results
      this.$on('onSortChange', (type) => {
        vm.sortBy = type;
        vm.getData();
      });
    } else {
      // pre-chosen sections from query string
      if (vm.$route.query.section) {
        vm.selected = vm.$route.query.section;
        // pre-chosen sections from props
      } else if (vm.preSelected) {
        vm.selected = vm.preSelected;
      } else {
        vm.selected = 'all';
      }

      vm.getCategories();
    }
  },
  methods: {
    getCategories() {
      const vm = this;

      axios.get(vm.categorysource)
        .then((response) => {
          // get categories. This only happens once.
          vm.categories = response.data.categories;

          // now get all the results
          vm.getData();
        })
        .catch((error) => {
          // handle error
          console.log(error);
        })
        .then(() => {
          // always executed
        });
    },
    getData(start) {
      const vm = this;
      const query = (!vm.isSearch) ? vm.selected : vm.$route.query.query;
      let url = vm.endpoint;
      url += (vm.isSearch) ? `${vm.sortBy}/${query}` : query;
      url += `?items_per_page=${vm.itemsPerPage}`;

      if (start) {
        url += `&page=${start - 1}`;
      }

      axios.get(url)
        .then((response) => {
          vm.results = response.data.items;
          vm.noResults = (vm.results.length === 0);

          if (!vm.noResults) {
            // Loop through the results. If there is an authored URL present, override the existing
            // URL data with that value.
            vm.results.forEach((result) => {
              result.url_target = '';
              if (typeof result.authored_url !== 'undefined' && result.authored_url !== '') {
                result.url = result.authored_url;
                result.url_target = '_blank';
              }
            });

            vm.pagerData = response.data.pager;
          }
          vm.ready = true;
        })
        .catch((error) => {
          // handle error
          console.log(error);
        })
        .then(() => {
          vm.scrollToTop();
        });
    },
    handleSelect(selections) {
      this.selected = (selections !== 'all') ? selections.join(',') : 'all';
      this.getData();
    },
    handlePage(pageNum) {
      this.getData(pageNum);
    },
    getAlt(item) {
      return (item.imagealt) ? item.imagealt : item.title;
    },
    scrollToTop() {
      const currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
      if (currentScroll > this.scrollTo) {
        window.requestAnimationFrame(this.scrollToTop);
        // a higher number = slower scroll
        window.scrollTo(this.scrollTo, currentScroll - (currentScroll / 15));
      } else {
        this.focusTop();
      }
    },
    // after scrolling to top, focus on first list item
    focusTop() {
      if (this.firstScroll) {
        this.firstScroll = false;
      } else if (this.selected === 'all') {
        // get the first focusable link in the results
        const focusable = this.$el.querySelector('.c-list__item a');
        if (focusable) {
          focusable.focus();
        }
      }
    },
  },
};
</script>
