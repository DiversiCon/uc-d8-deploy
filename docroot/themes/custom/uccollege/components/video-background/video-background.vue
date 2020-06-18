<template>
  <div :style="styles"
       class="c-video-background"
       @click.prevent="toggleVideo">
    <div class="c-video-background__wrap">
      <component v-if="heading"
                 :is="tag"
                 class="c-video-background__heading"
                 v-html="heading"/>
    </div>
    <a class="c-video-background__controls"
       href="#">
      <uc-icon :glyph="glyph"/>
    </a>
    <video ref="video"
           :poster="poster"
           :src="src"
           :width="width"
           :height="height"
           muted
           loop
           playsinline
           autoplay>
      <img v-if="poster"
           :data-src="poster"
           :alt="alt"
           class="lazyload">
    </video>
  </div>
</template>

<script>
const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');

export default {
  name: 'VideoBackground',
  components: {
    UcIcon,
  },
  props: {
    heading: String,
    src: {
      type: String,
      required: true,
    },
    poster: {
      type: String,
      default: '',
    },
    tag: {
      type: String,
      default: 'h1',
    },
    width: {
      type: String,
      default: '100%',
    },
    height: {
      type: String,
      default: '100%',
    },
    alt: {
      type: String,
      default: '',
    },
    background: {
      type: String,
      default: '#fff',
    },
  },
  data() {
    return {
      playing: true,
    };
  },
  computed: {
    glyph() {
      return (this.playing) ? 'pause' : 'play';
    },
    styles() {
      let styles = `background-color: ${this.background}; `;
      styles += (this.width !== '100%')
        ? `max-width: ${this.width}px; max-height: ${this.height}px`
        : 'width: 100%; height: 100%';
      return styles;
    },
  },
  mounted() {
  },
  methods: {
    toggleVideo() {
      this.playing = !this.playing;
      if (this.playing) {
        this.$refs.video.play();
      } else {
        this.$refs.video.pause();
      }
    },
  },
};
</script>
