<template>
  <div class="flex-column row my-3">


    <h1>Comments: </h1>

    <div class=" col-12" v-show="loggedIn">

      <b-form-group label="New Comment" label-for="input-3">
        <b-form-textarea
            v-model="newComment.body"
            required
            placeholder="Comment..."
            class="form-control-lg"
            :state="bodyState"
        ></b-form-textarea>
        <b-form-invalid-feedback id="input-live-feedback">
          Your comment must be 8 - 200 characters.
        </b-form-invalid-feedback>
      </b-form-group>

      <b-form-group label-for="input-3">
        <b-button class="m-2" @click="postComment" variant="success" :disabled="submitState">Post Comment</b-button>
      </b-form-group>


    </div>


    <div class="flex-row col-12 d-flex p-0 m-0">
      <div class="flex-column col-12 p-0 m-0">
        <div v-for="com in commentArray" :key="com.id" v-show="commentArray.length !== 0">
          <userComment :com="com" class="my-4"/>
        </div>
      </div>
    </div>

  </div>
</template>

<script>

import GlobalMixin from "@/mixins/global-mixin";
import comment from "@/components/comment";

export default {
  name: "commentSection",
  mixins:[GlobalMixin],
  components:{
    userComment: comment
  },
  props:{
    artID: String
  },
  data(){
    return{
      commentArray: [],
      html: "",
      newComment: {
        alias: '',
        body: '',
        artID: this.artID,
        username: '',
        token: ''
      },
    }
  },
  methods:{
    genComments(){
      this.callAPI(this.COMMENT_URL,'get', {'articleid':this.artID})
          .then(response => {
            this.commentArray = response.data;
          })
          .catch(errors => {
            errors;
          }).finally(() => {

        this.newComment.body = '';
          });
    },
    postComment(){
      this.newComment.username = this.getCookie('username');
      this.newComment.token = this.getCookie('token');
      if (!this.submitState) {
        this.newComment['articleid'] = this.artID;
        this.callAPI(this.COMMENT_URL, 'post', this.newComment)
            .finally(() => {
              this.genComments();

            });
      }
    },
  },
  computed:{
    bodyState() {
      return this.newComment.body === '' ? null : this.newComment.body.length > 7 ?
          this.newComment.body.length < 200: false;
    },
    submitState() {
      return !(this.bodyState);
    },
    loggedIn(){
      return ! (this.getCookie('username') === "");
    }
  },
  watch:{
    artID:{
      handler(newArt, oldArt){
        if (newArt !== oldArt || newArt !== -1){
          this.genComments();
        }
        this.error = {};
      }
    }
  }
}
</script>
