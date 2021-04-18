<template class="w-100">
  <div class=" col-12">

    <div class="card p-3">
      <div class="container">
        <h4><b>{{this.com.username}}</b></h4>
        <h5>{{date = new Date(this.com.date).toDateString()}} : {{date = new Date(this.com.date).toLocaleTimeString()}}</h5>
        <p>{{this.com.body}}</p>
        <b-button @click="showModal" v-show="matchUser">Edit Comment</b-button>
      </div>
    </div>


    <b-modal :ref="this.com.id" id="edit-modal" hide-header-close hide-footer no-close-on-esc no-close-on-backdrop title="Modify Comment">
      <b-form>

        <b-form-group label="Edit Comment">
          <b-form-textarea
              v-model="newBody"
              required
              placeholder="Comment"
              :state="bodyState"
          ></b-form-textarea>
          <!-- Invalid Feedback -->
          <b-form-invalid-feedback id="input-live-feedback">
            Your comment must be 8 - 200 characters.
          </b-form-invalid-feedback>
        </b-form-group>
        <b-button class="float-right mx-3"  variant="primary" @click="editComment" :disabled="!bodyState">Edit</b-button>
        <b-button class="float-right mx-3" @click="hideModal">Cancel</b-button>
        <b-button class="float-right mx-3" variant="danger" @click="deleteComment">Delete</b-button>


      </b-form>
    </b-modal>

  </div>

</template>

<script>
import GlobalMixin from "@/mixins/global-mixin";

export default {
mixins: [GlobalMixin],
name: "comment",
  data(){
    return{
      newBody: '',
      token: '',
      username: '',
      matchUser: false
    }
  },
  props:{
    com:{
      type:Object
    }
  },
  computed:{
    bodyState() {
      return this.newBody === '' ? null : this.newBody.length > 7 ?
          this.newBody.length < 200: false;
    }
  },
  methods:{
    deleteComment(){

      this.username = this.getCookie('username');
      this.token = this.getCookie('token');
        this.callAPI(this.COMMENT_URL, 'delete', {id:this.com.id,username:this.username,token:this.token})
            .finally(() => {
              window.location.reload();
            });
    },
    editComment(){
      this.username = this.getCookie('username');
      this.token = this.getCookie('token');
      if (this.bodyState) {
        this.callAPI(this.COMMENT_URL, 'put', {id:this.com.id,username:this.username,token:this.token,body:this.newBody})
            .finally(() => {
              window.location.reload();
            });
      }
    },
    checkUserMatch(){
      this.matchUser = this.com.username == this.getCookie('username');
    },
    hideModal(){
      this.$refs[this.com.id].hide();
    },
    showModal(){
      this.$refs[this.com.id].show();
    }
  },
  mounted() {
    this.checkUserMatch();
    this.newBody = this.com.body;
  }
}

</script>
<style scoped>
.card {
  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
}

.container {
  padding: 2px 16px;
}

img {
  max-width: 3em;
}
</style>