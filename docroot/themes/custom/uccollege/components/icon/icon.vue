<template>
  <svg :class="classNames"
       :width="width"
       :height="height"
       :fill="color"
       class="c-icon">
    <use :xlink:href="`/themes/custom/uccollege/dist/icons.svg#${glyph}`"/>
  </svg>
</template>

<script>
export default {
  name: 'UcIcon',
  props: {
    className: {
      type: String,
      default: '',
    },
    glyph: String,
    width: String,
    height: String,
    color: String,
  },
  computed: {
    classNames() {
      return `c-icon--${this.glyph} ${this.className}`;
    },
  },
};
</script>
