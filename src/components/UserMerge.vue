<template>
  <div id="user-merge">
    <div id="generalInfo">
      <h3>Semi-automatic Merging</h3>
      <h4>Still under development</h4>
      <p>
        You are in the semi-automatic merging mode. This means you can
        cherry-pick the changes you want to apply. We provide different views on
        the changes:
      </p>
    </div>

    <template v-for="(reaction, index) in reactionsArr" :key="index">
      <div class="card text-center" v-if="currentSlide == index">
        <div class="card-header">Featured</div>
        <div class="card-body row g-0 p-0">
          <div class="col-8 bivesGraph" :id="`bivesGraph-${index}`"></div>
          <div class="col-4 changeList" :id="`changeList-${index}`">
            List changes here instead of popup?
          </div>
        </div>
        <div class="card-footer text-muted">Show math here?</div>
      </div>
    </template>

    <!-- Slide buttons -->
    <div class="btn btn-primary" @click="this.currentSlide++">Up</div>
    <div class="btn btn-primary" @click="this.currentSlide--">Down</div>

    <!--     <Carousel
      :decArr="decisionArr"
      @arrChanged="decisionArr = $event"
      @slideChange="currentSlide = $ent"
      @gotOldDoc="oldDocument = $event"
      @gotNewDoc="newDocument = $event"
    /> -->
<!--     <Selection
      v-if="!hide"
      :decArr="decisionArr"
      :slideChng="currentSlide"
      :oldDoc="oldDocument"
      :newDoc="newDocument"
    /> -->
  </div>
  <div id="devOutput" v-if="dev">
    <h3>Dev mode is active!</h3>
  </div>
</template>


<script>
import axios from 'axios';
import { callDiVil } from "../../DiVil/javascriptAndCss/init";
/* import Carousel from "./Carousel.vue"; */
//import Selection from "./Selection.vue";
//import dev data
/* import devDataJson from "/dev/navicenta/sbgnJson.json";
import devDataXmlDiff from "raw-loader!/dev/navicenta/xmlDiff.xml";
import version1 from "raw-loader!/dev/navicenta/version1.xml";
import version2 from "raw-loader!/dev/navicenta/version2.xml"; */
import devDataJson from "/dev/dupreez_6-7/sbgnJson.json";
/* import devDataXmlDiff from "/dev/dupreez_6-7/xmlDiff.xml";
import version1 from "/dev/dupreez_6-7/dupreez6.xml";
import version2 from "/dev/dupreez_6-7/dupreez7.xml"; */

export default {
  name: "user-merge",
  components: {
    /*     Carousel,*/
    /* Selection, */
  },
  data() {
    return {
      /* START - dev vars */
      json: devDataJson,
      xmlDiff: null,
      updates: 0,
      inserts: 0,
      deletes: 0,
      moves: 0,
      v1: null,
      v2: null,
      hide: true,
      /* END */
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
      handler: function () {
        if (this.currentSlide == -1)
          this.currentSlide = this.reactionsArr.length - 1;
        if (this.currentSlide >= this.reactionsArr.length)
          this.currentSlide = 0;
      },
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
       axios.get('/dev/dupreez_6-7/xmlDiff.xml')
        .then(res => this.xmlDiff = res.data )
        .catch(err => console.log(err))
      //this.xmlDiff = require("/dev/dupreez_6-7/xmlDiff.xml");
      console.log(this.xmlDiff);
/* import version1 from "/dev/dupreez_6-7/dupreez6.xml";
import version2 from "/dev/dupreez_6-7/dupreez7.xml"; */
      console.log("Dev Mode is active!");
      console.log(this.json.links, this.json.nodes, this.xmlDiff);
      //compute change stats
      console.log(this.xmlDiff, typeof this.xmlDiff);
      //let xmlLines: string[] ;
      //console.log(JSON.parse(this.json));
      //compute reaction view
      /*
       * To go through each single change might be cumbersum.
       * With this view we split the network into each reaction.
       * Reactions with changes can than be viewed.
       */
      this.json.nodes.forEach((node) => {
        if (node.id.startsWith("r") && node.bivesChange != "nothing") {
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
            bivesChange: node.bivesChange,
            nodes: reactionNodes,
            links: reactionLinks,
          });
        }
      });
      console.log("reactionsArr: ", this.reactionsArr);
    }
    /* 
    function getNode(nodeID) {
      console.log(this.json);
      let node = this.json.nodes.find((node) => node.id == nodeID);
      console.log(node);
      return node;
    } */
  },
  updated() {
    callDiVil(
      this.reactionsArr[this.currentSlide],
      this.xmlDiff,
      this.v1,
      this.v2,
      "bivesGraph-" + this.currentSlide
    );
  },
};
</script>

<style>
.bivesGraph {
  min-height: 300px;
  background-color: lightgray;
}
/*bives-colors*/
.delete-color {
  color: #d66a56;
}
.insert-color {
  color: #76d6af;
}
.move-color {
  color: #8e67d6;
}
.update-color {
  color: #d6d287;
}
</style>