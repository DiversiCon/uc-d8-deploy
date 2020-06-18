<template>
  <div class="c-video__wrap">
    <h3 v-if="heading">{{ heading }}</h3>
    <div class="c-video responsive-embed widescreen">
      <uc-media-button :callback="playVideo"
                       :duration="duration"
                       :class="{'c-media-button--hidden': !showOverlay}"
                       tag="button"/>
      <div :class="{'c-video__overlay--visible': showOverlay}"
           class="c-video__overlay"
           @click.prevent="playVideo">
        <img :data-src="getPoster"
             class="c-video__thumbnail lazyload"
             alt="">
        <a href="#"
           class="c-video__sharing"
           @click.prevent.stop="handleShare">
          <uc-icon glyph="share"/>
        </a>
      </div>

      <div :class="{'c-video__embed--visible': showVideo}"
           class="c-video__embed responsive-embed widescreen">
        <youtube ref="youtube"
                 :video-id="videoid"
                 width="100%"
                 height="100%"
                 @playing="playing"/>
      </div>
    </div>

    <uc-caption
      v-if="description || credit"
      :description="description"
      :credit="credit"/>

  </div>
</template>

<script>
// Wrapper for embedded Youtube video.
// Initially shows a poster frame with custom controls on top.
// Mobile shows Youtube's native controls

import { Youtube } from 'vue-youtube';

const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');
const UcMediaButton = () => import(/* webpackChunkName: "media-button-lazy" */ '../media-button/media-button.vue');

export default {
  name: 'UcVideo',
  components: {
    Youtube,
    UcMediaButton,
    UcIcon,
  },
  props: {
    videoid: {
      type: String,
      required: true,
    },
    poster: String,
    description: String,
    credit: String,
    heading: String,
  },
  data() {
    return {
      isPlaying: false,
      btnIcon: 'play-circle',
      duration: '',
      playerReady: false,
      captionHeight: '',
      isMobile: window.innerWidth < 1025,
      inAlert: false,
      youtubeUrl: `https://youtu.be/${this.videoid}`,
    };
  },
  computed: {
    player() {
      return this.$refs.youtube.player;
    },
    getPoster() {
      if (!this.poster) {
        return `//img.youtube.com/vi/${this.videoid}/mqdefault.jpg`;
      }
      return this.poster;
    },
    showOverlay() {
      return ((!this.isMobile && !this.isPlaying)
        || (this.isMobile && !this.playerReady && !this.inAlert)
        || (this.inAlert && !this.isPlaying));
    },
    showVideo() {
      return (this.isPlaying || (this.isMobile && this.playerReady));
    },
  },
  mounted() {
    const self = this;
    window.addEventListener('resize', self.init);

    // is this video within an alert menu?
    this.inAlert = this.$parent.$options._componentTag === 'uc-menu';

    // get video duration from Youtube API
    if (!this.isMobile) {
      this.player.getDuration()
        .then((data) => {
          this.duration = this.formatDuration(data);
        });
    }

    this.player.on('ready', () => {
      self.playerReady = true;
    });

    // if this video is inside an alert menu,
    // play the video when it's expanded
    this.$root.$on('onExpandMenu', () => {
      if (this.inAlert) {
        this.playVideo();
      }
    });

    // when the menu closes, pause the video
    this.$root.$on('onMenuClose', this.pauseVideo);
  },
  methods: {
    // User has clicked custom play button
    playVideo() {
      this.player.playVideo();
    },
    pauseVideo() {
      this.player.pauseVideo();
    },
    // triggered when video starts (emitted by Youtube API)
    playing() {
      this.isPlaying = true;
    },
    paused() {
      // when the youtube video is paused, swap back to poster
      // this.btnIcon = 'pause-circle'; // optionally change play button icon
      this.isPlaying = false;
    },
    formatDuration(s) {
      const output = (s - (s %= 60)) / 60 + (s > 9 ? ':' : ':0') + s;
      return output;
    },
    handleEmbed() {
      console.log('embed');
    },
    handleShare() {
      window.open(this.youtubeUrl, 'uc-video-share');
    },
  },
};
</script>
