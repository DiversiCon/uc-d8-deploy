<template>
  <uc-slider v-cloak
             class="c-callout c-callout--event">
    <ul>
      <li v-for="(item, index) in results"
          :key="`item-${index}`"
          class="c-card">
        <!-- Use pathAlias for the link, fallback to id (item.pathAlias includes an initial slash, whereas item.id does not) -->
        <a :href="item.pathAlias ? `/event${item.pathAlias}` : `/event/${item.id}`">
          <div v-if="showImages" class="c-callout__img">
            <img v-if="item.imgId"
                 :src="images[item.imgId]"
                 alt="" >

            <svg v-else
                 class="c-icon">
              <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix"/>
            </svg>
          </div>

          <time v-if="!showImages">
            <span v-html="formatMonth(item.start)" class="month"></span>
            <span v-html="formatDay(item.start)" class="day"></span>
          </time>

          <p class="c-callout__item-text">
            {{ item.title }}
            <span v-if="showImages" v-html="formatDate(item.start, item.end)"/>
            <span v-html="formatTime(item.start, item.end)"/>
          </p>
        </a>
      </li>
    </ul>

    <div class="is-visible c-gallery__button-wrap">
      <a href=""
         class="swiper-button-prev"
         aria-label="previous slide">
        <uc-icon glyph="arrow-left"/>
      </a>
      <a href=""
         class="swiper-button-next"
         aria-label="next slide">
        <uc-icon glyph="arrow-right"/>
      </a>
    </div>
  </uc-slider>
</template>

<script>
import moment from 'moment';
import ucSlider from '../slider/slider.vue';
import EventsAPI from './EventsAPI';

export default {
  name: 'UcEventsCallout',
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
    eventIds: {
      type: Array,
      default: () => [],
    },
    calloutType: {
      type: String,
      default: 'featured',
    },
    showImages: {
      type: Boolean,
      default: true,
    }
  },
  data() {
    return {
      results: [],
      images: null,
      noResults: null,
    };
  },
  async mounted() {
    const vm = this;
    const eventsAPI = new EventsAPI({ endpoint: this.endpoint, instance: this.instance });

    if (vm.calloutType === 'curated') {
      const resultSet = await eventsAPI.getEventsById(this.eventIds);

      vm.results = resultSet.results;
      vm.images = resultSet.images;
    } else {
      const resultSet = await eventsAPI.getFeaturedEvents(4);

      vm.results = resultSet.results;
      vm.images = resultSet.images;
    }
  },
  methods: {
    formatDate(start, end) {
      let dateStringStart = moment.utc(start).format('ddd. MMM DD');
      const dateStringEnd = moment.utc(end).format('ddd. MMM DD');
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
    formatMonth(datetime) {
      return moment.utc(datetime).format('MMMM');
    },
    formatDay(datetime) {
      return moment.utc(datetime).format('DD');
    }
  },
};
</script>
