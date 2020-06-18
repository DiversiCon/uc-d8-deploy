<template>
  <div class="c-alphalist">

    <div ref="controls"
         :class="{'sticky': stickyControls}"
         class="c-alphalist__controls">
      <div
          :class="{'c-alphalist__searchform--nodropdown': hideSecondaryDropdown}"
          class="c-searchform c-alphalist__searchform">
        <form
          autocomplete="off"
          accept-charset="UTF-8"
          @submit.prevent="doSearch">
          <div class="c-searchform__input_container">
            <input
              v-model="query"
              type="text"
              name="query"
              aria-label="Search by name"
              placeholder="Search by name">
            <button
              type="submit"
              role="button"
              aria-label="search"
              @click.prevent="doSearch">
              <uc-icon glyph="search"/>
            </button>
          </div>
        </form>
      </div>

      <uc-dropdown
        v-show="!hideDropdown && !filterBySection"
        ref="dropdownDept"
        :options="topics"
        :on-select="selectTopic"
        :default-selection="selectedTopic"
        display-name="All Topics"
        class="c-alphalist__dropdown"
        name="topic" />

      <uc-dropdown
        v-show="filterBySection && !hideSecondaryDropdown"
        ref="dropdownSection"
        :options="sections"
        :on-select="selectTopic"
        class="c-alphalist__dropdown"
        name="section"
        display-name="All Sections"
        default-selection="All Sections"/>

      <ul v-if="ready"
          class="c-alphalist__alphabet">
        <li v-for="letter in alphabet"
            :key="`index-${letter}`">
          <a
            v-if="results[letter]"
            :href="`#${letter}`"
            :class="{ 'active': letter === activeLetter} "
            @click.prevent="setActiveLetter(letter)">{{ letter }}</a>
          <span v-else>{{ letter }}</span>
        </li>
      </ul>
    </div>

    <div v-if="noResults"
         class="c-alphalist__noresults">
      <h2>No results found.</h2>
    </div>

    <div v-if="!noResults"
         ref="grid">
      <section v-for="(letter, idx) in results"
               :key="`letter-${idx}`"
               class="c-alphalist__grid">
        <h2>
          <div :id="idx"
               tabindex="0"
               class="c-alphalist__anchor">{{ idx }}</div>
        </h2>

        <div>
          <div v-for="item in letter"
               :key="`item-${item.id}`"
               class="c-alphalist__grid-item">
            <div class="c-alphalist__grid-image c-list__item-imagewrap">
              <svg class="c-icon">
                <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix" />
              </svg>
              <img v-if="item.photoUrl && item.showPhoto"
                   :data-src="`${item.photoUrl}?cache=${item.id}`"
                   :alt="`${item.fullName}`"
                   class="lazyload"
                   width="345"
                   height="345">
            </div>

            <div class="c-alphalist__grid-content">
              <h3 class="">
                <!-- Use pathAlias for the link, fallback to id (item.pathAlias includes an initial slash, whereas item.id does not) -->
                <a :href="item.pathAlias ? `/faculty${item.pathAlias}` : `/faculty/${item.id}`">
                  {{ item.fullName }}<span v-if="item.degree">, {{ item.degree }}</span>
                </a>
              </h3>
              <p><strong>Title: </strong>{{ item.title }}</p>
              <p v-if="item.topics">
                <strong>Research topics:</strong>
                {{ item.topics.join(', ') }}
              </p>
              <p v-if="item.research_group">
                <strong>Research group:</strong>
                <a :href="item.research_group.url">{{ item.research_group.name }}</a>
              </p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Fuse from 'fuse.js';
import _groupBy from 'lodash/groupBy';
import _sortBy from 'lodash/sortBy';
import _filter from 'lodash/filter';
import _uniqBy from 'lodash/uniqBy';

const ucDropdown = () => import(/* webpackChunkName: "dropdown-lazy" */ '../dropdown/dropdown.vue');

