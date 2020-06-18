<template>
  <a class="c-skip-nav"
     href="#"
     @click.prevent="skipTo">{{ text }}</a>
</template>

<script>
export default {
  name: 'UcSkipNav',
  props: {
    text: {
      type: String,
      default: 'Skip to main content',
    },
  },
  methods: {
    skipTo() {
      // Determine container to focus on.
      // If the global alert is present, focus on that.
      // If not, identify the first instance of the 'l-container' class.
      const target = (document.querySelector('.c-alert')) ? document.querySelector('.c-alert') : document.querySelectorAll('.l-container')[0];

      // Set the target div's tabindex and focus on it
      target.tabIndex = -1;
      target.focus();

      // when the target is blurred, remove that tabindex setting
      target.addEventListener('blur', () => {
        target.removeAttribute('tabIndex');
      });
    },
  },
};
</script>
