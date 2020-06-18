<template>
  <div v-show="ready"
       :class="{'c-gallery--single': !useSwiper}"
       class="c-gallery"
       @mouseover="handleHover(true)"
       @mouseleave="handleHover(false)">
    <h3 v-if="totalSlides > 1 && title">{{ title }} {{ currentSlide }}/{{ totalSlides }}</h3>
    <div class="c-gallery__ctn swiper-container">
      <div class="swiper-wrapper"
           @click.prevent="slideTransition">
        <slot/>
      </div>

      <a
        v-if="share"
        :data-a2a-url="share"
        :href="`https://www.addtoany.com/share#url=${share}`"
        class="c-video__sharing a2a_dd">
        <icon glyph="share"/>
      </a>

      <div v-if="useSwiper"
           :class="{isVisible: showButtons}"
           class="c-gallery__button-wrap">
        <a ref="prev"
           :class="{ 'swiper-button-disabled': isBeginning }"
           :aria-disabled="isBeginning"
           href="#"
           class="c-gallery__button swiper-button-prev"
           aria-label="previous slide"
           @click.prevent="prevSlide">
          <uc-icon glyph="arrow-left"/>
        </a>
        <a ref="next"
           :class="{ 'swiper-button-disabled': isEnd }"
           :aria-disabled="isEnd"
           href="#"
           class="c-gallery__button swiper-button-next"
           aria-label="next slide"
           @click.prevent="nextSlide($event)">
          <uc-icon glyph="arrow-right"/>
        </a>
      </div>
    </div>

    <uc-caption
      :description="description"
      :credit="credit"/>
  </div>
</template>


<script>
const getSwiper = () => import(/* webpackChunkName: "swiper-lazy" */ '../../node_modules/swiper/dist/js/swiper.esm');
const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');

export default {
  name: 'UcGallery',
  components: {
    UcIcon,
  },
  props: {
    title: String,
    share: String,
  },
  data() {
    return {
      gallerySwiper: null,
      useSwiper: true,
      isMobile: true,
      showButtons: false,
      description: '',
      credit: '',
      photos: '',
      totalSlides: 1,
      currentSlide: 1,
      ready: false,
      isEnd: false,
      isBeginning: true,
    };
  },
  mounted() {
    // set reference to photo child components
    this.photos = this.$children.filter(comp => comp.$options._componentTag === 'uc-photo');
    // total number of images, used in the 'X of X' counter
    this.totalSlides = this.photos.length;
    // set the description and credit from the first image
    this.updateCaption(0);
    this.isMobile = (window.innerWidth <= 768);

    // is this just a single photo? If so, don't init Swiper
    if (this.totalSlides === 1) {
      this.ready = true;
      this.useSwiper = false;
    } else {
      this.initSlider();
    }
  },
  methods: {
    initSlider() {
      // Initialize Swiper
      const vm = this;
      const swiperOptions = {
        init: false,
        loop: false,
        slidesPerView: 1,
        slidesPerGroup: 1,
        direction: 'horizontal',
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
      };

      // disable swiping on screens above 1024.
      // prevents accidental swipes from mouse clicks on arrows.
      if (window.innerWidth > 1024) {
        swiperOptions.noSwiping = true;
        swiperOptions.noSwipingClass = 'swiper-slide';
      }

      const galleryCtn = this.$el.getElementsByClassName('c-gallery__ctn')[0];
      getSwiper().then((swiperModule) => {
        this.gallerySwiper = new swiperModule.Swiper(galleryCtn, swiperOptions);
        this.gallerySwiper.on('init', () => {
          vm.ready = true;
        });
        // listen for slide changes, to update captions
        this.gallerySwiper.on('slideChange', () => {
          vm.updateCaption(vm.gallerySwiper.activeIndex);

          // remove disabled button classes
          [].forEach.call(vm.$el.getElementsByClassName('c-gallery__button'), (button) => {
            button.classList.remove('swiper-button-disabled');
          });

          vm.isEnd = vm.gallerySwiper.isEnd;
          vm.isBeginning = vm.gallerySwiper.isBeginning;
        });
        this.gallerySwiper.init();

        // in some browsers, if the gallery is cached, a
        // refresh will cause the slides to not advance.
        // Force an update just in case
        setTimeout(() => {
          vm.gallerySwiper.update();
        }, 500);
      });
    },
    nextSlide() {
      this.gallerySwiper.slideNext();
    },
    prevSlide() {
      this.gallerySwiper.slidePrev();
    },
    handleHover(hovering) {
      // if an image is hovered on, and we're on desktop, show buttons
      if (this.useSwiper) {
        this.isMobile = (window.innerWidth <= 768);
        this.showButtons = (hovering && !this.isMobile);
      }
    },
    updateCaption(index) {
      // Update caption based on the index of the 'active' Swiper slide
      this.currentSlide = index + 1;
      const activePhoto = this.photos[index];
      this.description = activePhoto._props.description;
      this.credit = activePhoto._props.credit;
    },
    slideTransition() {
      // clicking on the container advances one slide.
      // if at the end, cycle back to the beginning.
      if (this.gallerySwiper.isEnd) {
        this.gallerySwiper.slideTo(0);
      } else {
        this.gallerySwiper.slideNext();
      }
    },
  },
};
</script>
