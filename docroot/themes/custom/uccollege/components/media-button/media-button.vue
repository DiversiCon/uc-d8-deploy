<template>
  <div :class="classes"
       class="c-media-button">
    <div class="c-media-button__ctn">
      <component :is="tag"
                 v-bind="attrs"
                 @click.prevent="callback">
        <uc-icon :glyph="buttonIcon"
                 class-name=""/>
        <div v-if="duration && buttontype !== 'podcast'"
             class="c-media-button__duration">
          {{ duration }}
        </div>
      </component>
    </div>

    <uc-icon v-show="showPodcastLogo"
             ref="podcastLogo"
             glyph="podcast-uchicago"/>
  </div>
</template>

<script>
const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');

export default {
  name: 'UcMediaButton',
  components: {
    UcIcon,
  },
  props: {
    callback: {
      type: Function,
      required: true,
    },
    duration: String,
    buttontype: {
      type: String,
      default: 'video',
    },
    inline: {
      type: Boolean,
      default: false,
    },
    url: String,
    tag: {
      type: String,
      default: 'div',
    },
  },
  data() {
    return {
      buttonIcon: 'play',
      attrs: {},
      showPodcastLogo: false,
    };
  },
  computed: {
    classes() {
      let classList = `c-media-button--${this.buttontype}`;
      if (this.inline) {
        classList += ' c-media-button--inline';
      }
      return classList;
    },
  },
  mounted() {
    if (this.buttontype === 'podcast') {
      this.buttonIcon = 'podcast';
      this.displayPodcastLogo();
    }

    if (this.tag === 'a') {
      this.attrs.href = this.url;
    }
  },
  methods: {
    displayPodcastLogo() {
      // The UChicago podcast logo should display on the lower right
      // corner of the same container as the media button. This only
      // is possible if we move it from this component and up into
      // its container
      if (!this.inline) {
        const podcastLogo = this.$refs.podcastLogo;
        this.$el.parentElement.appendChild(podcastLogo.$el);
        this.showPodcastLogo = true;
      }
    },
  },
};
</script>
