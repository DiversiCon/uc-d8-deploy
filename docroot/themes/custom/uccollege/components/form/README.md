# Form component

Generic form styles, initially built for the Feedback form. Form can be vue or Drupal form.

The form itself is outside the `form.vue` file as an inline template in `form.html.twig`. This gives us all the functions normally available in a Vue template block, but also can be dynamically filled with authorable data without relying on props, etc.

## Validation
Validation is handled by the [Vuelidate](https://monterail.github.io/vuelidate/) plugin.  

## Usage

```html
<uc-form inline-template>
  <div class="c-form">
    <form action="/" method="post" @submit.prevent="handleSubmit">
    <!-- form goodness here -->
    </form>
  </div>
</uc-form>
```