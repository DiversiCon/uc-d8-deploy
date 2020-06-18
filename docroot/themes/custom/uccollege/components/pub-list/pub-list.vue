<template>
  <div>
    <section class="l-container c-content" v-if="staffUrl">
      <div class="c-content__body">
        <header class="c-headline c-headline--lg">
          <a :href="`${staffUrl}`"
             class="c-headline__topic t-heading--topic">{{ author }}</a>
          <h1 class="c-headline__heading">{{ headline }}</h1>
        </header>
      </div>
    </section>
    <div class="c-pub-list">
      <div ref="controls"
           class="c-pub-list__controls"
           :class="{ 'c-pub-list__controls--hide-topics': hideTopics }">

        <div
                class="c-searchform c-pub-list__searchform">
          <form
                  autocomplete="off"
                  accept-charset="UTF-8"
                  @submit.prevent="searchPublications">
            <div class="c-searchform__input_container">
              <input
                      v-model="query"
                      type="text"
                      name="query"
                      placeholder="Search Publications">
              <button
                      type="submit"
                      @click.prevent="searchPublications">
                <uc-icon glyph="search"/>
              </button>
            </div>
          </form>
        </div>

        <uc-dropdown
          v-if="!hideTopics"
          ref="dropdownTopic"
          :options="topicDropdownOptions"
          :default-selection="topicDropdownSelected"
          :on-select="handleTopicDropdownChange"
          display-name="All Topics"
          class="c-pub-list__dropdown"
          name="topics" />

        <uc-dropdown
          ref="dropdownYear"
          :options="yearDropdownOptions"
          :default-selection="yearDropdownSelected"
          :on-select="handleYearDropdownChange"
          display-name="All Years"
          class="c-pub-list__dropdown"
          name="years" />
      </div>

      <section class="c-pub-list__search-info">
        <div v-if="noResults && isSearchResults"
             class="c-pub-list__noresults">
          <h2>No results found.</h2>
        </div>
        <p v-else-if="!isLoading && isSearchResults" class="c-pub-list__search-summary">
          Displaying {{ results.length }} results for <strong>{{ $route.query.query }}</strong>
        </p>
      </section>

      <section v-if="!noResults" class="c-pub-list__content">
        <div v-for="item in pagedResults"
             :key="`pub-${item.drupalId}`"
             class="c-pub-list__item">
          <h3 class="c-pub-list__item-title">
            <a :href="`${item.linkUrl}`" target="_blank">
              {{ item.label }}
            </a>
          </h3>
          <p class="c-pub-list__item-publication">{{ item.citation }}</p>
        </div>
      </section>
    </div>

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
  import moment from 'moment';
  import Fuse from 'fuse.js';
  import _filter from 'lodash/filter';
  import _uniqBy from 'lodash/uniqBy';
  import _orderBy from 'lodash/orderBy';

  const ucPager = () => import(/* webpackChunkName: "pager-lazy" */ '../pager/pager.vue');
  const ucDropdown = () => import(/* webpackChunkName: "dropdown-lazy" */ '../dropdown/dropdown.vue');

  export default {
    name: 'uc-pub-list',
    components: {
      ucPager,
      ucDropdown
    },
    props: {
      endpoint: {
        type: String,
        default: 'http://bsd-data.dev.uchicago.edu/api/node/publication',
      },
      authorType: {
        type: String,
        default: 'faculty',
      },
      staffId: {
        type: String,
        required: false,
      },
      staffUrl: {
        type: String,
        required: false,
      },
      headline: {
        type: String,
        default: 'Publications',
      },
      hideTopics: {
        type: Boolean,
        default: false,
      }
    },
    data() {
      return {
        allPubs: null,
        pubMedUrl: 'https://www.ncbi.nlm.nih.gov/pubmed',
        results: null,
        pagedResults: null,
        noResults: null,
        author: null,
        query: '',
        isLoading: true,
        isSearchResults: false,
        searchIndex: null,
        pagination: {
          currentIndex: 0,
          currentPage: 1,
          totalPages: 1,
          totalPerPage: 10,
          totalResults: 0,
          currentResults: 0,
        },
        isDropdownSort: false,
        yearDropdownOptions: [],
        yearDropdownSelected: 'All',
        topicDropdownOptions: [],
        topicDropdownSelected: 'All',
      };
    },
    async mounted() {
      const vm = this;
      vm.isLoading = true;

      // Check if the query in the router has any value.
      if (vm.$route.query.query !== null && vm.$route.query.query) {
        vm.query = vm.$route.query.query;
      }

      if (vm.$route.query.years !== null && vm.$route.query.years) {
        vm.yearDropdownSelected = vm.$route.query.years;
      }

      // Get all publications.
      await vm.getAllPubs();

      // Load the year dropdown.
      vm.loadYearDropdown();

      // Load the topic dropdown.
      vm.loadTopicDropdown();

      // The component is now loaded.
      vm.isLoading = false;
    },
    methods: {
      /**
       * Get and display all publications from the CDR endpoint.
       */
      async getAllPubs() {
        const vm = this;

        // Initialize the publications list.
        vm.allPubs = [];

        // This will track the next page link provided by the CDR.
        let nextPage = '';

        // Request the first page of publications.
        try {
          const params = {
            include: 'field_topics',
            sort: '-field_year,-field_publication_date'
          };

          if (vm.staffId) {
            params['filter[field_associated_people.id]'] = vm.staffId;
          }

          const response = await axios.get(vm.endpoint, { params: params });

          // Normalize the first page of results, get the author, and display the first page.
          vm.allPubs = vm.normalize(response.data);

          if (vm.staffId) {
            vm.author = vm.getAuthor(vm.staffId);
          }

          // If there is no search query present, display the first page of results.
          if (!vm.queryHasValue()) {
            vm.setResults(vm.allPubs);
            vm.getResults(1);
          }

          // Check if a next page link exists from the CDR response.
          if ('next' in response.data.links) {
            nextPage = response.data.links.next.href;
          }
        }
        catch (error) {
          //TODO: Better error handling.
          console.log(error);
        }

        // While the CDR response provides a next page link, continue to request data and add it
        // to our list of publications.
        while (nextPage !== '') {
          try {
            // Request the next page of publications.
            const response = await axios.get(nextPage);

            // Add the publications to the existing list.
            vm.allPubs = vm.allPubs.concat(vm.normalize(response.data));

            // Set the next page link if it exists.
            if ('next' in response.data.links) {
              nextPage = response.data.links.next.href;
            }
            else {
              nextPage = '';
            }
          }
          catch (error) {
            //TODO: Better error handling.
            console.log(error);
          }
        }

        // Prepare the pagination.
        vm.setResults(vm.allPubs);
        vm.getPaginationInfo();

        // Prepare the search index options.
        const searchIndexOptions = {
          shouldSort: true,
          tokenize: true,
          threshold: 0,
          location: 0,
          distance: 0,
          maxPatternLength: 32,
          minMatchCharLength: 1,
          keys: [
            "label",
            "authors",
            "pmid",
          ]
        };

        // Create the search index.
        vm.searchIndex = new Fuse(vm.allPubs, searchIndexOptions);

        // If we have a search query present, initiate a search of the publications data set.
        if (vm.queryHasValue()) {
          vm.searchPublications();
        }

      },
      getAuthor(guid) {
        const vm = this;

        let fieldsQuery = 'fields[node--faculty]=field_first_name,field_last_name';

        switch (vm.authorType) {
          case 'person':
            fieldsQuery = 'fields[node--person]=field_headline_text';

            break;

          case 'group':

            break;
        }

        axios.get(`${vm.endpoint}/${guid}?${fieldsQuery}`)
          .then((response) => {
            const data = response.data.data.attributes;

            switch (vm.authorType) {
              case 'person':
                vm.author = `${data.field_headline_text.value}`;

                break;

              case 'group':

                break;

              case 'faculty':
                vm.author = `${data.field_first_name} ${data.field_last_name}`;

                break;
            }

          })
          .catch((error) => {
            // handle error
            console.log(error);
          });
      },
      normalize (dataSet) {
        // loop through data and convert into smaller, more
        // manageable fields
        const newList = [];
        const includedData = dataSet.included;

        // map of new keys and their location
        // within the original JSON object
        dataSet.data.forEach((item) => {
          const newItem = {};
          ({
            id: newItem.id,
            attributes: {
              drupal_internal__nid: newItem.drupalId,
              field_author_list: newItem.authors,
              field_citation: newItem.citation,
              title: newItem.label,
              field_pmid: newItem.pmid,
              field_publication: newItem.publication,
              field_publication_date: newItem.publicationDate,
              field_year: newItem.year,
              field_link_single: newItem.linkUrl,
            },
          } = item);

          // Convert the publication date to a timestamp. The publication date
          // is used for sorting.
          let timestamp = '';

          if (item.attributes.field_publication_date != null && item.attributes.field_publication_date !== '') {
            timestamp = moment(item.attributes.field_publication_date).unix();
          }

          newItem.timestamp = timestamp;

          // Set a zero for all nulls. We handle this when in comes to displaying/filtering by year.
          if (newItem.year == null) {
            newItem.year = 0;
          }

          // Parse the ids of any related topic terms.
          const topicTermIds = [];
          if (item.relationships.field_topics != null) {
            item.relationships.field_topics.data.forEach((topicData) => {
              topicTermIds.push(topicData.id);
            });
          }

          // Initialize the related topics.
          newItem.topics = [];

          // Reduce link url to the proper value.
          if (newItem.linkUrl != null) {
            if (newItem.linkUrl.uri != null) {
              newItem.linkUrl = newItem.linkUrl.uri;
            }
            else {
              newItem.linkUrl = '';
            }
          }
          else {
            newItem.linkUrl = '';
          }

          // Loop through the term ids and find matching term names in the included data.
          topicTermIds.forEach((termId) => {
            includedData.forEach((includedItem) => {
              if (termId === includedItem.id) {
                newItem.topics.push(includedItem.attributes.name);
              }
            });
          });

          newList.push(newItem);
        });

        return newList;
      },
      setResults(results) {
        const vm = this;

        vm.results = _orderBy(results, ['year', 'timestamp'], ['desc', 'desc'])
      },
      getResults(page) {
        const vm = this;
        const start = (page === 1) ? 0 : ((page - 1) * vm.pagination.totalPerPage);
        const end = start + vm.pagination.totalPerPage;
        vm.pagedResults = vm.results.slice(start, end);
      },
      searchPublications() {
        const vm = this;

        vm.resetYearDropdown();
        vm.resetTopicDropdown();

        vm.updateQueryString();
        vm.isSearchResults = false;

        let matches = vm.allPubs;

        if (vm.queryHasValue()) {
          vm.isSearchResults = true;

          matches = vm.searchIndex.search(vm.query);
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
            vm.setResults(matches);
          }
        }
        else {
          vm.noResults = false;
          vm.setResults(vm.allPubs);
        }

        vm.getResults(1);
        vm.getPaginationInfo();
      },
      /**
       * Determines if the search query has a value.
       *
       * @returns {boolean}
       */
      queryHasValue() {
        const vm = this;

        return (vm.query !== '');
      },
      updateQueryString() {
        const vm = this;

        if (vm.query !== '') {
          this.$router.replace({ query: { query: vm.query } });
        } else {
          this.removeSearchQuery();
        }
      },
      removeSearchQuery() {
        const query = Object.assign({}, this.$route.query);
        delete query.query;
        this.$router.replace({ query });
      },
      removeAuthors(citation, authors) {
        if (citation === null) {
          citation = '';
        }
        // the author list is repeated in
        // the citation. Need to remove it
        return citation.replace(`${authors}. `, '');
      },
      getPaginationInfo() {
        const vm = this;
        vm.pagination.totalPages = Math.ceil(vm.results.length / vm.pagination.totalPerPage);
      },
      handlePage(pageNum) {
        // Handles clicks on pagination links
        const vm = this;
        // get new start and end dates to query for, based
        // on page number selected
        vm.scrollToTop();
        vm.getResults(pageNum);
      },
      loadYearDropdown() {
        const vm = this;

        let yearOptions = [
          {
            name: 'All Years',
            value: 'All',
          },
        ];

        vm.allPubs.forEach((publication) => {
          if (publication.year != null && publication.year !== 0) {
            yearOptions.push({name: publication.year, value: publication.year});
          }
        });

        yearOptions = _uniqBy(yearOptions, 'name');
        yearOptions = _orderBy(yearOptions, 'name', 'desc');

        vm.yearDropdownOptions = yearOptions;

        if (vm.yearDropdownSelected !== 'All') {
          vm.$refs.dropdownYear.handleSelect(
            {
              name: vm.yearDropdownSelected,
              value: vm.yearDropdownSelected,
            },
            false
          );
        }
      },
      handleYearDropdownChange(yearSelected) {
        const vm = this;

        vm.resetSearch();
        vm.resetTopicDropdown();

        vm.isSearchResults = false;
        vm.isDropdownSort = true;

        let matches = vm.allPubs;

        if (yearSelected !== 'All') {
          matches = _filter(vm.allPubs, publication => (publication.year === yearSelected));
        }

        vm.setResults(matches);

        vm.getResults(1);
        vm.getPaginationInfo();
      },
      resetSearch() {
        const vm = this;

        vm.query = '';
        vm.isSearchResults = false;
        vm.removeSearchQuery();
      },
      resetYearDropdown() {
        const vm = this;

        if (typeof vm.$refs.dropdownYear !== 'undefined') {
          vm.$refs.dropdownYear.reset();
          vm.removeYearQuery();
        }
      },
      removeYearQuery() {
        const query = Object.assign({}, this.$route.query);
        delete query.years;
        this.$router.replace({ query });
      },
      loadTopicDropdown() {
        const vm = this;

        let topicOptions = [
          {
            name: 'All Topics',
            value: 'All',
          },
        ];

        vm.allPubs.forEach((publication) => {
          publication.topics.forEach((topic) => {
            topicOptions.push({ name: topic, value: topic });
          });
        });

        topicOptions = _uniqBy(topicOptions, 'name');
        topicOptions = _orderBy(topicOptions, 'name', 'asc');

        vm.topicDropdownOptions = topicOptions;

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
      handleTopicDropdownChange(topicSelected) {
        const vm = this;

        vm.resetSearch();
        vm.resetYearDropdown();

        vm.isSearchResults = false;
        vm.isDropdownSort = true;

        let matches = vm.allPubs;

        if (topicSelected !== 'All') {
          matches = _filter(vm.allPubs, (publication) => {
            let matchFound = false;

            publication.topics.some((topic) => {
              if (topic === topicSelected) {
                matchFound = true;
                return true;
              }
            });

            return matchFound;
          });
        }

        vm.setResults(matches);

        vm.getResults(1);
        vm.getPaginationInfo();
      },
      resetTopicDropdown() {
        const vm = this;

        if (typeof vm.$refs.dropdownTopic !== 'undefined') {
          vm.$refs.dropdownTopic.reset();
          vm.removeTopicQuery();
        }
      },
      removeTopicQuery() {
        const query = Object.assign({}, this.$route.query);
        delete query.topics;
        this.$router.replace({ query });
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
          const focusable = this.$el.querySelector('.c-pub-list a');
          if (focusable) {
            focusable.focus();
          }
        }, 150);
      },
    },
  };
</script>
