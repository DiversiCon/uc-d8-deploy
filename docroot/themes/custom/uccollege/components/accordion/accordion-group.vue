<template>
	<div class="c-accordion-group__wrap">
		<h2 v-if="heading" class="c-accordion-group__heading" :class="{'c-accordion-group__heading--centered' : headingCentered}">{{ heading }}</h2>
		<ul class="c-accordion-group" :class="{ 'c-accordion-group--2col': showColumns }">
			<slot></slot>
		</ul>
	</div>
</template>

<script>
  export default {
    name: "uc-accordion-group",
    props: {
      single: {
        type: Boolean,
        default: false,
      },
      columns: {
        type: Number,
        default: 1,
      },
      heading: String,
      headingCentered: {
        type: Boolean,
        default: false,
      },
    },
    data() {
      return {
        activeItem: 0,
        collapsePoint: 1080,
        isMobile: window.innerWidth < this.collapsePoint,
      }
    },
    computed: {
      showColumns() {
        return !this.isMobile && this.columns > 1;
      }
    },
    mounted() {
      const vm = this;

      // the collapsePoint specifies where accordion items should become accordions
      // this is primarily used on the homepage so that two-column accordion groups
      // can become accordions earlier
      if (vm.columns > 1) {
        vm.collapsePoint = 1080;
      }

      vm.isMobile = window.innerWidth < this.collapsePoint;

      vm.$on('toggled', (idx) => {
        vm.activeItem = idx;
        vm.toggleItems(idx);
      });

      window.addEventListener("resize", this.debounce(200, () => {
        vm.isMobile = window.innerWidth < vm.collapsePoint
      }));

      // Listen for an event to reset all accordions in this group
      this.$parent.$on('resetAll', () => {
        this.$emit('resetItems', 'all');
      });
    },
    methods: {
      toggleItems(idx) {
        // broadcast a reset event to all other open accordion items
        if (this.single) {
          this.$emit('resetItems', idx);
        }
      }
    }
  }
</script>
