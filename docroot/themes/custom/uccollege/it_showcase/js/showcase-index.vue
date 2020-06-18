<template>
  <div class="showcase__index">

    <section v-if="ready" class="showcase__sidebar">
      <h2>Components</h2>

      <div v-for="category in getCategoriesByType('Component')">
        <h3>{{ getCategoryName(category) }}</h3>
        <ul>
          <li v-for="component in getItemsByCategory('Component', category)">
            <a @click="activateItem(component.id)" :href="`#${component.id}`">{{ component.title }}</a>
          </li>
        </ul>
      </div>

      <h2>Pages</h2>
      <div v-for="category in getCategoriesByType('Page')">
        <h3>{{ getCategoryName(category) }}</h3>
        <ul>
          <li v-for="page in getItemsByCategory('Page', category)">
            <a @click="activateItem(page.id)" :href="`#${page.id}`">{{ page.title }}</a>
          </li>
        </ul>
      </div>

      <h2>Endpoints</h2>
      <div v-for="category in getCategoriesByType('Endpoint')">
        <h3>{{ getCategoryName(category) }}</h3>
        <ul>
          <li v-for="endpoint in getItemsByCategory('Endpoint', category)">
            <a @click="activateItem(endpoint.id)" :href="`#${endpoint.id}`">{{ endpoint.title }}</a>
          </li>
        </ul>
      </div>
    </section>

    <section class="showcase__main">
    <h4>Components</h4>

      <div class="showcase__list">
        <div v-for="component in components" class="showcase__item" :id="component.id">
          <a :href="component.path" class="showcase__item-title">{{ component.title }}</a>
          <p>{{ component.description }}</p>
          <ul v-if="component.links" class="showcase__links">
            <li v-for="link in component.links">
              <a :href="link.link" :target="link.target">{{ link.text }}</a>
            </li>
          </ul>
        </div>
      </div>

      <h4>Pages</h4>

      <div class="showcase__list">
        <div v-for="page in pages" class="showcase__item" :id="page.id">
          <a :href="page.path">{{ page.title }}</a>
          <p>{{ page.description }}</p>
          <ul v-if="page.links" class="showcase__links">
            <li v-for="link in page.links">
              <a :href="link.link" :target="link.target">{{ link.text }}</a>
            </li>
          </ul>
        </div>
      </div>

      <h4>Endpoints</h4>

      <div class="showcase__list">
        <div v-for="endpoint in endpoints" class="showcase__item" :id="endpoint.id">
          <a :href="endpoint.path">{{ endpoint.title }}</a>
          <p>{{ endpoint.description }}</p>
        </div>
      </div>
    </section>

  </div>
</template>

<script>
  import axios from "axios";
  import _filter from "lodash/filter";
  import _includes from "lodash/includes";
  import _isEmpty from "lodash/isEmpty";
  import _uniq from "lodash/uniq";

  export default {
    name: "uc-showcase-index",
    data() {
      return {
        allData: null,
        categories: null,
        types: null,
        pages: null,
        components: null,
        endpoints: null,
        readme: null,
        ready: false,
      }
    },
    computed: {

    },
    mounted() {
      const vm = this;
      vm.getShowcaseList();

      // only temporary
      // document.querySelector('.c-masthead').style.display = "none";
      // document.querySelector('.c-footer').style.display = "none";
      document.querySelector('#app.l-container__wrap').style.paddingTop = "0";
    },
    methods: {
      getShowcaseList() {
        const vm = this;

        axios.get('/it/showcase/api/v1/list')
          .then(function (response) {
            vm.allData = response.data;
            vm.categories = response.data.category;
            vm.types = response.data.type;
            vm.pages = _filter(response.data.items, { 'type': 'Page'});
            vm.endpoints = _filter(response.data.items, { 'type': 'Endpoint'});
            vm.components = _filter(response.data.items, { 'type': 'Component'});
            vm.readme = _filter(response.data.items, { 'type': 'Readme'});
            vm.ready = true;
          })
          .catch(function (error) {
            // handle error
            console.log(error);
          })
          .then(function () {
            // always executed
          });
      },
      activateItem(id) {
        document.getElementById(id).classList.add('active');
      },
      getCategoriesByType(type) {
        const vm = this;

        // get items of this type
        const results = _filter(vm.allData.items, { 'type': type });

        // get list of categories
        const categories = [];

        results.forEach((item) => {
          // const cat = (item.category) ? "" : item.category;
          item.category.map((cat) => {
            categories.push(cat);
          });
        });
        categories.sort().push('');
        return _uniq(categories);
      },
      getItemsByCategory(type, category) {
        // return list of items matching category
        const vm = this;
        const results = _filter(vm.allData.items, { 'type': type });
        return _filter(results, (result) => {
          return (category === '') ? _isEmpty(result.category, category) : _includes(result.category, category);
        });
      },
      getCategoryName(category) {
        return (category === '') ? 'General' : category;
      }
    }
  }
</script>
