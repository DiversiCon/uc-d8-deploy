<template>
  <div class="c-starter">
    <p>I am the starter component.</p>
    <!-- @slot Main content slot -->
    <slot/>
  </div>
</template>

<script>
export default {
  name: 'Starter',
  mounted() {
    console.log('starter component mounted');
  },
  methods: {

  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
  // Only directly import this component's sass file if it *MUST*
  // be scoped

  // import global sass dependencies
  // @import './../../src/sass/dependencies';
  // import this component's sass file, so that it becomes scoped.
  // @import './c-starter';
</style>
