<template>
  <div>
    <nav role="navigation"
         aria-label="Pagination"
         class="pager">
      <paginate
        v-model="currentPage"
        :page-count="total"
        :page-range="5"
        :click-handler="handleClick"
        :prev-text="'&lt;'"
        :next-text="'&gt;'"
        :hide-prev-next="true"
        :prev-class="'pager__item pager__item--prev pager__item--link'"
        :next-class="'pager__item pager__item--next pager__item--link'"
        :page-class="'pager__item pager__item--link'"
        :container-class="'pager__items'"
        active-class="is-active"
        break-view-text="—"/>
    </nav>
  </div>
</template>

<script>
import Paginate from 'vuejs-paginate';

export default {
  name: 'UcPager',
  components: {
    Paginate,
  },
  props: {
    page: {
      type: Number,
      default: 1,
    },
    total: {
      type: Number,
      required: true,
    },
    onPage: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      currentPage: this.page,
    };
  },
  watch: {
    page() {
      this.currentPage = this.page;
    },
  },
  mounted() {

  },
  methods: {
    // send page number back to list component
    handleClick(pageNum) {
      this.onPage(pageNum);
    },
  },
};
</script>
