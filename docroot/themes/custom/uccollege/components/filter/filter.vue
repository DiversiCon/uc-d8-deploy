<template>
  <div class="c-filter">

    <h2 v-if="title"
        class="c-filter__title">{{ title }}</h2>
    <div class="view-filters">

      <div class="form--inline clearfix">
        <div>
          <p class="c-filter__checkbox">
            <input v-model="all"
                   type="checkbox"
                   name="all"
                   @click.prevent="selectAll()" > <span>All</span>
          </p>
          <p v-for="item in categories"
             :key="item.id"
             class="c-filter__checkbox">
            <input
              v-model="selected"
              :value="item.id"
              :disabled="itemDisabled(item.id)"
              :class="{disabled: itemDisabled(item.id)}"
              type="checkbox"
              name="item.name"
            ><span v-html="item.name"/>
          </p>
        </div>
      </div>
    </div>

    <div class="c-list__skip">
      <a href="#list-results">Skip to results</a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UcFilter',
  props: {
    categories: {
      type: Array,
      required: true,
    },
    title: String,
    onSelect: {
      type: Function,
      required: true,
    },
    preSelected: {
      type: String,
    },
  },
  data() {
    return {
      all: true,
      selected: [],
      firstLoad: true,
    };
  },
  watch: {
    // watch for changes to the checkboxes
    selected() {
      // don't fire if this is the first time
      if (!this.firstLoad) {
        // if not all boxes are checked, un-check "all"
        this.all = (this.selected.length === this.categories.length);

        // update query string
        const query = Object.assign({}, this.$route.query);

        if (!this.all) {
          query.section = this.selected.join(',');
        } else {
          delete query.section;
        }
        this.$router.replace({ query });

        // send selected options back to the list component
        const output = (this.all) ? 'all' : this.selected;
        this.onSelect(output);
      } else {
        this.firstLoad = false;
      }
    },
  },
  mounted() {
    const vm = this;

    // are any categories passed in query string?
    if (vm.$route.query.section) {
      vm.selectFromQuery(vm.$route.query.section);
      // Any categories passed via prop?
    } else if (vm.preSelected) {
      vm.selectFromQuery(vm.preSelected);
    } else {
      vm.selectAll();
    }
  },
  methods: {
    selectAll() {
      const allSelected = [];
      Object.keys(this.categories).forEach((item) => {
        if (Object.prototype.hasOwnProperty.call(this.categories, item)) {
          allSelected.push(this.categories[item].id);
        }
      });
      this.selected = allSelected;
      this.all = this.categories.length === this.selected.length;
    },
    selectFromQuery(query) {
      this.selected = query.split(',');
      this.all = this.categories.length === this.selected.length;
    },
    itemDisabled(id) {
      return (this.selected.length === 1 && this.selected.indexOf(id) !== -1);
    },
  },
};
</script>
