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
      <div class="card" v-if="currentSlide == index">
        <div class="card-header text-center">Featured</div>
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
    <merger :xml-diff="xmlDiff" :decision-arr="decisionArr" :v1="v1" :v2="v2"></merger>
  </div>
</template>


<script>
import Merger from "./Merger.vue";
import axios from 'axios';
import * as divilApi from "../../DiVil/javascriptAndCss/init";
/* import Carousel from "./Carousel.vue"; */
//import Selection from "./Selection.vue";
//import dev data
/* import devDataJson from "/dev/navicenta/sbgnJson.json";
import devDataXmlDiff from "raw-loader!/dev/navicenta/xmlDiff.xml";
import version1 from "raw-loader!/dev/navicenta/version1.xml";
import version2 from "raw-loader!/dev/navicenta/version2.xml"; */
import devDataJson from "/dev/dupreez_6-7/sbgnJson.json";
import testArr from "/dev/dupreez_6-7/decisionArray.js";
/* import devDataXmlDiff from "/dev/dupreez_6-7/xmlDiff.xml";
import version1 from "/dev/dupreez_6-7/dupreez6.xml";
import version2 from "/dev/dupreez_6-7/dupreez7.xml"; */

export default {
  name: "user-merge",
  components: {
    Merger
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
      decisionArr: testArr, // [],
      reactionsArr: [],
      structuredData: null,
      currentSlide: 0,
      oldDocument: null,
      newDocument: null,
      dev: true, //flag for development, use sample data
    };
  },
  methods: {
    getId: function (line){
          var regex = new RegExp('id="(.*?)"', 'g');
          return regex.exec(line)[1];
    }
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
/*     decisionArr: {
      handler: function () { },
    }, */
    newDocument: {
      handler: function () { },
    },
  },
  async mounted() {
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
      const promiseDiff = await axios.get('/dev/dupreez_6-7/xmlDiff.xml');
      const promiseV1 = await axios.get('/dev/dupreez_6-7/dupreez6.xml');
      const promiseV2 = await axios.get('/dev/dupreez_6-7/dupreez7.xml');

      Promise.allSettled([promiseV1, promiseV2, promiseDiff])
        .then((responses) => {
          console.log(responses);
          //alert("?");
          this.v1 = responses[0].value.data;
          this.v2 = responses[1].value.data;
          this.xmlDiff = responses[2].value.data;

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
                  //console.log(addNodeId, addNode);
                  reactionNodes.push(addNode);
                }
              });
              this.reactionsArr.push({
                centralNode: node.path,
                bivesChange: node.bivesChange,
                nodes: reactionNodes,
                links: reactionLinks,
              });
            }
          });

          //init computed divil data
          this.structuredData = divilApi.initDivil(this.xmlDiff, this.v1, this.v2);

          console.info(this.decisionArr);

          /**********  Creation of Decision Array, works fine currently not used for development   
          //create decision array
          //filter triggered changes and moves
          let type = '-';
          let xmlLines = this.xmlDiff.split(/\r?\n/);
          xmlLines.every(line => {
            if(line.includes("<move>")) return false;
            if(line.includes("<insert>")){
              type = 'i';
              return true;
            }
            if(line.includes("<delete>")){
              type = 'd';
              return true;
            }
            if(line.includes("<update>")){
              type = 'u';
              return true;
            }

            if(line.includes("id=") && !line.includes("triggeredBy=")){
              console.info("found lines");
              this.decisionArr.push([this.getId(line), -1, type]);
            }
            return true;
          })

          if(this.decisionArr[0][0] == "bivesPatch") this.decisionArr.shift();
          console.info(this.decisionArr);
          *********************************/
        })
        .catch(error => {
          console.error(error.message)
        });



      console.log("Dev Mode is active!");
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
    console.log(this.v1, this.v2);
    divilApi.callDiVil(
      this.reactionsArr[this.currentSlide],
      this.xmlDiff,
      this.v1,
      this.v2,
      "bivesGraph-" + this.currentSlide,
      "changeList-" + this.currentSlide,
      this.structuredData
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