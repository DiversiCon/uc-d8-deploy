<template>
  <div class="c-share-links">
    <div class="a2a_kit a2a_kit_size_32">
      <a class="a2a_button_facebook"/>
      <a class="a2a_button_twitter"/>
      <a class="a2a_button_linkedin"/>
      <a class="a2a_button_email"/>
      <a class="a2a_button_print"/>
      <a v-if="calendar"
         :href="calendar"
         class="c-share-links--calendar">
        <span class="a2a_svg a2a_s__default"/>
        <span class="a2a_label">Add to Calendar</span>
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UcShareLinks',
  props: {
    calendar: {
      type: String,
    },
  },
};
</script>
