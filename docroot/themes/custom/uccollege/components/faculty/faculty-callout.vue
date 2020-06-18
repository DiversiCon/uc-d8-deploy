<template>
  <uc-slider v-cloak
             class="c-callout c-callout--faculty">
    <ul>
      <li v-for="item in results"
          class="c-card">
        <a :href="`${item.urlAlias}`">

          <div class="c-callout__img">
            <img v-if="item.image && item.image.resized && item.showPhoto"
                 :src="item.image.resized"
                 alt="" >

            <svg v-else
                 class="c-icon">
              <use xlink:href="/themes/custom/uccollege/dist/icons.svg#phoenix"/>
            </svg>
          </div>

          <h4>
            {{ item.firstName }} {{ item.lastName }}
            <span v-for="title in item.academicAppointments.slice(0,2)">{{ title.title }} of {{ title.department }}</span>
          </h4>
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
import ucSlider from '../slider/slider.vue';
import FacultyAPI from './FacultyAPI';

export default {
  name: 'UcFacultyCallout',
  components: {
    ucSlider,
  },
  props: {
    endpoint: {
      type: String,
      default: 'http://bsd-data.dev.uchicago.edu/api/node/event',
    },
    facultyIds: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      results: [],
    };
  },
  async mounted() {
    const vm = this;
    const facultyAPI = new FacultyAPI({ endpoint: this.endpoint });

    vm.results = await facultyAPI.getFacultyById(this.facultyIds);
  },
};
</script>
