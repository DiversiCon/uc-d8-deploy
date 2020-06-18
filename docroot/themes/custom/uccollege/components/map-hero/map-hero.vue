<template>
  <div :style="'height: ' + containerHeight"
       class="c-map-hero">
    <button
      :aria-label="btnLabel"
      :class="{ 'c-map-hero__btn--close': showMap }"
      class="c-map-hero__btn"
      @click.prevent="toggle">
      <span v-html="btnText"/>
    </button>

    <div :class="{ 'inactive' : showMap }"
         class="c-map-hero__img">
      <img :data-src="src"
           alt=""
           class="lazyload">
    </div>

    <uc-map
      v-show="showMap"
      :lat="lat"
      :lon="lon"
    />
  </div>
</template>

<script>
const UcMap = () => import(/* webpackChunkName: "gmap-lazy" */ '../gmap/gmap.vue');
const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');

export default {
  name: 'UcMapHero',
  components: {
    UcMap,
    UcIcon,
  },
  props: {
    src: {
      type: String,
      required: true,
    },
    lat: {
      type: String,
      required: true,
    },
    lon: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      showMap: false,
      btnText: 'Map<br>View',
      containerHeight: null,
      imageContainer: null,
    };
  },
  computed: {
    btnLabel() {
      return (this.showMap) ? 'Close Map' : 'Show Google Map';
    },
  },
  mounted() {
    const vm = this;
    // on load and resize, set the height of the container
    // to the height of the image. This will ensure the
    // map stays the same height.
    setTimeout(() => {
      vm.setContainerHeight();
    }, 200);

    window.addEventListener('resize', this.debounce(50, () => {
      vm.setContainerHeight();
    }));
  },
  methods: {
    toggle() {
      this.showMap = !this.showMap;
      this.btnText = (this.showMap) ? 'X' : 'Map<br>View';
      const currentHeight = this.$el.offsetHeight;
      if (this.showMap) {
        this.containerHeight = `${currentHeight}px`;
      } else {
        this.containerHeight = `${this.$el.querySelector('.c-gmap__ctn').offsetHeight}px`;
      }
    },
    setContainerHeight() {
      if (window.innerWidth < 768) {
        // on mobile, maps and photos are squares. Make
        // the height equal to the width
        this.containerHeight = `${window.innerWidth}px`;
      } else {
        this.containerHeight = 'auto';
      }
    },
  },
};
</script>
