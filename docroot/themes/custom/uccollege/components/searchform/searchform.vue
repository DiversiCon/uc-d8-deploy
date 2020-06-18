<template>
  <div :class="getTheme"
       class="c-searchform">
    <form action="/search"
          method="get"
          autocomplete="off"
          accept-charset="UTF-8">
      <div class="c-searchform__input_container">
        <input :value="query"
               :placeholder="placeholder"
               :aria-label="placeholder"
               type="text"
               name="query" >
        <button type="submit"
                role="button"
                aria-label="search">
          <uc-icon glyph="search"/>
        </button>
      </div>
    </form>
  </div>
</template>

<script>
const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');

export default {
  name: 'UcSearchform',
  components: {
    UcIcon,
  },
  props: {
    theme: String,
    placeholder: {
      type: String,
      default: 'Search',
    },
  },
  data() {
    return {
      query: this.query,
    };
  },
  computed: {
    getTheme() {
      return (this.theme) ? `c-searchform--${this.theme}` : '';
    },
  },
  mounted() {
    this.query = this.$route.query.query;
  },
  methods: {
  },
};
</script>
