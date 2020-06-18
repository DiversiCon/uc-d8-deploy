<template>
  <div>
    <div v-if="grid && noResults"
         class="c-events--noresults">
      <h3 class="t-heading--medium">There are currently no featured events</h3>
    </div>
    <component v-if="results.length !== 0"
               :is="tag"
               :class="{ 'c-cards--4up': !grid }"
               class="c-cards">
      <ul class="c-cards__content">
        <li v-for="(item, index) in results"
            :key="`item-${index}`"
            class="c-card">
          <div class="c-card__img">
            <!-- Use pathAlias for the link, fallback to id (item.pathAlias includes an initial slash, whereas item.id does not) -->
            <a :href="item.pathAlias ? `/event${item.pathAlias}` : `/event/${item.id}`">
              <svg class="c-icon">
                <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix"/>
              </svg>
              <picture v-if="item.imgId"><img :data-src="images[item.imgId]"
                                              :alt="item.imgCaption"
                                              class="lazyload"></picture>
            </a>
          </div>
          <p v-if="item.status"
             class="c-card__status">{{ item.status }}</p>
          <h2 class="c-card__heading">
            <!-- Use pathAlias for the link, fallback to id (item.pathAlias includes an initial slash, whereas item.id does not) -->
            <a :href="item.pathAlias ? `/event${item.pathAlias}` : `/event/${item.id}`">{{ item.title }}</a>
          </h2>
          <div class="c-card__description">
            <p class="c-card__date"
               v-html="formatDate(item.start, item.end)"/>
            <p v-if="!item.allDay"
               class="c-card__time">{{ formatTime(item.start, item.end) }}</p>
          </div>
        </li>
      </ul>
      <div class="c-cards__cta" v-if="!grid">
        <a href="#"
           class="c-cta-link c-cta-link--black">All Featured Events</a></div>
    </component>
  </div>
</template>

<script>
import moment from 'moment';
import ucSlider from '../slider/slider.vue';
import EventsAPI from './EventsAPI';

export default {
  name: 'UcEventsFeatured',
  components: {
    ucSlider,
  },
  props: {
    endpoint: {
      type: String,
      default: 'http://bsd-data.dev.uchicago.edu/api/node/event',
    },
    instance: {
      type: String,
      required: true,
      default: 'pritzker',
    },
    grid: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      results: [],
      images: null,
      tag: null,
      noResults: null,
    };
  },
  async mounted() {
    const vm = this;
    vm.tag = (vm.grid) ? 'div' : 'uc-slider';

    const eventsAPI = new EventsAPI({ endpoint: this.endpoint, instance: this.instance });

    const pageLimit = (!vm.grid) ? 4 : 0;

    const resultSet = await eventsAPI.getFeaturedEvents(pageLimit);

    vm.results = resultSet.results;
    vm.images = resultSet.images;
  },
  methods: {
    formatDate(start, end) {
      let dateStringStart = moment.utc(start).format('ddd. MMM DD, YYYY');
      const dateStringEnd = moment.utc(end).format('ddd. MMM DD, YYYY');
      if (dateStringStart !== dateStringEnd) {
        dateStringStart += (this.grid) ? ' - ' : ' - <br>';
        dateStringStart += dateStringEnd;
      }
      return dateStringStart;
    },
    formatTime(start, end) {
      let timeString = moment.utc(start).format('h:mm a');
      timeString += (start !== end) ? ' - ' + moment.utc(end).format('h:mm a') : '';
      return timeString;
    },
  },
};
</script>
