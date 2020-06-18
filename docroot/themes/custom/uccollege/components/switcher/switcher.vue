<template>
  <div>
    <slot v-if="showProspect"
          name="prospect"/>

    <slot v-if="showAffiliate"
          name="affiliate"/>
  </div>
</template>

<script>
export default {
  name: 'UcSwitcher',
  data() {
    return {
      showProspect: true,
      showAffiliate: false,
      cookieName: 'SignOnDefault', // try utag_main for local testing
      cookieExists: false,
    };
  },
  mounted() {
    // Disabling the switcher functionality.
    // this.cookieExists = this.detectCookie();

    // for local dev, we can force page into affiliate
    // mode by adding the "affiliate" query param
    // Disabling the switcher functionality.
    /* if(this.$route.query['affiliate'] || this.cookieExists) {
        this.showAffiliate = true;
        this.showProspect = false;
      } */
  },
  methods: {
    detectCookie() {
      return document.cookie.indexOf(this.cookieName) !== -1;
    },
  },
};
</script>
