<template>
  <div class="c-quotator">
    <div v-if="direction === 'horizontal'"
         class="c-quotator__wrap">
      <div class="c-quotator__img">
        <img :src="quote.image"
             :alt="`${quote.alt}`">
      </div>
      <div class="c-quotator__text">
        <blockquote>
          “{{ quote.text }}”
          <footer v-html="`—${quote.attribution}`"/>
        </blockquote>
        <a :href="linkurl"
           class="c-cta-link show-for-large">{{ linktext }}</a>
      </div>
    </div>

    <figure v-else
            class="c-feature-quote">
      <img :src="quote.image"
           :alt="`${quote.alt}`">
      <blockquote>
        “{{ quote.text }}”
      </blockquote>
      <figcaption>
        <cite>
          <span class="c-feature-quote__attribution"
                v-html="`— ${ quote.attribution }`"/>
          <span v-if="quote.attribution_additional_info.length !== 0"
                class="c-feature-quote__attribution-extra">
            {{ quote.attribution_additional_info }}
          </span>
        </cite>
        <a v-if="quote.linktext.length !== 0"
           :href="quote.linkurl" :target="quote.linktarget">{{ quote.linktext }}</a>
      </figcaption>
    </figure>
  </div>
</template>

<script>
export default {
  name: 'UcQuotator',
  props: {
    quotes: Array,
    linktext: String,
    linkurl: String,
    direction: String,
  },
  data() {
    return {
      quote: this.quotes[Math.floor(Math.random() * this.quotes.length)],
    };
  },
};
</script>
