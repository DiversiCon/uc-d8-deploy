<template>
  <div class="c-sorter">
    <div class="c-list__item">
      <div class="c-list__item-image">
        Sort by:
        <a v-if="sortBy === 'date'"
           href="#"
           @click.prevent="changeSort('rank')">Relevance</a> <span v-else>Relevance</span> |
        <a v-if="sortBy === 'rank'"
           href="#"
           @click.prevent="changeSort('date')">Date</a> <span v-else>Date</span>
      </div>
      <div class="c-list__item-text">
        Displaying results {{ start }} - {{ end }} out of {{ total }} for
        <strong> {{ query }}</strong>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UcSorter',
  props: {
    start: Number,
    end: Number,
    total: Number,
    sortBy: String,
  },
  data() {
    return {
      query: this.$route.query.query,
    };
  },
  mounted() {

  },
  methods: {
    // tell the parent list component
    // to re-fetch in a new order
    changeSort(type) {
      this.$parent.$emit('onSortChange', type);
    },
  },
};
</script>
