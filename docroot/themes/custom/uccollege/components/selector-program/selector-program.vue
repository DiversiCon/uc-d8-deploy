<template>
  <div class="c-selector c-selector-program">

    <div class="c-selector__dropdown-wrap">
      <uc-dropdown
        v-if="!hideDivision"
        :default-selection="defaultDivision"
        :options="divisions"
        :on-select="selectDivision"
        name="division"
        display-name="Collegiate Division"/>

      <uc-dropdown
        v-if="!hideType"
        :default-selection="defaultType"
        :options="types"
        :on-select="selectType"
        name="type"
        display-name="Type"/>
    </div>

    <uc-accordion-group>
      <uc-accordion v-for="program in results"
                    :title="program.name"
                    :key="program.name">
        <p class="c-selector-program__majors">
          <strong>Offered as:</strong> {{ majorList(program.types) }}
        </p>
        <div class="c-selector-program__text"
             v-html="program.text"/>

        <ul v-if="program.links"
            class="s-dash">
          <li v-for="(link, index) in program.links"
              :key="`link-${index}`">
            <a :href="link.url"
               :target="link.target">{{ link.title }}</a></li>
        </ul>
      </uc-accordion>
    </uc-accordion-group>

  </div>
</template>

<script>
import axios from 'axios';
import _filter from 'lodash/filter';
import _isEmpty from 'lodash/isEmpty';
import _concat from 'lodash/concat';
import _union from 'lodash/union';
import _flatten from 'lodash/flatten';

const UcAccordionGroup = () => import(/* webpackChunkName: "accordion-group-lazy" */ '../accordion/accordion-group.vue');
const UcAccordion = () => import(/* webpackChunkName: "accordion-lazy" */ '../accordion/accordion.vue');
const UcDropdown = () => import(/* webpackChunkName: "dropdown-lazy" */ '../dropdown/dropdown.vue');

export default {
  name: 'UcSelectorProgram',
  components: {
    UcAccordionGroup,
    UcAccordion,
    UcDropdown,
  },
  props: {
    datasource: {
      type: String,
      default: '/themes/custom/uccollege/components/selector-program/programs.json',
    },
    defaultDivision: {
      type: String,
      default: 'All',
    },
    defaultType: {
      type: String,
      default: 'All',
    },
    hideDivision: {
      type: Boolean,
      default: false,
    },
    hideType: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      allData: null,
      allPrograms: null,
      divisions: null,
      types: null,
      results: null,
      selectedDivision: this.defaultDivision,
      selectedType: this.defaultType,
      expandedDivision: false,
      expandedType: false,
      zindex: 50,
    };
  },
  watch: {
    // when selections are made in either dropdown,
    // fetch new results
    selectedDivision(newDivision) {
      this.getResults();
      if (newDivision === 'All') {
        this.filterTypes();
      }
    },
    selectedType() {
      this.getResults();
    },
    // when results get updated, filter for available types.
    // when the division is 'All', show all types
    results() {
      if (this.selectedDivision !== 'All') {
        this.filterTypes();
      }
    },
  },
  mounted() {
    const vm = this;

    // set decrementing z-index for dropdowns
    const wrappers = this.$el.querySelectorAll('.c-selector__dropdown-wrap .c-dropdown');
    for (let i = 0; i < wrappers.length; i += 1) {
      wrappers[i].style.zIndex = (vm.zindex - 1);
    }

    // check for filter params passed via query string
    if (vm.$route.query.division) {
      vm.selectDivision(vm.$route.query.division);
    }

    if (vm.$route.query.type) {
      vm.selectType(vm.$route.query.type);
    }

    // load the json
    vm.getAllData();
  },
  methods: {
    getAllData() {
      const vm = this;

      axios.get(vm.datasource)
        .then((response) => {
          // all the data, including dropdown names
          vm.allData = response.data;
          // just program data
          vm.allPrograms = vm.allData.programs;
          // initially, show all programs (if no query params and no defaults passed via props)
          if (_isEmpty(vm.$route.query) && (vm.defaultDivision === 'All' && vm.defaultType === 'All')) {
            vm.results = vm.allPrograms;
          } else {
            vm.getResults();
          }

          // objects to populate dropdowns
          vm.divisions = vm.allData.divisions;
          vm.types = vm.allData.types;
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

      // send event to the accordion group to reset all open accordions
      vm.$emit('resetAll', 'all');

      // filter for matching programs
      vm.results = _filter(vm.allPrograms, o => (o.division === vm.selectedDivision || vm.selectedDivision === 'All')
            && (o.types.indexOf(vm.selectedType) !== -1 || vm.selectedType === 'All'));
    },
    // process the degree types from all program results, and merge
    // into a unique list for the types dropdown
    filterTypes() {
      const vm = this;
      const typeList = [];
      for (let i = 0; i < vm.results.length; i += 1) {
        typeList.push(vm.results[i].types);
      }
      // get the unique array
      const uniqueList = _concat('All', _union(_flatten(typeList)));
      // conform array to match object structure dropdowns expect
      vm.types = uniqueList.map(x => ({ name: x }));
    },
    selectDivision(division) {
      this.selectedDivision = division;
      this.$emit('resetTypes');
    },
    selectType(type) {
      this.selectedType = type;
    },
    majorList(types) {
      return types.join(' | ');
    },
  },
};
</script>
