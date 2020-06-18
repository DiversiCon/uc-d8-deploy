<script>
import { email, required } from 'vuelidate/lib/validators';

export default {
  name: 'UcForm',
  data() {
    return {
      email: '',
      name: '',
      comments: '',
      category: '',
      formComplete: false,
    };
  },
  mounted() {
    console.log('form component mounted');
  },
  methods: {
    getForm() {
      return this.$el.getElementsByTagName('form')[0];
    },
    handleSubmit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
      } else {
        this.formComplete = true;
        this.getForm().submit();
      }
    },
  },
  validations: {
    name: {
      required,
    },

    category: {
      required,
    },

    comments: {
      required,
    },

    email: {
      email,
      required,
    },
  },
};
</script>
