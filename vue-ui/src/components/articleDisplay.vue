<template>

  <div class="mt-5 border p-5 bg-light">
    <b-jumbotron :header=this.article.title :lead=this.article.subtitle>
      <h5>Written By: {{this.article.username}}</h5>
    </b-jumbotron>

    <b-list-group class="m-4 flex-row flex-wrap" horizontal>
      <h3 class="mr-3">Related Tags:</h3>
      <b-list-group-item v-for="tag in tags" :key="tag.id" v-show="tags.length !== 0">
        {{tag.tag}}
      </b-list-group-item>
    </b-list-group>

    <img class="border w-100" style="max-height: 30em; object-fit:cover;"  :src=this.article.bannerURL>

    <div class="my-5">
      <p>{{this.article.body}}</p>
    </div>

    <commentSec :artID="articleID"/>
  </div>

</template>

<script>
import GlobalMixin from "@/mixins/global-mixin";
import commentSection from "@/components/commentSection";

export default {
  name: "articleContent",
  mixins:[GlobalMixin],

  components:{
    commentSec: commentSection
  },
  props:{
    content:{
      type:Object
    }
  },
  data(){
    return{
      articleID: null,
      article:{
        type:Object
      },
      tags:{
        type:Array
      }
    }
  },
  methods:{
    getTags(){
      this.callAPI(this.TAG_URL,'get', {'articleId':this.articleID})
          .then(response => {
            this.tags = response.data;
          })
          .finally(() => {
      });
    }
  },
    mounted() {
    this.articleID = this.$route.query.id;
    if (this.articleID != null){
      this.callAPI(this.ARTICLE_URL, 'get', {id:this.articleID})
          .then(response => {
            this.article = response.data[0];
          })
          .finally(() => {
          this.getTags();
      });
    }
  }
}
</script>