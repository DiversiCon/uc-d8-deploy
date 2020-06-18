<template>
  <div class="c-tint-embed">
    <div v-if="isReady">
      <div :data-id="tintId"
           :data-columns="columnCount"
           :data-count="count"
           :data-query="query"
           :data-personalization-id="personalizationId"
           class="tintup"
           data-expand="true"
           data-paginate="true"
           style="height:500px;width:100%;"/>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TintEmbed',
  props: {
    tintId: String,
    query: String,
    columns: {
      type: Number,
      default: 4,
    },
    rows: Number,
    personalizationId: Number,
  },
  data() {
    return {
      columnCount: this.columns,
      count: 3,
      isMobile: true,
      isReady: false,
    };
  },
  mounted() {
    this.init();
  },
  methods: {
    init() {
      const vm = this;
      vm.viewportDetect();
      window.addEventListener('load', vm.renderTint());
    },
    onResize() {
      this.viewportDetect();
      this.isReady = false;
      // wait for dom update (.tintup div will be removed)
      this.$nextTick(() => {
        this.isReady = true;
        HM.render();
      });
    },
    viewportDetect() {
      // detect viewport width and set tint params
      this.isMobile = (window.innerWidth < 768);
      this.getCounts();
    },
    getCounts() {
      const defaultCount = (this.isMobile) ? 3 : 8;
      const defaultColumns = (this.isMobile) ? 1 : this.columns;

      // if rows are specified
      if (this.rows && !this.isMobile) {
        this.count = (this.columns) ? this.rows * this.columns : this.rows * defaultCount;
      } else {
        this.count = defaultCount;
        this.columnCount = defaultColumns;
      }
    },
    renderTint() {
      // add display code to the dom
      this.isReady = true;
      // wait for the next dom update to complete
      this.$nextTick(() => {
        // fire the ready event (the global mixin is listening for this)
        this.$emit('embed-ready');
      });
    },
  },
};
</script>
