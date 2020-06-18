<template>
  <div :class="{'c-events--featured': featured}"
       class="c-events">
    <div class="c-searchform">
      <form autocomplete="off"
            accept-charset="UTF-8"
            @submit.prevent="doSearch">
        <div class="c-searchform__input_container">
          <input v-model="query"
                 type="text"
                 name="query"
                 aria-label="Search for an event"
                 placeholder="Search for an event" >
          <button type="submit"
                  role="button"
                  aria-label="search"
                  @click.prevent="doSearch">
            <uc-icon glyph="search"/>
          </button>
        </div>
      </form>
    </div>

    <article class="c-article">
      <div class="c-article__body">

        <div v-if="!isSearch">
          <div v-for="(value, key, idx) in results"
               :key="`day-${value[0].id}`"
               class="c-events__ctn">
            <h2>{{ value[0].displayDate }}</h2>
            <ul>
              <li v-for="item in value"
                  :key="`item-${item.id}`"
                  class="c-events__item">
                <p v-if="item.status"
                   class="c-events__status">{{ item.status }}</p>
                <p v-if="!item.allDay">{{ formatTime(item.start, item.end) }}</p>
                <p>
                  <!-- Use pathAlias for the link, fallback to id (item.pathAlias includes an initial slash, whereas item.id does not) -->
                  <a :href="item.pathAlias ? `/event${item.pathAlias}` : `/event/${item.id}`">
                    {{ item.title }}
                  </a>
                </p>
                <p v-if="item.location">{{ item.location }}</p>
                <p v-if="item.address">{{ item.address }}</p>
              </li>
            </ul>
          </div>
        </div>

        <div v-else
             class="c-events__ctn c-events__search-results">
          <h2 v-if="results.length === 0"
              class="c-events__no-results">No results to display for "{{ $route.query.query }}"
          </h2>
          <p v-else-if="!isLoading"
             class="c-events__search-summary">
            Displaying results {{ pagination.currentIndex + 1 }}
            - {{ pagination.currentResults }}
            out of {{ pagination.totalResults }} for <strong>{{ $route.query.query }}</strong>
          </p>
          <div v-for="(value, key, idx) in results"
               :key="`day-${value[0].id}`"
               class="c-events__ctn">
            <h2>{{ value[0].displayDate }}</h2>
            <ul>
              <li v-for="item in value"
                  :key="`item-${item.id}`"
                  class="c-events__item">
                <p v-if="item.status"
                   class="c-events__status">{{ item.status }}</p>
                <p v-if="!item.allDay">{{ formatTime(item.start, item.end) }}</p>
                <p>
                  <!-- Use pathAlias for the link, fallback to id (item.pathAlias includes an initial slash, whereas item.id does not) -->
                  <a :href="item.pathAlias ? `/event${item.pathAlias}` : `/event/${item.id}`">
                    {{ item.title }}
                  </a>
                </p>
                <p v-if="item.location">{{ item.location }}</p>
                <p v-if="item.address">{{ item.address }}</p>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </article>

    <uc-pager
      v-show="pagination.totalPages > 1"
      ref="pager"
      :on-page="handlePage"
      :page="pagination.currentPage"
      :total="pagination.totalPages"
      class="c-list__pager"/>
  </div>
</template>

<script>
import _groupBy from 'lodash/groupBy';
import _filter from 'lodash/filter';
import _includes from 'lodash/includes';
import _values from 'lodash/values';
import _clone from 'lodash/clone';
import moment from 'moment';
import EventsAPI from './EventsAPI';
import 'twix';

const ucPager = () => import(/* webpackChunkName: "pager-lazy" */ '../pager/pager.vue');

