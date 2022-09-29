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

    <div class="row justify-content-evenly">
      <input type="radio" class="btn-check col-2" name="options" id="option1" autocomplete="off" checked>
      <label @click="view = 'model'" class="btn btn-outline-primary col-2" for="option1">Model</label>

      <input type="radio" class="btn-check col-2" name="options" id="option2" autocomplete="off">
      <label @click="view = 'species';" class="btn btn-outline-primary col-2" for="option2">Species</label>

      <input type="radio" class="btn-check col-2" name="options" id="option3" autocomplete="off"  >
      <label @click="view = 'reactions'" class="btn btn-outline-primary col-2" for="option3">Reactions</label>

      <input type="radio" class="btn-check col-2" name="options" id="option4" autocomplete="off" disabled>
      <label @click="view = 'parameters'" class="btn btn-outline-primary col-2" for="option4">Parameters</label>
    </div>

    <div  v-if="view == 'model'">
      <p>Looking at the changes in model describtion, name, ...</p>
        <ul class="list-group">
          <template v-for="(element, index) in modelArr" :key="index">
          <li v-if="element.id != 'listOfSpecies' && element.id != 'listOfReactions'" class="list-group-item" :id="`${element.id}`">
            <h5>{{ element.id }}</h5>
            <ul class="list-group">
              <template v-for="(el, key) in element.attr" :key="key">
                <li class="list-group-item d-flex justify-content-evenly row" >
                  <div class="col-3"><b>{{ key }}</b>:</div>
                  <div v-if="el.changeID" class="col-3">{{ el.oldValue }}</div>
                  <div v-if="el.changeID" class="col-3">{{ el.newValue }}</div>
                  <div v-else class="col-9">{{ el }} </div>

                    <div v-if="el.changeID" class="btn-group col-3" role="group" aria-label="Basic radio toggle button group">
                      <input type="radio" class="btn-check" name="btnradio" :id="`${el.changeID}-bV1`" autocomplete="off" @click="this.updateDecision(el.changeID, 0)">
                      <label class="btn btn-outline-primary" :for="`${el.changeID}-bV1`">V1</label>

                      <input type="radio" class="btn-check" name="btnradio" :id="`${el.changeID}-bNew`" autocomplete="off" @click="this.updateDecision(el.changeID, 2)" disabled>
                      <label class="btn btn-outline-primary" :for="`${el.changeID}-bNew`">New</label>

                      <input type="radio" class="btn-check" name="btnradio" :id="`${el.changeID}-bV2`" autocomplete="off" @click="this.updateDecision(el.changeID, 1)">
                      <label class="btn btn-outline-primary" :for="`${el.changeID}-bV2`">V2</label>
                    </div>
                </li>
              </template>
            </ul>
          </li>
        </template>
      </ul>
    </div>


    <div v-if="view == 'species'">
      <template v-for="(species, index) in speciesArr" :key="index">
        <div class="card" v-if="currentSlide == index">
          <div class="card-header text-center">Featured</div>
          <div class="card-body row g-0 p-0">
            <div class="col-8 bivesGraph" :id="`bivesGraphSpecies-${index}`"></div>
            <div class="col-4 changeList" :id="`changeListSpecies-${index}`">
              Species focus view, just like reaction view, but the subgraph is computed for d=2
              {{ this.currentSlide }}
            </div>
          </div>
          <div class="card-footer text-muted">Show math here?</div>
        </div>
      </template>
    </div>


    <div v-if="view == 'reactions'">
      <template v-for="(reaction, index) in reactionsArr" :key="index">
        <div class="card" v-if="currentSlide == index">
          <div class="card-header text-center">Featured</div>
          <div class="card-body row g-0 p-0">
            <div class="col-8 bivesGraph" :id="`bivesGraphReaction-${index}`"></div>
            <div class="col-4 changeList" :id="`changeListReaction-${index}`">
              List changes here instead of popup?
            </div>
          </div>
          <div class="card-footer text-muted">Show math here?</div>
        </div>
      </template>
    </div>

    <!-- Slide buttons -->
    <div class="btn btn-primary" @click="this.currentSlide++">Up</div>
    <div class="btn btn-primary" @click="this.currentSlide--">Down</div>


  </div>
  <div id="devOutput" v-if="dev">
    <h3>Dev mode is active!</h3>
    <p> The Merg is produced with local files for versions, diff and decisionArray. So it is not coupled with the slider. </p>
    <merger :xml-diff="xmlDiff" :decision-arr="decisionArr" :v1="v1" :v2="v2"></merger>
  </div>
</template>


