<template>
  <div class="c-hero-rotator" :class="{'c-hero-rotator--has-caption': (hero.left_media.caption !== null || hero.right_media.caption !== null) }">
    <div class="grid-2up grid-2up--nogap">
      <div class="grid-2up__title">
        <h1 class="t-heading--superscript" :class="{ 'c-hero-rotator__heading': hero.cta.url !== '' }">
          <span>{{ hero.headline_start }}</span>
          {{ hero.headline_problem }}<br />
          {{ hero.headline_solution }}
        </h1>
        <h1 v-if="hero.cta.url !== ''" class="t-heading--superscript c-hero-rotator__heading-mobile">
          <a :href="hero.cta.url"
             :target="hero.cta.target">
            <span>{{ hero.headline_start }}</span>
            {{ hero.headline_problem }}<br />
            {{ hero.headline_solution }}
          </a>
        </h1>
      </div>

      <div class="grid-2up__cell1">
        <img v-if="hero.left_media.type === 'image'"
          :src="hero.left_media.image" />

        <uc-video-background v-else v-square:limit="0"
           background="#6f97bf"
           :poster="hero.left_media.poster"
           width="100%"
           height="100%"
           alt="Video Background"
           :src="hero.left_media.src">
        </uc-video-background>

        <p v-if="hero.left_media.caption !== null"
           class="c-hero-rotator__caption">{{ hero.left_media.caption }}</p>
      </div>

      <div class="grid-2up__cell2">
        <a v-if="hero.cta.url !== ''"
           :href="hero.cta.url"
           :target="hero.cta.target"
           class="c-cta-link__grid2up"
           :class="{ 'c-cta-link__grid2up--white': (hero.cta.theme === 'white') }">{{ hero.cta.text }}</a>

        <img v-if="hero.right_media.type === 'image'"
             :src="hero.right_media.image" />

        <uc-video-background v-else v-square:limit="0"
           :background="hero.right_media.background"
           :poster="hero.right_media.poster"
           :width="hero.right_media.width"
           :height="hero.right_media.height"
           :alt="hero.right_media.alt"
           :src="hero.right_media.src">
        </uc-video-background>

        <p v-if="hero.right_media.caption !== null"
           class="c-hero-rotator__caption">{{ hero.right_media.caption }}</p>
      </div>
    </div>
  </div>
</template>

<script>
const UcVideoBackground = () => import(/* webpackChunkName: "video-background-lazy" */ '../../components/video-background/video-background.vue');

export default {
  name: 'uc-hero-rotator',
  components: {
    UcVideoBackground,
  },
  props: {
    heroes: {
      type: Array,
    },
  },
  data() {
    return {
      hero: this.heroes[Math.floor(Math.random() * this.heroes.length)],
    };
  },
};
</script>
