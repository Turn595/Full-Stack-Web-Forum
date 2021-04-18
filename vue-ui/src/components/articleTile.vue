<template>
  <div class="m-4">
    <b-card
        :title=tile.title
        :img-src=tile.bannerURL
        img-alt="Image"
        img-top
        tag="article"
        style="max-width: 25rem;"
        class="mb-2"
        @click="genURL"
    >

      <h6>
        {{date = new Date(tile.date).toDateString()}}
      </h6>

      <b-card-text>
         {{tile.body.slice(0,120)}}...
      </b-card-text>
    </b-card>
  </div>
</template>

<script>
  export default {
    name: "articleTile",
    props:{
      tile:{
        type:Object
      }
    },
    methods:{
      genURL(){
        let URL = "/article?id="+this.tile.id;
        this.$router.push(URL);
      }
    }
  }
</script>