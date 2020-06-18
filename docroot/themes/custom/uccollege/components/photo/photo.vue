<template>
  <div :class="{'swiper-slide': inGallery}"
       class="c-photo">
    <picture>
      <source :data-srcset="imgFull"
              media="(min-width: 1024px)">
      <source :data-srcset="imgLarge"
              media="(min-width: 768px)">
      <source :data-srcset="imgMedium"
              media="(min-width: 480px)">
      <source :data-srcset="imgSmall"
              media="(max-width: 479px)">
      <img :data-src="imgFull"
           :alt="alt"
           class="lazyload">
    </picture>

    <uc-caption
      v-if="showCaption"
      :description="description"
      :credit="credit" />
  </div>
</template>

<script>
export default {
  name: 'UcPhoto',
  props: {
    imgFull: String,
    imgLarge: String,
    imgMedium: String,
    imgSmall: String,
    description: String,
    credit: String,
    alt: String,
  },
  data() {
    return {
      showCaption: false,
      inGallery: false,
    };
  },
  mounted() {
    // for IE11 only. If there's no grid support, look to see if this
    // image's parent has the .c-hero--2col class.
    if (!Modernizr.cssgrid) {
      const self = this;
      const isTwoCol = self.$el.parentNode.classList.contains('c-hero--2col');
      if (isTwoCol) {
        self.reposition();
        window.addEventListener('resize', self.reposition);
      }
    }

    this.inGallery = (this.$parent.$options._componentTag === 'uc-gallery');

    // only display a caption if this photo is NOT part of a gallery.
    // The gallery component will take care of captions otherwise.
    this.showCaption = (!this.inGallery && (this.description !== '' || this.credit !== ''));
  },
  methods: {
    reposition() {
      // in 2-column hero components, the image in IE11 needs to be lifted
      // up by the same amount as the height of the h1 and h2 content.
      const heading = document.getElementsByClassName('c-hero__heading');
      const subhead = document.getElementsByClassName('c-hero__subhead');
      // subhead is optional
      const headingHeight = (subhead.length !== 0)
        ? heading[0].offsetHeight + subhead[0].offsetHeight : heading[0].offsetHeight;
      const picture = this.$el.getElementsByTagName('picture');

      this.$el.style.top = `-${headingHeight + 20}px`;
      this.$el.style.marginBottom = `-${headingHeight - 40}px`;
      this.$el.style.height = `${picture[0].offsetHeight}px`;
    },
  },
};
</script>
