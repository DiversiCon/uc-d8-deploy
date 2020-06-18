<template>
  <section :class="{'hide-for-medium': mobileonly }"
           class="c-teaser l-stripe c-teaser--external">

    <h2 v-if="topic"
        class="t-heading--topic"><a :href="topicUrl">{{ topic }}</a></h2>
    <h3 v-if="heading"
        class="t-heading--medium">{{ heading }}</h3>

    <component :is="(mobileslider) ? 'uc-slider' : 'div'">
      <ul>
        <li v-for="(item, index) in content"
            :key="`item-${index}`">
          <div class="c-teaser__img">
            <a :href="item.url">
              <div v-if="showIcon(item.type)"
                   class="c-media-icon">
                <uc-icon :class="`c-icon--${item.type}`"
                         :glyph="item.type"/>
              </div>
              <img :data-src="item.image"
                   :alt="item.title"
                   class="lazyload">
            </a>
          </div>

          <a :href="item.url"
             class="c-teaser__link">{{ item.title }}</a>
          <div v-if="item.subheading"
               class="c-teaser__subtitle">{{ item.subheading }}</div>
        </li>

      </ul>
    </component>
  </section>
</template>

<script>
import axios from 'axios';

const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');
const UcSlider = () => import(/* webpackChunkName: "slider-lazy" */ '../slider/slider.vue');

export default {
  name: 'UcTeaser',
  components: {
    UcIcon,
    UcSlider,
  },
  props: {
    heading: String,
    topic: String,
    topicUrl: String,
    datasource: {
      type: String,
      default: '/themes/custom/uccollege/components/teaser/news.json',
    },
    mobileslider: {
      type: Boolean,
      default: false,
    },
    mobileonly: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      content: null,
    };
  },
  mounted() {
    this.getAllData();
  },
  methods: {
    getAllData() {
      const vm = this;

      axios.get(vm.datasource)
        .then((response) => {
          // all the data, including dropdown names
          vm.content = response.data.items;
        })
        .catch((error) => {
          // handle error
          console.log(error);
        })
        .then(() => {
          // always executed
        });
    },
    showIcon(type) {
      const icons = ['podcast', 'video'];
      return (icons.indexOf(type) !== -1);
    },
  },
};
</script>