<script>
import Merger from "./Merger.vue";
import axios from 'axios';
import * as divilApi from "../../DiVil/javascriptAndCss/init";
 //import * as mathJax from "../../3rdPartyJS/MathJax-2.7.7/MathJax";

import devDataJson from "/dev/dupreez_6-7/sbgnJson.json";
import testArr from "/dev/dupreez_6-7/decisionArray.js";
import modelTestArr from "/dev/dupreez_6-7/decisionArray.js";
import { list } from "postcss";


export default {
  name: "user-merge",
  components: {
    Merger

  },
  data() {
    return {
      json: devDataJson,
      view: "model",
      v1: null,
      v2: null,
      hide: true,
      modelArr: [],
      listsArr: [],
      decisionArr: {}, //testArr
      reactionsArr: [],
      speciesArr: [],
      structuredData: null,
      currentSlide: 0,
      oldDocument: null,
      newDocument: null, //should also be given to subcomponent to avoid parsing it twice!
      dev: true, //flag for development, use sample data
    };
  },
  methods: {
    getId: function (line){
          var regex = new RegExp('id="(.*?)"', 'g');
          return regex.exec(line)[1];
    },
    getModelChanges: function () {
      let modelChanges = [];
      let xmlLines = this.xmlDiff.split(/\r?\n/);
      let type;
      xmlLines.forEach(line => {
        if(line.includes("triggeredBy="));
        else if(line.includes("<move>")) type = null;
        else if(line.includes("<insert>")){
              type = 'i';
            }
        else if(line.includes("<delete>")){
              type = 'd';
            }
        else if(line.includes("<update>")){
              type = 'u';
            }
        else if(line.includes('/sbml[1]/model[1]\"') && type != null){
          let parser = new DOMParser();
          line = parser.parseFromString(line, "application/xml");
          let p = {};
          p["type"] = type;
          p["target"] = line.firstChild.localName;
          
          let attr = line.firstChild.attributes;

          for(let i = 0; i < attr.length; i++){
            p[attr[i].localName] = attr[i].value;
          }

          modelChanges.push(p);
        }
        else if(!line.includes("/listOfSpecies") && !line.includes("/listOfReactions") && line.includes("/listOf") && type != null){
          console.debug(type, line); //***************!!!!!!!!!!!!!!!!!!!!!!!!!!!! */

        }

      });
      
      return modelChanges;
    },

    getModelAttr: function (){

      // gets Attributes on SBML tag
      let attr = [];
      let modelAttr = [];
      attr = this.getAllNodeAttr(this.newDocument.firstChild);
      modelAttr.push({'id': 'sbmlAttr', 'attr': attr});

      //get all Attributes of model
      attr = this.getAllNodeAttr(this.newDocument.firstChild.children.item('model'));
      modelAttr.push({'id': 'modelAttr', 'attr': attr});

      //get all childrenTags of <model>
      let lists = this.newDocument.firstChild.children.item(0).children;
      for(let i = 0; i < lists.length; i++){
        let p = {};
        p["class"] = "list";
        p["id"] = lists[i].localName;
        
        modelAttr.push(p);
      } 

      return modelAttr;
    },

    combineModelAttrWithChanges: function(){
      let changes = this.getModelChanges();
      let attributes = this.getModelAttr();


      
      changes.forEach((c) => {
        if(c.target == "attribute"){
          
          let el = {};
          let target;
          if(c.newPath == "/sbml[1]/model[1]")  target = attributes.find(a => a.id === "modelAttr");
          else if(c.newPath == "/sbml[1]")  target = attributes.find(a => a.id === "sbmlAttr");

          if(c.type == "u"){
            el["changeID"] = c.id;
            el["changeType"] = "u";
            el["oldValue"] = c.oldValue;
            el["newValue"] = c.newValue;
            
            target.attr[c.name] = el;

          } else if(c.type == "i"){
            el["changeID"] = c.id;
            el["changeType"] = "i";
            el["newValue"] = c.newValue;
            
            target.attr[c.name] = el;

          } else if(c.type == "d"){
            el["changeID"] = c.id;
            el["changeType"] = "d";
            el["oldValue"] = c.oldValue;
            
            target.attr[c.name] = el;
          }
        }
        else if(c.target == "node"){
          
          console.debug(c);
        }
        else alert("can't handle " + c.target + " change on model level");
      })

      this.modelArr = attributes;
      console.debug(this.modelArr);
      
    },

    computeListChanges: function(){

    },

    getAllNodeAttr: function(node) {
      let attrMap = node.attributes;
      let p = {};
      for(let i = 0; i < attrMap.length; i++){
        p[attrMap[i].localName] = attrMap[i].value;
      }

      return p;
    },

    updateDecision: function(cID, d) {
      this.decisionArr[cID]['decision'] = d;

      console.debug(this.decisionArr);
    }
  },
  watch: {
    view:{
      handler: function(){
        divilApi.stopD3ForceOfDivil("bivesGraph-" + this.currentSlide);
      }
    },  
    currentSlide: {
      handler: function () {
        let length;
        if(this.view == 'species') length = this.speciesArr.length;
        else length = this.reactionsArr.length;
        if (this.currentSlide == -1)
          this.currentSlide = length - 1;
        if (this.currentSlide >= length)
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

          let parser = new DOMParser();
          this.newDocument = parser.parseFromString(this.v2, "application/xml");
          
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
                  
                  //console.log(addNodeId, addNode);
                  if(!reactionNodes.some(rN => rN.id === addNodeId)){
                    console.info(reactionNodes, addNodeId);
                    //alert("check nodes");
                    addNode = this.json.nodes.find((n) => n.id == addNodeId);
                    reactionNodes.push(addNode);
                  }
                }
              });
              this.reactionsArr.push({
                centralNode: node.path,
                bivesChange: node.bivesChange,
                nodes: reactionNodes,
                links: reactionLinks,
              });
            } else if (node.id.startsWith("s") && node.bivesChange != "nothing"){ //want the subnetwor for the distance 2
              let speciesNodes = [node];
              let speciesLinks = [];
              //add reaction to Array, include all link and participants
              this.json.links.forEach((link) => {
                if (link.target == node.id || link.source == node.id) {
                  speciesLinks.push(link);
                  //add other participant of link
                  let addNode, addNodeId;
                  if (link.target == node.id) {
                    addNodeId = link.source;
                  } else addNodeId = link.target;
                  
                  if(!speciesNodes.some(sN => sN.id === addNodeId)){
                    addNode = this.json.nodes.find((n) => n.id == addNodeId);
                    speciesNodes.push(addNode);
                  }
                   this.json.links.forEach((link2) => {

                    if(link2.target == addNodeId || link2.source == addNodeId){

                    
                    
                    

                    //alert("before before");

                    if(!speciesLinks.some(sL => sL.path === link2.path))
                    {
                      speciesLinks.push(link2);

                      let addNode2, addNodeId2;
                      if(link2.target == addNodeId) addNodeId2 = link2.source;
                      else addNodeId2 = link2.target;

                      if(!speciesNodes.some(sN => sN.id === addNodeId2))
                      {
                        //alert("check nodes"); 
                        addNode2 = this.json.nodes.find((n) => n.id === addNodeId2);
                        speciesNodes.push(addNode2);
                       }
                      //addNode2 = this.json.nodes.find((n) => n.id == addNodeId2);
                      //speciesNodes.push(addNode2);*/
                    }
                  }
                  }) 
                }
              });
       
              this.speciesArr.push({
                centralNode: node.path,
                bivesChange: node.bivesChange,
                nodes: speciesNodes,
                links: speciesLinks,
              });
            }
          });


          //init computed divil data
          this.structuredData = divilApi.initDivil(this.xmlDiff, this.v1, this.v2);

          //console.info(this.decisionArr);
          console.info(this.speciesArr);
          console.info(this.reactionsArr);

          /**********  Creation of Decision Array, works fine currently not used for development   */
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

            if(line.includes("id=") && !line.includes("triggeredBy=") && !line.includes("bivesPatch")){
              console.info("found lines");
              let a = {};
              let id = this.getId(line);
         
              
              a["decision"] = -1;
              a["type"] = type;


              this.decisionArr[id] = a;
            }
            return true;
          })

          /*********************************/
          this.combineModelAttrWithChanges();

        })
        .catch(error => {
          console.error(error.message);
        });


      console.log("Dev Mode is active!");
      console.log("reactionsArr: ", this.reactionsArr);
    }
  },
  updated() {
    if(this.view === "reactions" || this.view === "species"){
      let currentSlide;
      let container;
      let changeList;
      if(this.view == 'reactions'){
        currentSlide = this.reactionsArr[this.currentSlide];
        container = "bivesGraphReaction-" + this.currentSlide;
        changeList = "changeListReaction-" + this.currentSlide;
      } else if(this.view == 'species'){
        currentSlide = this.speciesArr[this.currentSlide];  //OBACHT: this.currentSlide is atm the some for both views
        container = "bivesGraphSpecies-" + this.currentSlide;
        changeList = "changeListSpecies-" + this.currentSlide;
      }
    


      console.info(currentSlide);

      divilApi.callDiVil(
        currentSlide,
        this.xmlDiff,
        this.v1,
        this.v2,
        container,
        changeList,
        this.structuredData
      );
      MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
    }
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