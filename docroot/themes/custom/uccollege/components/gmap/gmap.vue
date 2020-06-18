<template>
  <div class="c-gmap">
    <h3 v-if="heading">{{ heading }}</h3>
    <div id="gmap"
         class="c-gmap__ctn"/>
  </div>
</template>

<script>
export default {
  name: 'UcGmap',
  props: {
    apikey: {
      type: String,
      default: 'AIzaSyClm6qw2yjOWH8V-dmAZfcWKHSU9TBwHBU',
    },
    lat: {
      type: String,
      required: true,
    },
    lon: {
      type: String,
      required: true,
    },
    zoom: {
      type: Number,
      default: 18,
    },
    heading: String,
  },
  mounted() {
    const vm = this;
    vm.loadScript();

    // poll for the google object to be available
    const poll = setInterval(() => {
      if (typeof (google) !== 'undefined') {
        vm.loadMap();
        clearInterval(poll);
      }
    }, 500);
  },
  methods: {
    loadScript() {
      const googleMapScript = document.createElement('script');
      const scriptUrl = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(this.apikey)}`;

      googleMapScript.setAttribute('src', scriptUrl);
      googleMapScript.setAttribute('async', '');
      googleMapScript.setAttribute('defer', '');
      document.head.appendChild(googleMapScript);
    },
    loadMap() {
      const vm = this;
      const latlon = new google.maps.LatLng(vm.lat, vm.lon);
      const map = new google.maps.Map(document.getElementById('gmap'), {
        center: latlon,
        zoom: vm.zoom,
      });
    },
  },
};
</script>
