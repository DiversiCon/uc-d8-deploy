<template>
  <section
    v-if="alertActive"
    class="c-alert">
    <div class="c-alert__ctn">
      <button
        @click.prevent="handleClick">
        <span>Close alert message</span>
        <uc-icon glyph="x-close"/>
      </button>

      <h2>{{ heading }}</h2>
      <p>{{ description }}
        <a
          v-if="url"
          :href="url"
          :target="urlTarget">{{ urlText }}<span>…</span></a>
      </p>
    </div>
  </section>
</template>

<script>
const UcIcon = () => import(/* webpackChunkName: "icon-lazy" */ '../icon/icon.vue');

export default {
  name: 'uc-alert',
  components: {
    UcIcon,
  },
  props: {
    heading: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      required: true,
    },
    url: String,
    urlText: String,
    urlTarget: {
      type: String,
      default: '_self',
    },
  },
  data() {
    return {
      alertActive: false,
    };
  },
  mounted() {
    this.checkAlertSleep();
  },
  methods: {
    handleClick() {
      // if the alert has been closed, set a
      // timestamp in localstorage equivalent
      // to 1 hour from now
      this.setAlertSleep();
      this.alertActive = false;
    },
    setAlertSleep() {
      const dt = new Date();
      // set time for 1 hour from now
      dt.setHours(dt.getHours() + 1);
      localStorage.setItem('ucc-alert-sleep', dt);
    },
    checkAlertSleep() {
      const now = new Date();
      const sleepTime = new Date(localStorage.getItem('ucc-alert-sleep'));
      // if the current time is greater than what's in
      // localstorage, show the alert menu
      if (now.getTime() > sleepTime.getTime()) {
        this.alertActive = true;
      }
    },
  },
};
</script>
