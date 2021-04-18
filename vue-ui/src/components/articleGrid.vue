<template>

  <div>
    <div class="flex-row col-12 d-flex justify-content-center">
      <div class="row justify-content-center col-12 p-0">
        <div v-for="tile in tileArray" :key="tile.id" v-show="tileArray.length !== 0">
          <aTile :tile="tile"/>
        </div>
      </div>
    </div>
  </div>

</template>

<script>

  import GlobalMixin from "@/mixins/global-mixin";
  import articleTile from "@/components/articleTile";

  export default {
    name: "articleGrid",
    mixins:[GlobalMixin],
    components:{
      aTile: articleTile
    },
    data(){
      return{
        tileArray: [],
        html: "",
      }
    },
    methods:{
      getTiles(){
        this.callAPI(this.ARTICLE_URL, 'get')
            .then(response => {
              this.tileArray = response.data;
            })
            .catch(errors => {
              errors;
            });
      }
    },
    mounted() {
      this.getTiles()
    }
  }
</script>
