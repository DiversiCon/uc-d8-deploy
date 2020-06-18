<template>
  <span class="c-link-indicator">
    <svg class="c-icon">
      <use :xlink:href="`/themes/custom/uccollege/dist/icons.svg#${iconType}`"/>
    </svg>
    <span class="c-link-indicator__text">{{ displayText }}</span>
  </span>
</template>

<script>
  import moment from 'moment';
  import 'moment-duration-format';

  export default {
    name: 'uc-link-indicator',
    props: {
      type: {
        type: String,
        default: 'message',
        // Supported types:
        //  message
        //  youtube
      },
      text: {
        type: String,
      },
      id: {
        type: String,
        default: null,
      }
    },
    data() {
      return {
        displayText: null,
        iconType: null,
      };
    },
    created() {
      // In the event we load the Google API, we're displaying a video duration.
      this.$on('gapi-loaded', this.getVideoDuration);
    },
    destroyed() {
      this.$off('gapi-loaded', this.getVideoDuration);
    },
    async mounted() {
      const vm = this;

      // If the indicator type is a youtube video, some additional setup steps are required.
      if (vm.type === 'youtube') {
        vm.iconType = 'video';

        // Load the Google API library.
        gapi.load('client', vm.initGoogleApiClient);
      }
      else {
        vm.iconType = 'crest';
        vm.displayText = vm.text;
      }
    },
    methods: {
      /**
       * Initializes the Google API client.
       */
      initGoogleApiClient() {
        const vm = this;

        // Initalize the Google API client. We're only interested in the YouTube Data API.
        gapi.client.init({
          apiKey: 'AIzaSyDR4nLijAqn3QskXJ1RUv4J5HpN2rcFsGA',
          discoveryDocs: [
            'https://www.googleapis.com/discovery/v1/apis/youtube/v3/rest',
          ],
        }).then(() => {
          // The API is loaded successfully. Emit the loaded event.
          vm.$emit('gapi-loaded');
        });
      },
      /**
       * Retrieves and displays the video duration from the the YouTube Data API.
       */
      async getVideoDuration() {
        const vm = this;

        // Make sure that a YouTube video id was provided.
        if (vm.id != null) {
          // Retrieve the content details, which contain the duration.
          let response = await gapi.client.youtube.videos.list({
            "part": "contentDetails",
            "id": vm.id
          });

          if (response.result.items.length > 0) {
            // Only use the first result.
            const videoDetails = response.result.items[0];

            // Create the duration, and format it appropriately.
            const videoLength = moment.duration(videoDetails.contentDetails.duration);

            // Slightly different format depending on if only seconds are present.
            if (videoLength.hours() > 0 || videoLength.minutes() > 0) {
              vm.displayText = videoLength.format('HH:mm:ss');
            }
            else {
              vm.displayText = videoLength.format('m:ss', { trim: false });
            }
          }
        }
      }
    },
  };
</script>
