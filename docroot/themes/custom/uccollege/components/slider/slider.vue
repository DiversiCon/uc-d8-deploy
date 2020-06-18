<template>
  <div :class="{ 'c-slider swiper-container': shouldSwipe }">
    <slot name="default" :nextSlide="nextSlide" :prevSlide="prevSlide"/>
  </div>
</template>

<script>
const getSwiper = () => import(/* webpackChunkName: "swiper-lazy" */ '../../node_modules/swiper/dist/js/swiper.esm');

export default {
  name: 'UcSlider',
  props: {
    breakpoint: {
      type: Number,
      default: 768,
    },
  },
  data() {
    return {
      realBreakpoint: this.breakpoint,
      shouldSwipe: false,
      swiperObject: null,
      swiperWrapper: null,
      swiperSlides: null,
      swiperOptions: {
        init: false,
        loop: false,
        slidesPerView: 1,
        slidesPerGroup: 1,
        direction: 'horizontal',
        spaceBetween: 10,
      },
    };
  },
  mounted() {
    const vm = this;
    // the breakpoint may need to change, based on the
    // presence of rich text in a callout.
    const richText = this.$el.getElementsByClassName('c-callout__text');
    if (richText.length > 0) {
      vm.realBreakpoint = 1024;
    }

    // If there are more than 4 items, the slider is initialized at all times
    const totalSlides = vm.$el.getElementsByTagName('li');
    if (totalSlides.length > 4) {
      vm.realBreakpoint = 0;
    }
    // check to see if we're at or below the
    // specified breakpoint to enable slider
    vm.checkBreakpoint();

    // re-check on resize
    window.addEventListener('resize', this.debounce(200, () => {
      vm.checkBreakpoint();
    }));
  },
  updated() {
    const vm = this;
    vm.checkBreakpoint();
  },
  methods: {
    checkBreakpoint() {
      // a breakpoint of zero will enable the slider at all breakpoints
      this.shouldSwipe = (window.innerWidth <= this.realBreakpoint) || this.realBreakpoint === 0;
      if (this.shouldSwipe) {
        this.prepSlider();
      } else if (this.swiperObject) {
        this.revertSlider();
      }
    },
    prepSlider() {
      // prep layout structure with necessary classes
      const vm = this;
      // add class of .swiper-wrapper to the first child of this component, like a <ul>
      vm.swiperWrapper = vm.$el.firstElementChild;
      vm.swiperWrapper.classList.add('swiper-wrapper');
      vm.swiperSlides = vm.swiperWrapper.children;

      // for each child of .swiper-wrapper, add .swiper-slide, for instance all <li>'s.
      // Convert HTMLCollection to array
      const slides = [].slice.call(vm.swiperSlides);
      slides.forEach((slide) => {
        slide.classList.add('swiper-slide');
      });
      vm.initSlider();
    },
    setSlideCounts() {
      // Between 768 and the established breakpoint, show 2 slides at a time
      if (window.innerWidth >= 1024) {
        this.swiperOptions.slidesPerView = 4;
        this.swiperOptions.slidesPerGroup = 4;
        this.swiperOptions.spaceBetween = 80;
        this.swiperOptions.navigation = {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        };
      } else if (window.innerWidth >= 768) {
        this.swiperOptions.slidesPerView = 2;
        this.swiperOptions.slidesPerGroup = 2;
      } else {
        this.swiperOptions.slidesPerView = 1;
        this.swiperOptions.slidesPerGroup = 1;
      }
    },
    initSlider() {
      // initialize the Swiper object
      const vm = this;
      vm.setSlideCounts();
      getSwiper().then((swiperModule) => {
        vm.swiperObject = new swiperModule.Swiper(vm.$el, vm.swiperOptions);
        vm.$nextTick(() => {
          vm.swiperObject.init();
        });
      });
    },
    revertSlider() {
      // remove swiper classes and destroy swiper object
      this.swiperWrapper.classList.remove('swiper-wrapper');
      for (let i = 0; i < this.swiperSlides.length; i += 1) {
        this.swiperSlides[i].classList.remove('swiper-slide');
      }
      this.swiperObject.destroy();
    },
    nextSlide() {
      this.swiperObject.slideNext();
    },
    prevSlide() {
      this.swiperObject.slidePrev();
    },
  },
};
</script>
