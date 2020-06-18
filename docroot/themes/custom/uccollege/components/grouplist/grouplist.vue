<template>
  <div class="c-grouplist">
    <div ref="controls"
         class="c-grouplist__controls">

      <div class="c-searchform c-grouplist__searchform">
        <form
          autocomplete="off"
          accept-charset="UTF-8"
          @submit.prevent="searchGroups">
          <div class="c-searchform__input_container">
            <input
              v-model="search"
              type="text"
              name="search"
              placeholder="Search">
            <button
              type="submit"
              @click.prevent="searchGroups">
              <uc-icon glyph="search"/>
            </button>
          </div>
        </form>
      </div>

      <uc-dropdown
        ref="dropdownTopic"
        :options="topicDropdownOptions"
        :default-selection="topicDropdownSelected"
        :on-select="handleTopicDropdownChange"
        display-name="All Topics"
        class="c-grouplist__dropdown"
        name="topic" />

    </div>

    <section class="c-grouplist__search-info">
      <div v-if="noResults && isSearchResults"
           class="c-grouplist__noresults">
        <h2>No results found.</h2>
      </div>
      <p v-else-if="isSearchResults" class="c-grouplist__search-summary">
        Displaying {{ results.length }} results for <strong>{{ $route.query.search }}</strong>
      </p>
    </section>

    <ol v-if="!noResults" class="c-grouplist__results-list">
      <li
        v-for="result in pagedResults"
        class="c-grouplist__result">
        <div class="c-grouplist__result-image">
          <div class="c-grouplist__result-imagewrap">
            <svg class="c-icon">
              <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix" />
            </svg>

            <img v-if="result.image"
               class="lazyload"
               :data-src="result.image"
               :alt="result.name" />
          </div>
        </div>
        <div class="c-grouplist__result-content">
          <h3 class="c-grouplist__result-name">
            <a :href="result.url" :target="result.url_target">{{ result.name }}</a>
          </h3>
          <!--
          <p v-if="result.members > 0" class="c-grouplist__result-members"><strong>Members:</strong> {{ result.members }}</p>
          -->
          <p v-if="result.latestPublication" class="c-grouplist__result-publication">
            <strong>Latest Publication:</strong>
            <a :href="result.latestPublication.url" :target="result.latestPublication.url_target">{{ result.latestPublication.name }}</a>
          </p>
          <p class="c-grouplist__result-topics" v-if="result.topics.length > 0">
            <strong>Tags: </strong>
            {{ result.topics.join(', ') }}
          </p>
          <div class="c-grouplist__result-desc" v-html="result.description"></div>
        </div>
      </li>
    </ol>

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
  import axios from 'axios';
  import _filter from 'lodash/filter';
  import _uniqBy from 'lodash/uniqBy';
  import _orderBy from 'lodash/orderBy';

  const ucPager = () => import(/* webpackChunkName: "pager-lazy" */ '../pager/pager.vue');
  const ucDropdown = () => import(/* webpackChunkName: "dropdown-lazy" */ '../dropdown/dropdown.vue');

  export default {
    name: 'uc-grouplist',
    components: {
      ucPager,
      ucDropdown,
    },
    props: {
      endpoint: {
        type: String,
        default: '/it/showcase/endpoint/grouplist-endpoint',
      },
    },
    data() {
      return {
        groups: [],
        results: [],
        pagedResults: [],
        search: '',
        isSearchResults: false,
        noResults: false,
        pagination: {
          currentIndex: 0,
          currentPage: 1,
          totalPages: 1,
          totalPerPage: 10,
          totalResults: 0,
          currentResults: 0,
        },
        isDropdownSort: false,
        topicDropdownOptions: [],
        topicDropdownSelected: 'All',
      };
    },
    async mounted() {
      const vm = this;

      // Check if the query in the router has any value.
      if (vm.$route.query.search !== null && vm.$route.query.search) {
        vm.search = vm.$route.query.search;
      }

      if (vm.$route.query.topic !== null && vm.$route.query.topic) {
        vm.topicDropdownSelected = vm.$route.query.topic;
      }

      await vm.initialize();

      // Load the year dropdown.
      vm.loadTopicDropdown();
    },
    methods: {
      async initialize() {
        const vm = this;

        // Retrieve all groups via endpoint.
        try {
          const response = await axios.get(vm.endpoint);

          vm.groups = vm.normalize(response.data.items);
          vm.results = vm.groups;
        }
        catch (error) {
          vm.log(error, 'error');
        }

        // Display the first page of results, and relevant pagination.
        vm.getPagedResults(1);
        vm.getPaginationInfo();

        // If we have a search query present, initiate a search of the groups data set.
        if (vm.queryHasValue()) {
          vm.searchGroups();
        }
      },
      /**
       * Performs a search of the groups based on entered search text.
       */
      searchGroups() {
        const vm = this;

        vm.updateQueryString();
        vm.isSearchResults = false;

        let matches = vm.groups;

        if (vm.queryHasValue()) {
          vm.isSearchResults = true;
          // Create an array of search tokens by splitting apart the query via spaces between words.
          const searchTokens = vm.search.toLowerCase().split(' ');

          matches = _filter(vm.groups, (group) => {
            // Sentinel value when locating a match.
            let matchFound = false;

            // This will contain all searchable data points for a faculty member.
            let searchableData = [];

            searchableData = searchableData.concat(group.name.toLowerCase().replace(/[,.]/g, '').split(' '));

            if (group.topics) {
              group.topics.forEach((topic) => {
                searchableData = searchableData.concat(topic.toLowerCase().split(' '));
              });
            }

            // Remove any duplicate terms.
            searchableData = _uniqBy(searchableData);

            // Iterate through the searchable data looking for a match.
            searchableData.some((searchableDatum) => {
              searchTokens.some((token) => {
                if (searchableDatum === token) {
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

        if (vm.isSearchResults) {
          // If no matches are found, set the appropriate state.
          if (matches.length === 0) {
            // display no results
            vm.noResults = true;
            vm.results = [];
          }
          else {
            // prepare and display results
            vm.noResults = false;
            vm.results = matches;
          }
        }
        else {
          vm.noResults = false;
          vm.results = vm.groups;
        }

        // Display the first page of results and update pagination info.
        vm.getPagedResults(1);
        vm.getPaginationInfo();
      },
      /**
       * Converts data into the structure needed by the Vue component.
       *
       * @param data {array}
       *    The data structure to be normalized.
       *
       * @return {array}
       *    The normalized data structure.
       */
      normalize(data) {
        const normalizedData = [];

        // Loop through the data items, convert each one to the new structure,
        // and add it to new normalized data list.
        data.forEach((datum) => {
          const normalizedDatum = {};

          ({
            name: normalizedDatum.name,
            sort_name: normalizedDatum.sort_name,
            url: normalizedDatum.url,
            url_target: normalizedDatum.url_target,
            image: normalizedDatum.image,
            members: normalizedDatum.members,
            description: normalizedDatum.description,
            topics: normalizedDatum.topics,
            latest_publication: normalizedDatum.latestPublication,
          } = datum);

          normalizedData.push(normalizedDatum);
        });

        return normalizedData;
      },
      /**
       * Update the paged results based on the given page number.
       *
       * @param pageNum {int}
       */
      getPagedResults(pageNum) {
        const vm = this;

        const start = (pageNum === 1) ? 0 : ((pageNum - 1) * vm.pagination.totalPerPage);
        const end = start + vm.pagination.totalPerPage;
        vm.pagedResults = vm.results.slice(start, end);
      },
      /**
       * Calculates the total pages based on current result set and number per page.
       */
      getPaginationInfo() {
        const vm = this;

        vm.pagination.totalPages = Math.ceil(vm.results.length / vm.pagination.totalPerPage);
      },
      /**
       * Loads the topic dropdown with data.
       */
      loadTopicDropdown() {
        const vm = this;

        // Initialize the dropdown options, starting with the 'All' default option.
        let topicOptions = [
          {
            name: 'All Topics',
            value: 'All',
          },
        ];

        // Loop through the groups and build the list of options.
        vm.groups.forEach((group) => {
          if (group.topics) {
            group.topics.forEach((topic) => {
              topicOptions.push({ name: topic, value: topic });
            });
          }
        });

        // Remove duplicate values, and order the list of options.
        topicOptions = _uniqBy(topicOptions, 'name');
        topicOptions = _orderBy(topicOptions, 'name');

        vm.topicDropdownOptions = topicOptions;

        // During load, if there is a selected value set other than the default, trigger
        // the handle function to filter the list.
        if (vm.topicDropdownSelected !== 'All') {
          vm.$refs.dropdownTopic.handleSelect(
            {
              name: vm.topicDropdownSelected,
              value: vm.topicDropdownSelected,
            },
            false
          );
        }
      },
      /**
       *  Handles the page change event.
       *
       *  @param pageNum {int}
       */
      handlePage(pageNum) {
        const vm = this;

        // Since the page is change, scroll to the top of the list to display the start of the new page.
        vm.scrollToTop();

        // Load the next page of results.
        vm.getPagedResults(pageNum);
      },
      /**
       * Handle a select change for the topic dropdown.
       */
      handleTopicDropdownChange(topicSelected) {
        const vm = this;

        // Reset any search state.
        vm.resetSearch();

        // Initialize our state variables.
        vm.isSearchResults = false;
        vm.isDropdownSort = true;

        // Set matches to all groups initially.
        let matches = vm.groups;

        if (topicSelected !== 'All') {
          // Filter the groups for matching topics.
          matches = _filter(vm.groups, (group) => {
            if (group.topics && group.topics.includes(topicSelected)) {
              return true;
            }
          });
        }

        // Set the results, display the first page, and update the pagination info.
        vm.results = matches;
        vm.getPagedResults(1);
        vm.getPaginationInfo();
      },
      /**
       * Resets the current search state.
       */
      resetSearch() {
        const vm = this;

        vm.search = '';
        vm.isSearchResults = false;
        vm.removeRouterQuery('search');
      },
      /**
       * Resets the topic dropdown state.
       */
      resetTopicDropdown() {
        const vm = this;

        vm.$refs.dropdownTopic.reset();
        vm.removeRouterQuery('topic');
      },
      /**
       * Removes the specified router parameter from the query string.
       *
       * @param name
       *    Name of the query string parameter to remove.
       */
      removeRouterQuery(name) {
        const query = Object.assign({}, this.$route.query);
        delete query[name];
        this.$router.replace({ query });
      },
      /**
       * Scrolls to the top of the list.
       */
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
      /**
       * Returns focus to the first focus-able link in the results list.
       */
      focusTop() {
        setTimeout(() => {
          const focusable = this.$el.querySelector('.c-grouplist a');
          if (focusable) {
            focusable.focus();
          }
        }, 150);
      },
      /**
       * Determines if the search query has a value.
       *
       * @returns {boolean}
       */
      queryHasValue() {
        const vm = this;

        return (vm.search !== '');
      },
      /**
       * Updates the search query string in the Vue component based on the value in the router.
       */
      updateQueryString() {
        const vm = this;

        if (vm.query !== '') {
          this.$router.replace({ query: { search: vm.search } });
        } else {
          this.removeRouterQuery('search');
        }
      },
      /**
       * Logs a given message to the console.
       *
       * @param message {string}
       * @param type {string}
       */
      log(message, type = 'info') {
        const prefix = 'GroupList';

        if (type === 'error') {
          console.error(`${prefix}: ${message}`);
        }
        else {
          console.log(`${prefix}: ${message}`);
        }
      }
    },
  };
</script>
