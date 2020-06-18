<template>
  <figure class="c-pullquote">
    <blockquote>
      {{ quote }}
    </blockquote>
    <figcaption>
      {{ attribution }}
    </figcaption>
    <div :data-a2a-title="tweetText"
         class="c-pullquote__share a2a_kit a2a_kit_size_32">
      <a class="a2a_button_twitter">
        <span class="show-for-sr">{{ attribution }}</span>
        <uc-icon glyph="twitter"/>
      </a>
    </div>
  </figure>
</template>

<script>
const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');

// Wrapper for pull quotes.
export default {
  name: 'UcPullquote',
  components: {
    UcIcon,
  },
  props: {
    quote: {
      type: String,
      required: true,
    },
    attribution: String,
  },
  computed: {
    tweetText() {
      return (this.attribution) ? `${this.quote} -- ${this.attribution}` : this.quote;
    },
  },
};
</script>