export default {
  name: 'UcEvents',
  components: {
    ucPager,
  },
  props: {
    endpoint: {
      type: String,
      required: true,
      default: 'http://bsd-data.dev.uchicago.edu/api/event_index',
    },
    instance: {
      type: String,
      required: true,
      default: 'pritzker',
    },
    featured: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      allEvents: null,
      groupedEvents: null,
      filterkey: 'displayDate',
      results: [],
      searchResults: [],
      noResults: null,
      query: null,
      isSearch: (this.query !== null && this.query !== ''),
      pagination: {
        currentIndex: 0,
        currentPage: 1,
        totalPages: 1,
        totalPerPage: 5,
        searchPerPage: 5,
        totalResults: 0,
        currentResults: 0,
        startDate: null,
        endDate: null,
        lastDate: null,
      },
      isLoading: true,
      eventsAPI: new EventsAPI({ endpoint: this.endpoint, instance: this.instance }),
      searchIndex: null,
    };
  },
  mounted() {
    const vm = this;
    vm.resetDateRange();

    // if a search query param is passed, populate
    // the search field and do the search
    if (typeof (vm.$route.query.query) !== 'undefined' && vm.$route.query.query !== '') {
      vm.query = vm.$route.query.query;
      vm.isSearch = true;
    } else {
      vm.isSearch = false;
    }
    // load the json
    this.loadEvents();
  },
  methods: {
    async doSearch() {
      const vm = this;
      vm.resetPager();
      vm.isLoading = true;

      // if the query is empty, the user may have just cleared the form
      // after a previous search, so reload events
      if (vm.query === '') {
        vm.isSearch = false;
        vm.pagination.currentIndex = 0;

        vm.removeQuery();
        vm.loadEvents();
      } else {
        vm.isSearch = true;
        // update the query string in the URL
        vm.updateQuery('query', vm.query);

        // Initialize the matches data set.
        let matches = [];

        // If there is a search query, perform the search. Otherwise set the matches to the complete data set.
        if (vm.query !== '') {
          // Create an array of search tokens by splitting apart the query via spaces between words.
          const searchTokens = vm.query.toLowerCase().split(' ');

          matches = _filter(vm.allEvents, (event) => {
            // Sentinel value when locating a match.
            let matchFound = false;

            // This will contain all searchable data points for a faculty member.
            let searchableData = [];

            searchableData = searchableData.concat(event.title.toLowerCase().split(' '));

            // Loop through the searchable data. If any match is found, return.
            // We use `some` instead of `forEach` due to being able to terminate early.
            searchableData.some((searchableDatum) => {
              // Loop through the search tokens looking for a match.
              searchTokens.some((token) => {
                if (searchableDatum === token) {
                  // A match is found!
                  matchFound = true;
                  return true;
                }
              });

              // Terminate the loop early if a match is found.
              if (matchFound) {
                return true;
              }
            });

            // Return the sentinel value. If true, this data item will be returned as a match.
            return matchFound;
          });
        }
        else {
          matches = vm.allData;
        }

        if (matches.length === 0) {
          vm.noResults = true;
          vm.results = [];
          vm.searchResults = [];
          vm.pagination.totalPages = 0;
        } else {
          vm.noResults = false;
          vm.pagination.totalResults = Object.keys(matches).length;
          vm.pagination.currentResults = (vm.pagination.totalResults > vm.pagination.totalPerPage) ? vm.pagination.totalPerPage : vm.pagination.totalResults;
          vm.processResults(matches);
        }
      }
    },
    async getPaginationInfo() {
      const vm = this;
      if (vm.groupedEvents != null) {
        vm.pagination.totalPages = Math.ceil(vm.groupedEvents.length / vm.pagination.totalPerPage);
      }
    },
    async loadEvents() {
      const vm = this;
      vm.isLoading = true;

      vm.allEvents = await vm.eventsAPI.getAllEvents();

      vm.noResults = (vm.allEvents.length === 0);
      if (vm.isSearch) {
        vm.doSearch();
      } else if (!vm.noResults) {
        vm.processResults();
      }
    },
    async processResults(events = this.allEvents) {
      const vm = this;
      vm.getPaginationInfo();
      // loop over all data and split multi-day
      // events into separate entries
      const expandedResults = [];

      events.forEach((value) => {
        // does this span multiple days?
        if (value.start !== value.end) {
          // start and end dates of the event
          const start = moment.utc(value.start).format('YYYY-MM-DD');
          const end = moment.utc(value.end).format('YYYY-MM-DD');

          // creat an array of each unique day of the event
          const rawDates = moment(start).twix(end).toArray('days');

          // for each day in the range, copy the event
          for (let i = 0; i < rawDates.length; i++) {
            const newRecord = _clone(value);
            newRecord.displayDate = vm.formatDate(rawDates[i]);
            expandedResults.push(newRecord);
          }
        } else {
          value.displayDate = vm.formatDate(value.start);
          expandedResults.push(value);
        }
      });

      // group by unique days
      const groupedObject = await _groupBy(expandedResults, vm.filterkey);
      vm.groupedEvents = _values(groupedObject);
      vm.getPagedResults(1);
      vm.isLoading = false;
    },
    getPagedResults(pageNum) {
      const vm = this;
      vm.getPaginationInfo();
      const start = (pageNum === 1) ? 0 : ((pageNum - 1) * vm.pagination.totalPerPage);
      const end = start + vm.pagination.totalPerPage;
      vm.results = vm.groupedEvents.slice(start, end);
    },
    formatDate(date) {
      return moment.utc(date).format('dddd, MMMM D, YYYY');
    },
    formatDateRange(start, end) {
      let dateStringStart = moment.utc(start).format('ddd, MMM DD, YYYY');
      const dateStringEnd = moment.utc(end).format('ddd, MMM DD, YYYY');
      if (dateStringStart !== dateStringEnd) {
        dateStringStart += ' - ' + dateStringEnd;
      }
      return dateStringStart;
    },
    formatTime(start, end) {
      let timeString = moment.utc(start).format('h:mm a');
      timeString += (start !== end) ? ' - ' + moment.utc(end).format('h:mm a') : '';
      return timeString;
    },
    handlePage(pageNum) {
      // Handles clicks on pagination links
      const vm = this;
      vm.getPagedResults(pageNum);
      vm.scrollToTop();
    },
    resetPager() {
      if (this.pagination.totalPages > 1) {
        this.$refs.pager.currentPage = 1;
        this.pagination.currentIndex = 0;
      }
    },
    scrollToTop() {
      const currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
      const scrollTarget = this.$el.offsetTop - 40;
      if (currentScroll > scrollTarget) {
        window.requestAnimationFrame(this.scrollToTop);
        // a higher number = slower scroll
        window.scrollTo(scrollTarget, currentScroll - (currentScroll / 15));
      }
      this.focusTop();
    },
    focusTop() {
      // get the first focusable link in the results
      setTimeout(() => {
        const focusable = this.$el.querySelector('.c-events__item a');
        if (focusable) {
          focusable.focus();
        }
      }, 150);
    },
    updateQuery(name, value) {
      // update the url query params on selection
      const query = Object.assign({}, this.$route.query);
      query[name] = value;
      this.$router.replace({ query });
    },
    removeQuery() {
      const query = Object.assign({}, this.$route.query);
      delete query.query;
      this.$router.replace({ query });
    },
    resetDateRange() {
      this.pagination.startDate = moment().format('YYYY-MM-DD');
      this.pagination.endDate = moment().add(1, 'weeks').format('YYYY-MM-DD');
    },
  },
};
</script>
