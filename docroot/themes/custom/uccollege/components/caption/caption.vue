<template>
  <div class="c-caption">
    <div class="c-caption__description"
         v-html="getCaption"/>
  </div>
</template>

<script>
export default {
  name: 'UcCaption',
  props: {
    description: String,
    credit: String,
  },
  data() {
    return {
      isLaptop: false,
      isMobile: false,
    };
  },
  computed: {
    getCaption() {
      let caption = this.description;
      const credit = this.getCredit;
      if (this.getCredit !== '') {
        caption += (this.description !== '') ? ` (${credit})` : credit;
      }
      return caption;
    },
    getDescription() {
      return (this.description) ? this.description : '';
    },
    getCredit() {
      return (this.credit) ? this.credit : '';
    },
  },
  mounted() {
    const vm = this;
    vm.init();
    window.addEventListener('resize', this.debounce(200, vm.init));
  },
  methods: {
    init() {
      this.isMobile = window.innerWidth < 768;
      this.isLaptop = window.innerWidth >= this.laptopMinWidth
        && window.innerWidth < this.laptopMaxWidth;
    },
  },
};
</script>
