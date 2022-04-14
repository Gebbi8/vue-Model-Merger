<template>
  <div id="user-merge">
    <div id="generalInfo">
      <h3>Semi-automatic Merging</h3>
      <h4>Still under development</h4>
      <p>
        You are in the semi-automatic merging mode. This means you can
        cherry-pick the changes you want to apply. To base the merging on one
        model and add parts, choose a main model.
      </p>
    </div>

    <Carousel
      :decArr="decisionArr"
      @arrChanged="decisionArr = $event"
      @slideChange="currentSlide = $ent"
      @gotOldDoc="oldDocument = $event"
      @gotNewDoc="newDocument = $event"
    />
    <Selection
      :decArr="decisionArr"
      :slideChng="currentSlide"
      :oldDoc="oldDocument"
      :newDoc="newDocument"
    />
  </div>
  <div id="devOutput" v-if="dev">
    <h3>Dev mode is active!</h3>
    <div id="sbgnJson"></div>
    <div id="container"></div>
  </div>
</template>


<script>
import { callDiVil } from "../../DiVil/javascriptAndCss/init";
import Carousel from "./Carousel.vue";
import Selection from "./Selection.vue";

//import dev data
import devData from "/dev/navicenta/sbgnJson.json";

export default {
  name: "user-merge",
  components: {
    Carousel,
    Selection,
  },
  data() {
    return {
      json: devData,
      decisionArr: [],
      reactionsArr: [],
      currentSlide: 0,
      oldDocument: null,
      newDocument: null,
      dev: true, //flag for development, use sample data
    };
  },
  watch: {
    currentSlide: {
      handler: function () {},
    },
    decisionArr: {
      handler: function () {},
    },
    newDocument: {
      handler: function () {},
    },
  },
  mounted() {
    //callDivil();
    /*  this.$root.$on("arrChanged", (data) => {
      this.decisionArr = data;
      console.log(data);
    });
    this.$root.$on("slideChange", (data) => {
      this.currentSlide = data;
      console.log(data);
    });
    this.$root.$on("gotOldDoc", (data) => {
      this.oldDocument = data;
      console.log(data);
    });
    this.$root.$on("gotNewDoc", (data) => {
      this.newDocument = data;
      console.log(data);
    }); */

    //check for dev mode
    if (this.dev) {
      console.log("Dev Mode is active!");
      console.log(this.json.links, this.json.nodes);
      //console.log(JSON.parse(this.json));

      //compute reaction view
      /*
       * To go through each single change might be cumbersum.
       * With this view we split the network into each reaction.
       * Reactions with changes can than be viewed.
       */
      this.json.nodes.forEach((node) => {
        if (node.id.startsWith("r")) {
          //every reaction ID starts with r
          let reactionNodes = [node];
          let reactionLinks = [];
          //add reaction to Array, include all link and participants
          this.json.links.forEach((link) => {
            if (link.target == node.id || link.source == node.id) {
              reactionLinks.push(link);

              //add other participant of link
              let addNode, addNodeId;
              if (link.target == node.id) {
                addNodeId = link.source;
              } else addNodeId = link.target;

              addNode = this.json.nodes.find((n) => n.id == addNodeId);
              console.log(addNodeId, addNode);
              reactionNodes.push(addNode);
            }
          });

          this.reactionsArr.push({
            nodes: reactionNodes,
            links: reactionLinks,
          });
        }
      });

      console.log(this.reactionsArr);
    }
    let reactionNum = 0;
    callDiVil(this.reactionsArr[reactionNum]);
    /* 
    function getNode(nodeID) {
      console.log(this.json);
      let node = this.json.nodes.find((node) => node.id == nodeID);
      console.log(node);
      return node;
    } */
  },
};
</script>

<style scoped>
#container {
  min-height: 500px;
  background-color: lightgray;
}
</style>