<template>
  <div :class="`c-dropdown--${name}`"
       class="c-dropdown">

    <div class="c-dropdown__wrap">
      <button
        v-show="ready"
        ref="button"
        :id="`c-dropdown-${itemID}`"
        :class="{'expanded': expanded}"
        :style="'width: ' + buttonWidth"
        class="c-dropdown__button"
        aria-haspopup="listbox"
        :aria-expanded="`${expanded}`"
        @click.prevent="toggle"
        @keyup.esc="toggle"
        @keyup.down.prevent="toggle">
        {{ getSelected() }}</button>

      <ul
        ref="list"
        :aria-labelledby="`c-dropdown-${itemID}`"
        :class="{'expanded': expanded}"
        class="c-dropdown__list"
        role="listbox"
        @keyup.prevent.esc="close"
        @keyup.down.prevent="nextItem"
        @keyup.prevent.up="prevItem">
        <li v-for="option in options"
            :key="`option-${formatName(option.name)}`"
            :id="`option-${formatName(option.name)}`"
            :aria-selected="selectedOption === option.name"
            :value="getValue(option)"
            tabindex="0"
            role="option"
            @keyup.enter.space.prevent="handleSelect(option)"
            @click.prevent="handleSelect(option)">
          {{ option.name }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UcDropdown',
  props: {
    name: String,
    displayName: String,
    options: Array,
    onSelect: Function,
    defaultSelection: {
      type: String,
      default: 'All',
    },
  },
  data() {
    return {
      selectedOption: this.defaultSelection,
      expanded: false,
      itemID: Math.floor(Math.random() * Math.floor(1000)),
      optionContainer: null,
      ready: false,
      buttonWidth: '100%',
    };
  },
  watch: {
    options() {
      this.$nextTick(() => {
        this.getButtonWidth();
      });
    },
    // when the list is expanded, set up a click listener
    // on the body so that a click anywhere else will close it.
    expanded() {
      const body = document.getElementById('app');
      if (this.expanded) {
        body.addEventListener('click', this.toggleBlur, true);
        this.$el.style.zIndex = 60;
      } else {
        body.removeEventListener('click', this.toggleBlur, true);
        this.$el.style.zIndex = 'inherit';
      }
    },
  },
  mounted() {
    const vm = this;

    // look to see if this dropdown's "name" is in the query string
    window.addEventListener('load', () => {
      setTimeout(() => {
        if (vm.$route.query[vm.name]) {
          vm.selectFromQuery(vm.$route.query[vm.name]);
        }
        vm.ready = true;
      }, 100);
    });

    window.addEventListener('resize', this.debounce(200, vm.getButtonWidth));

    vm.$parent.$on('resetTypes', () => {
      if (vm.name === 'type') {
        vm.selectedOption = 'All';
        vm.onSelect('All');
      }
    });
  },
  methods: {
    // Which name to display in the button control
    getSelected() {
      return (this.selectedOption === 'All') ? this.displayName : this.selectedOption;
    },
    // convert the name to lowercase and remove spaces,
    // for purposes of generating option ID's
    formatName(name) {
      return name.toLowerCase().replace(/\s/g, '');
    },
    // Determine what to use as the option value
    getValue(option) {
      return (option.value) ? option.value : option.name;
    },
    // open or close the option list
    toggle() {
      this.expanded = !this.expanded;
    },
    toggleBlur(event) {
      // if the clicked element is anything but this
      // dropdown's button, close the dropdown
      if (event.target.id !== `c-dropdown-${this.itemID}`) {
        this.toggle();
      }
    },
    close() {
      // focus back on the button
      this.$el.querySelector('button').focus();
      this.expanded = false;
    },
    nextItem() {
      const next = document.activeElement.nextSibling;
      if (next) {
        next.focus();
      }
    },
    prevItem() {
      const prev = document.activeElement.previousSibling;
      if (prev) {
        prev.focus();
      }
    },
    // what to do when an option is selected
    handleSelect(option, changeQuery = true) {
      const value = (option.value) ? option.value : option.name;
      this.expanded = false;
      this.$el.querySelector('button').focus();
      this.selectedOption = option.name;
      this.$el.querySelector('.c-dropdown__list').setAttribute('aria-activedescendant', `option-${this.formatName(option.name)}`);

      if (changeQuery) {
        this.updateQuery(this.name, value);
      }

      // send selection data back to the selector
      // parent using the callback function specified
      // in dropdown props
      this.onSelect(value);
    },
    updateQuery(name, value) {
      // update the url query params on selection
      const query = Object.assign({}, this.$route.query);
      query[name] = value;
      this.$router.replace({ query });
    },
    // A dropdown value has been passed via query string. Make
    // the selection and trigger callback.
    selectFromQuery(queryVal) {
      this.selectedOption = queryVal;
      this.$el.querySelector('.c-dropdown__list').setAttribute('aria-activedescendant', `option-${this.formatName(queryVal)}`);
    },
    reset() {
      this.selectFromQuery('All');
    },
    // dropdown selections can only be as wide as the button text,
    // so to avoid weird wrapping, determine the width of the longest
    // option and apply that to the button
    getButtonWidth() {
      const list = this.$refs.list;
      if (window.innerWidth >= 768) {
        // change position to relative, get the width,
        // then change it back
        list.style.position = 'relative';
        setTimeout(() => {
          const newWidth = list.offsetWidth + 1;
          this.buttonWidth = `${newWidth}px`;
          list.style.width = `${newWidth}px`;
          list.style.position = 'absolute';
        }, 50);
      } else {
        this.buttonWidth = '100%';
        list.style.width = '100%';
      }
    },
  },
};
</script>