export default {
  name: 'uc-alphalist',
  components: {
    ucDropdown,
  },
  props: {
    endpoint: {
      type: String,
      default: '/api/faculty_index',
    },
    groupBy: {
      type: String,
      default: 'lastName',
    },
    defaultTopic: {
      type: String,
      default: 'All Topics',
    },
    hideDropdown: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      allData: null,
      allTopics: null,
      activeLetter: null,
      results: null,
      ready: false,
      query: null,
      topics: null,
      selectedTopic: this.defaultTopic,
      sections: null,
      filterBySection: false,
      hideSecondaryDropdown: this.hideDropdown,
      noResults: false,
      stickyControls: false,
      alphabet: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
      controlsPosition: 0,
      isMobile: window.innerWidth < 1100,
      useStorage: false,
      searchIndex: null,
      allTopicOption: {
        name: 'All Topics',
        value: 'All Topics',
      },
      allSectionOption: {
        name: 'All Sections',
        value: 'All Topics',
      }
    };
  },
  mounted() {
    const vm = this;
    vm.controlsPosition = vm.$refs.controls.getBoundingClientRect().top;

    vm.handleScroll();
    window.addEventListener('scroll', vm.handleScroll);

    window.addEventListener('resize', this.debounce(200, () => {
      vm.isMobile = window.innerWidth < 1100;
      vm.controlsPosition = vm.$refs.controls.getBoundingClientRect().top;
    }));

    // check for query params
    if (vm.$route.query.query) {
      vm.query = vm.$route.query.query;
    }

    if (vm.$route.query.topic) {
      vm.selectedTopic = vm.$route.query.topic;
    }

    vm.filterBySection = (vm.defaultTopic !== 'All Topics' && !vm.hideDropdown);

    vm.init();
  },
  methods: {
    handleScroll() {
      const vm = this;
      const windowScroll = window.scrollY;
      const controlsHeight = document.querySelector('.c-alphalist__controls').offsetHeight;
      const navHeight = (vm.isMobile)
          ? (document.querySelector('.c-masthead')) ? document.querySelector('.c-masthead').offsetHeight : 0
          : (document.querySelector('.c-nav')) ? document.querySelector('.c-nav').offsetHeight : 0;

      if (windowScroll >= (vm.controlsPosition - navHeight)) {
        this.stickyControls = true;
        this.$refs.controls.style.top = navHeight + 'px';
        this.$refs.grid.style.marginTop = (vm.isMobile) ? controlsHeight + 'px' : navHeight + controlsHeight + 'px';
      } else {
        this.stickyControls = false;
        this.$refs.grid.style.marginTop = '0';
      }
    },
    init() {
      if (this.checkStorage() && this.useStorage) { //use local storage for data
        // data is cached in storage and is less than 24 hours old
        const string = localStorage.getItem('uc-faculty');
        const json = JSON.parse(string);
        this.allData = json.data;
        this.setup();
      } else {
        // data is missing, needs refreshed, or we are not using local storage
        this.getAllData();
      }
    },
    async getAllData() {
      const vm = this;
      try {
        const response = await axios.get(vm.endpoint);
        vm.allData = response.data.data;
        if (this.useStorage) { //using local storage for data
          vm.updateStorage();
        }
        vm.setup();
      } catch (error) {
        console.log(error);
      }
    },
    async setup() {
      const vm = this;
      if (vm.filterBySection) {
        vm.allData = vm.filterBy('topics', vm.selectedTopic);
        vm.getSections();
      }
      // give topics time to fully populate
      await vm.getTopics();

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
          "firstName",
          "lastName",
          "topics",
        ]
      };

      // Create the search index.
      vm.searchIndex = new Fuse(vm.allData, searchIndexOptions);

      // if a query string was passed, do a search
      if (vm.query) {
        vm.doSearch();
      } else {
        vm.getResults();
      }
    },
    checkStorage() {
      // check to see if allData is saved to localstorage, or is outdated
      let check = true;
      const data = JSON.parse(localStorage.getItem('uc-faculty'));
      const now = new Date();
      const sleepTime = (data !== null) ? new Date(data.timestamp) : now.getTime();
      if (data === null) {
        // data isn't set at all.
        check = false;
      } else if (now.getTime() > sleepTime.getTime()) {
        // if the current time is greater than what's in
        // localstorage, refresh data from feed
        check = false;
      }
      return check;
    },
    updateStorage() {
      // save allData payload to localstorage and cache for 24 hours
      const newData = {};
      const dt = new Date();
      // set time for 24 hours from now
      dt.setHours(dt.getHours() + 24);
      newData.timestamp = dt;
      newData.data = this.allData;
      localStorage.setItem('uc-faculty', JSON.stringify(newData));
    },
    getResults() {
      const vm = this;
      if (vm.selectedTopic === vm.allTopicOption.value) {
        vm.results = vm.processResults(vm.allData);
      } else {
        // filter results for only the specified department
        const matches = _filter(vm.allData, o => (o.department === vm.selectedTopic));
        vm.results = vm.processResults(matches);
      }
      if (vm.filterBySection) {
        const selection = (vm.$route.query.section)
          ? { name: vm.$route.query.section, value: vm.$route.query.section } : vm.allSectionOption;
        vm.$refs.dropdownSection.handleSelect(selection, false);
      } else {
        vm.$refs.dropdownDept.handleSelect({
          name: vm.selectedTopic,
          value: vm.selectedTopic,
        }, false);
      }
    },
    doSearch() {
      const vm = this;
      vm.updateQueryString();
      vm.resetLetters();
      vm.$refs.dropdownDept.reset();
      vm.$refs.dropdownSection.reset();

      // Initialize the results data. Set it to all faculty members. Successful search/filter will reduce this data set.
      let matches = vm.allData;

      // Only perform the search if we have a search value entered.
      if (vm.query.length > 0) {
        matches = vm.searchIndex.search(vm.query);
      }

      // If no matches are found, set the appropriate state.
      if (matches.length === 0) {
        vm.noResults = true;
        vm.results = [];
      } else {
        // Matches found. Display the results.
        vm.noResults = false;
        vm.results = vm.processResults(matches);
      }
    },
    updateQueryString() {
      if (this.query !== '') {
        this.$router.replace({ query: { query: this.query } });
      } else {
        this.removeQuery();
      }
    },
    removeQuery() {
      const query = Object.assign({}, this.$route.query);
      delete query.query;
      this.$router.replace({ query });
    },
    processResults(data) {
      const vm = this;
      // sort by last name
      data = _sortBy(data, [vm.groupBy]);
      // group results by first letter of last name
      data = _groupBy(data, item => item.lastName.substr(0, 1));
      vm.ready = true;
      vm.noResults = false;
      return data;
    },
    getTopics() {
      const vm = this;
      const topics = [vm.allTopicOption];
      vm.allData.forEach((person) => {
        if (person.topics && person.topics !== null) {
          // Loop through a persons topics and add them to the list.
          person.topics.forEach((topicName) => {
            topics.push({ name: topicName, value: topicName });
          });
        }
      });
      const sorted = _sortBy(topics, 'name');
      vm.topics = _uniqBy(sorted, 'name');
      return new Promise((resolve) => {
        setTimeout(() => {
          resolve('done');
        }, 100);
      });
    },
    getSections() {
      const vm = this;
      const sections = [vm.allSectionOption];
      vm.allData.forEach((person) => {
        if (person.section !== null) {
          sections.push({ name: person.section, value: person.section });
        }
      });
      const sorted = _sortBy(sections, 'name');
      vm.sections = _uniqBy(sorted, 'name');
      if (vm.sections.length < 2) {
        vm.hideSecondaryDropdown = true; //only show if there is more than one section
      }
    },
    selectTopic(choice) {
      const vm = this;
      const filterType = (vm.filterBySection) ? 'section' : 'topics';
      vm.query = null;
      vm.removeQuery();
      vm.resetLetters();

      if (choice === vm.allSectionOption.value || choice === vm.allTopicOption.value) {
        vm.results = vm.processResults(vm.allData);
      } else {
        let matches = vm.filterBy(filterType, choice);

        vm.results = vm.processResults(matches);
      }
    },
    filterBy(filterType, choice) {
      const vm = this;
      let matches = [];

      // Filtering method will be different depending on the filter type.
      if (filterType === 'topics') {
        matches = _filter(vm.allData, (facultyMember) => {
          let matchFound = false;

          if (facultyMember[filterType]) {
            facultyMember[filterType].some((topic) => {
              if (topic === choice) {
                matchFound = true;
                return true;
              }
            });
          }

          return matchFound;
        });
      }
      else {
        matches = _filter(vm.allData, o => (o[filterType] === choice));
      }

      return matches;
    },
    multiples(items) {
      const ordered = [];
      Object.keys(items).sort().forEach((key) => {
        ordered[key] = items[key];
      });
      return ordered.sort().join(', ');
    },
    sorted(items) {
      return _sortBy(items, [o => o]);
    },
    // programmatically update the query and refresh results
    updateSearch(query) {
      this.query = query;
      this.doSearch();
    },
    listLinks(links) {
      const linkList = [];
      // const sorted = _sortBy(links, ['name']);
      links.forEach((link) => {
        linkList.push(`<a href="${link.url}" target="_blank">${link.name}</a>`);
      });
      return linkList.join(', ');
    },
    setActiveLetter(letter) {
      this.activeLetter = letter;
      this.jumpTo(letter);
    },
    resetLetters() {
      this.activeLetter = null;
      window.scroll(0, 0);
    },
    jumpTo() {
      const vm = this;
      const targetLetter = document.getElementById(vm.activeLetter);
      let targetPosition = targetLetter.offsetTop;
      const controlsHeight = document.querySelector('.c-alphalist__controls').offsetHeight;
      const navHeight = (vm.isMobile) ? document.querySelector('.c-masthead').offsetHeight : document.querySelector('.c-nav').offsetHeight;
      if (vm.activeLetter !== 'A') {
        targetPosition -= (controlsHeight + navHeight + 20);
      } else {
        targetPosition -= (controlsHeight + navHeight);
      }
      window.scroll(0, targetPosition);

      targetLetter.focus();
    },
    scrollDown() {
      const vm = this;
      const target = document.getElementById(vm.activeLetter).offsetTop - 140;
      let currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
      currentScroll += 2; // +2 offset for Chrome

      // if we haven't reached the target yet AND not hit the bottom of the page
      if (currentScroll < target && !vm.atBottom()) {
        window.requestAnimationFrame(vm.scrollDown);
        window.scrollTo(target, currentScroll + 75); // a higher number = faster scroll
      }
    },
    atBottom() {
      const pageHeight = document.documentElement.offsetHeight;
      const windowHeight = window.innerHeight;
      const scrollPosition = window.scrollY
        || window.pageYOffset
        || document.body.scrollTop
        + (document.documentElement && document.documentElement.scrollTop || 0);
      return (pageHeight <= windowHeight + scrollPosition);
    },
  },
};
</script>
