<template>
  <div class="mt-5 border p-5 bg-light">

    <div>
      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facere id ipsum magnam magni nesciunt nostrum sunt unde! Delectus, maxime, quae. Aperiam delectus, illo inventore necessitatibus ratione reprehenderit repudiandae. Dolore, veritatis.</p>
    </div>


    <b-form @submit="onSubmit" @reset="onReset" v-if="show">
      <b-form-group
          id="input-group-1"
          label="Email address:"
          label-for="input-1"
          description="This stays just between us."
      >
        <b-form-input
            id="input-1"
            v-model="form.email"
            type="email"
            required
            placeholder="Enter email"
        ></b-form-input>
      </b-form-group>

      <b-form-group id="input-group-2" label="Your Name:" label-for="input-2">
        <b-form-input
            id="input-2"
            v-model="form.name"
            required
            placeholder="Enter name"
        ></b-form-input>
      </b-form-group>

      <b-form-group id="input-group-3" label="Let us know what you think:" label-for="input-3">
        <b-form-textarea
            id="input-3"
            v-model="form.content"
            required
            placeholder="What should we know?
    "
        ></b-form-textarea>
      </b-form-group>

      <b-button class="m-2" type="submit" variant="success">Submit</b-button>
      <b-button class="m-2" type="reset" variant="danger">Reset</b-button>
    </b-form>
    <b-card class="mt-3" header="Form Data Result">
      <pre class="m-0">{{ form }}</pre>
    </b-card>
  </div>
</template>

<script>

  export default {
    name: "emailForm",
    data() {
      return {
        form: {
          email: '',
          name: '',
          content: '',
        },
        show: true
      }
    },
    methods: {
      onSubmit(evt) {
        evt.preventDefault()
        alert(JSON.stringify(this.form))
      },
      onReset(evt) {
        evt.preventDefault()
        // Reset our form values
        this.form.email = ''
        this.form.name = ''
        this.form.food = ''
        // Trick to reset/clear native browser form validation state
        this.show = false
        this.$nextTick(() => {
          this.show = true
        })
      }
    }
  }

</script>
