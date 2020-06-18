<template>
  <div class="c-selector c-selector-group">

    <div v-if="!hideDropdown"
         class="c-selector__dropdown-wrap">
      <label for="dropdown">{{ label }}</label>
      <uc-dropdown
        id="dropdown"
        :name="filterkey"
        :default-selection="defaultOption"
        :options="options"
        :on-select="selectOption"
        display-name="Select"/>
    </div>

    <div class="c-selector-group__results c-selector-group__results-ungrouped">
      <ul>
        <li v-for="(item, index) in ungroupedResults"
            :key="`item-${index}`">
          <img v-if="item.image"
               :data-src="item.image"
               :alt="item.name"
               class="lazyload">
          <div class="c-selector-group__ctn">
            <h3 class="t-heading--small">{{ item.name }}</h3>
            <p class="c-selector-group__title"><strong>{{ item.title }}</strong></p>
            <p v-if="item.positions"
               v-html="item.positions"></p>
            <p v-if="item.location"
               class="c-selector-group__location"
               v-html="item.location"></p>
            <a v-if="item.phone"
               :href="`tel:${item.phone}`">{{ item.phone }}</a>
            <a v-if="item.email"
               :href="`mailto:${item.email}`">{{ item.email }}</a>
            <a v-if="item.link"
               :href="item.link.href" :target="item.link.target">{{ item.link.text }}</a>
          </div>
        </li>
      </ul>
    </div>

    <div v-for="(group, index) in results"
         :key="`group-${index}`"
         class="c-selector-group__results">
      <h2>{{ group[filterkey] }}</h2>
      <ul>
        <li v-for="(item, index) in group.items"
            :key="`item-${index}`">
          <img v-if="item.image"
               :data-src="item.image"
               :alt="item.name"
               class="lazyload">
          <div class="c-selector-group__ctn">
            <h3 class="t-heading--small">{{ item.name }}</h3>
            <p class="c-selector-group__title"><strong>{{ item.title }}</strong></p>
            <p v-if="item.positions"
               v-html="item.positions"></p>
            <p v-if="item.location"
               class="c-selector-group__location"
               v-html="item.location"></p>
            <a v-if="item.phone"
               :href="`tel:${item.phone}`">{{ item.phone }}</a>
            <a v-if="item.email"
               :href="`mailto:${item.email}`">{{ item.email }}</a>
            <a v-if="item.link"
               :href="item.link.href" :target="item.link.target">{{ item.link.text }}</a>
          </div>
        </li>
      </ul>
    </div>

  </div>
</template>

<script>
import axios from 'axios';
import _filter from 'lodash/filter';
import _groupBy from 'lodash/groupBy';

const UcDropdown = () => import(/* webpackChunkName: "dropdown-lazy" */ '../dropdown/dropdown.vue');

export default {
  name: 'UcSelectorGroup',
  components: {
    UcDropdown,
  },
  props: {
    datasource: {
      type: String,
      default: '/themes/custom/uccollege/components/selector-group/staff.json',
    },
    filterkey: {
      type: String,
      required: true,
    },
    groupSort: {
      type: String,
      default: '',
    },
    label: {
      type: String,
      default: 'Filter by',
    },
    defaultOption: {
      type: String,
      default: 'All',
    },
    hideDropdown: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      allData: null,
      options: null,
      results: null,
      ungroupedResults: null,
      selectedOption: this.defaultOption,
      expandedOption: false,
      zindex: 50,
    };
  },
  watch: {
    // when selections are made in either dropdown,
    // fetch new results
    selectedOption() {
      setTimeout(() => {
        this.getResults();
      }, 100);
    },
  },
  mounted() {
    const vm = this;

    // check for filter params passed via query string
    if (vm.$route.query[vm.filterkey]) {
      vm.selectOption(vm.$route.query[vm.filterkey]);
    }

    // load the json
    vm.getAllData();
  },
  methods: {
    arrayToObject(arr, keyField) {
      return Object.assign({}, ...arr.map(item => ({ [item[keyField]]: item })));
    },
    getAllData() {
      const vm = this;

      axios.get(vm.datasource)
        .then((response) => {
          // all the data, including dropdown names
          vm.allData = response.data.items;

          // objects to populate dropdowns
          vm.options = response.data.options;

          vm.getResults();
        })
        .catch((error) => {
          // handle error
          console.log(error);
        })
        .then(() => {
          // always executed
        });
    },
    getResults() {
      const vm = this;

      // filter for matching groups
      const filteredResults = _filter(vm.allData, o => (o[vm.filterkey] === vm.selectedOption || vm.selectedOption === 'All'));

      const groupedResults = _groupBy(filteredResults, vm.filterkey);

      const ungroupedResults = groupedResults["null"];

      // get the keys of the grouped results object
      const keysArray = Object.keys(groupedResults);

      // the sorted array will dictate the order of the grouped results
      let sorted;
      if (vm.groupSort === 'asc') {
        sorted = keysArray.sort();
      } else if (vm.groupSort === 'desc') {
        sorted = keysArray.sort((a, b) => b - a);
      } else {
        // convert options to array
        sorted = Object.keys(vm.options).map((key) => {
          if (vm.options[key].name !== 'All') {
            return vm.options[key].name;
          }
        });
      }

      // do the reordering in a new array
      const newResults = [];
      for (let i = 0; i < sorted.length; i += 1) {
        const key = sorted[i];
        if (groupedResults[key]) {
          newResults.push({ [vm.filterkey]: key, items: groupedResults[key] });
        }
      }

      vm.results = newResults;
      vm.ungroupedResults = ungroupedResults;
    },
    selectOption(option) {
      this.selectedOption = option;
    },
  },
};
</script>
