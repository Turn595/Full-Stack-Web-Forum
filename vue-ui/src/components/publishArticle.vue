<template>

  <div class="mt-5 border p-5 bg-light">

    <div>
      <b-jumbotron header="Article Creator" lead="Unleash Your Creativity."></b-jumbotron>
    </div>

    <b-form @submit="onSubmit" @reset="onReset">

      <b-form-group label="Main Title:">
        <b-form-input
            v-model="newArticle.title"
            required
            placeholder="Main Title"
            class="form-control-lg"
            :state="titleState"
        ></b-form-input>
        <b-form-invalid-feedback id="input-live-feedback">
          Enter a title between 5 and 50 characters.
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Secondary Title:">
        <b-form-input
            v-model="newArticle.subtitle"
            required
            placeholder="Secondary Title"
            class="form-control-lg"
            :state="subtitleState"
        ></b-form-input>
        <b-form-invalid-feedback id="input-live-feedback">
          Enter a secondary title between 5 and 50 characters.
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label="Banner Image URL">
        <b-form-input
            v-model="newArticle.bannerURL"
            required
            placeholder="Banner Image URL"
            class="form-control-lg"
        ></b-form-input>
      </b-form-group>

      <b-form-group id="input-group-3" label="Content Body" label-for="input-3">
        <b-form-textarea
            v-model="newArticle.body"
            required
            placeholder="Main Content"
            class="form-control-lg"
            :state="bodyState"
        ></b-form-textarea>

        <b-form-invalid-feedback id="input-live-feedback">
          Enter a secondary title between 5 and 50 characters.
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group>
        <div>
          <label>Enter new tags separated by space</label>
          <b-form-tags
              input-id="tags-remove-on-delete"
              :input-attrs="{ 'aria-describedby': 'tags-remove-on-delete-help' }"
              v-model=tags
              separator=" "
              placeholder="Enter new tags separated by space"
              remove-on-delete
              no-add-on-enter
          ></b-form-tags>
          <b-form-text id="tags-remove-on-delete-help" class="mt-2">
            Press <kbd>Backspace</kbd> to remove the last tag entered
          </b-form-text>
        </div>
      </b-form-group>

      <b-form-group label-for="input-3">
        <b-button class="m-2" @click="postArticle" variant="success" :disabled="submitState">Create</b-button>
      </b-form-group>

      <b-button class="m-2" type="reset" variant="danger">Reset</b-button>
    </b-form>
  </div>

</template>

<script>
import GlobalMixin from "@/mixins/global-mixin";
export default {
  name: "publishArticle",
  mixins:[GlobalMixin],
  data() {
    return {
      newArticle: {
        title: '',
        subtitle: '',
        bannerURL: '',
        body: '',
        token: '',
        username: ''
      },
      tags: [],
      respArticle: {
        type:Object
      }
    }
  },
  methods: {
    onSubmit() {
      alert(JSON.stringify(this.newArticle))
    },
    onReset() {
      // Reset our form values
      this.newArticle.title = ''
      this.newArticle.subtitle = ''
      this.newArticle.body = ''
      this.newArticle.bannerURL = ''
      this.tags = []
    },
    postArticle(){
      this.newArticle.token = this.getCookie('token');
      this.newArticle.username = this.getCookie('username');
      this.callAPI(this.ARTICLE_URL,'post', {title:this.newArticle.title,subtitle:this.newArticle.subtitle,
        bannerURL:this.newArticle.bannerURL,body:this.newArticle.body,token:this.newArticle.token,username:this.newArticle.username})
          .then(response => {
            this.respArticle = response.data;
          })
          .finally(() => {
          this.postTags();
          });
    },
    postTags(){
      this.callAPI(this.TAG_URL,'post', {tag:this.tags,articleId:this.respArticle.id,token:this.newArticle.token})
      .then(()=>{
        alert('Article Posted Successfully.');
      })
      .finally(()=>{
        this.onReset();
      })

    }
  },
  computed: {
    titleState() {
      return this.newArticle.title.length === 0 ? null : this.newArticle.title.length > 4 ?
          this.newArticle.title.length < 50: false;
    },
    subtitleState() {
      return this.newArticle.subtitle.length === 0 ? null : this.newArticle.subtitle.length > 4 ?
          this.newArticle.subtitle.length < 50: false;
    },
    bodyState() {
      return this.newArticle.body.length === 0 ? null : this.newArticle.body.length > 4 ?
          this.newArticle.body.length < 5000: false;
    },
    submitState() {
      return !(this.titleState && this.subtitleState && this.bodyState);
    }
  }
}
</script>