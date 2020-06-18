<template>
  <li
    :class="{'no-accordion': !this.showAccordion}"
    class="c-accordion__item"
    @keyup.prevent.down="nextHeading"
    @keyup.prevent.up="prevHeading"
    @keyup.prevent.esc="close">
    <div
      v-if="showAccordion"
      :class="{
        'c-accordion__item--expanded': expanded,
        'c-accordion__item--hasInset': insetImage }">

      <component
        :is="tag"
        class="c-accordion__title">
        <button
          :aria-controls="`accordion-${index}`"
          :aria-expanded="`${expanded}`"
          @click.prevent="toggle"
          v-html="buttonText">
        </button>
        <div
          :class="{'isToggled': expanded}"
          class="c-accordion__toggle-icon"
          aria-hidden="true">
        </div>
      </component>

      <div
        :class="contentClass()"
        :aria-hidden="!expanded"
        :id="`accordion-${index}`"
        class="c-accordion__ctn">
        <slot/>
      </div>
    </div>

    <div v-else>
      <component
        :is="tag"
        class="c-accordion__heading">{{ title }}
      </component>
      <slot/>
    </div>
  </li>
</template>

<script>
import StringUtil from '../../src/js/string-util';

export default {
  name: 'uc-accordion',
  props: {
    title: String,
    subtitle: String,
    twoColumn: {
      type: Boolean,
      default: false,
    },
    tag: {
      type: String,
      default: 'h3',
    },
    open: {
      type: Boolean,
      default: false,
    },
    mobileOnly: {
      type: Boolean,
      default: false,
    },
    insetImage: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      expanded: false,
      index: Math.floor(Math.random() * Math.floor(1000)),
      isMobile: window.innerWidth < this.$parent.collapsePoint,
      buttonText: null,
    };
  },
  computed: {
    showAccordion() {
      return (this.isMobile && this.mobileOnly) || !this.mobileOnly;
    },
  },
  mounted() {
    const vm = this;
    // should this item be open by default?
    vm.expanded = this.open;

    vm.buttonText = vm.getButtonText();

    window.addEventListener('resize', this.debounce(200, () => {
      vm.isMobile = window.innerWidth < this.$parent.collapsePoint;
    }));

    // if this accordion item hears a reset from the uc-accordion-group parent
    this.$parent.$on('resetItems', (idx) => {
      this.reset(idx);
    });
  },
  methods: {
    toggle(emit = true) {
      // emit an event up to the uc-accordion-group parent
      if (emit && this.$parent.single) {
        this.$parent.$emit('toggled', this.index);
      }
      this.expanded = !this.expanded;
    },
    close() {
      if (this.expanded) {
        this.toggle();
      }
    },
    reset(idx) {
      // if this accordion item is open and does not match the ID of the active item, close it
      if ((idx !== this.index || this.index === 'all') && this.expanded) {
        this.toggle(false);
      }
    },
    nextHeading() {
      const next = this.$el.nextElementSibling;
      if (next) {
        const heading = next.getElementsByTagName('button')[0];
        if (heading) {
          heading.focus();
        }
      }
    },
    prevHeading() {
      const prev = this.$el.previousElementSibling;
      if (prev) {
        const heading = prev.getElementsByTagName('button')[0];
        if (heading) {
          heading.focus();
        }
      }
    },
    contentClass() {
      return {
        'c-accordion-group--2col': this.twoColumn,
        expanded: this.expanded,
      };
    },
    getButtonText() {
      // if the accordion button has an inset image on mobile, grab it from
      // the accordion body and insert it into the html for the button title
      let inset = '';
      if (this.insetImage) {
        const imageEl = this.$el.querySelector('.c-list__item-image img');

        if (imageEl != null) {
          const imgSrc = imageEl.src;

          inset = `<img data-src="${imgSrc}" alt="" class="lazyload">`;
        }
      }
      let title = (this.subtitle) ? `${this.title} <span>${this.subtitle}</span>` : this.title;

      // Clean the title. Ensure any single or double quotes are unescaped.
      title = StringUtil.cleanString(title);

      return inset + `<div>${title}</div>`;
    },
  },
};
</script>
