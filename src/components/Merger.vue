<template>
    <p>{{status}}</p>
    <button
        v-if="this.progress == 100"
        ref="compute merge"
        v-on:click="computeMerge"
        type="button"
        class="btn btn-primary btn-lg"
      >
        Compute Merge
      </button>
</template>

<script>
import { useGetLocalXPath } from '../composables/xmlInteraction';
export default {
  name: "Merger",
  props: {
    xmlDiff: {
        //type: String,
        required: true
    },
    decisionArr:{
      type: Object,
      required: true
    },
    structuredData:{
      type: Array,
      required: true
    },
    oldDoc: {
      //type: String,
      required: true
    },
    newDoc:{
      //type: String,
      required: true
    },
    progress: {
      type: Number,
      required: true
    }    

  },
  data() {
    return {
      
      xmlDiffDoc: null,
      status: "waiting...",
      changesHandled: 0,
      totalChanges: 0,
      moveMap: []
    };
  },
  watch: {

  },
  methods: {
    computeMerge: function () {

      let xmlLines = this.$props.xmlDiff.split(/\r?\n/);

      let parser = new DOMParser();

      //this.oldDoc = parser.parseFromString(this.v1, "text/xml");
      console.debug(this.oldDoc);
      //this.newDoc = parser.parseFromString(this.v2, "text/xml");
      this.xmlDiffDoc = parser.parseFromString(this.xmlDiff, "text/xml");
 
      let moveFlag;  
      //create move map -- copy from DiVils xmlParser
    
     xmlLines.forEach(line => {
        if (line.includes("<move>")) {
            moveFlag = 1;
            return;
        }
        if (line.includes("</move>")) {
            moveFlag = 0;
            return;
        }
        if (moveFlag) {
            // console.log(line)
            let oldPath = this.regEx(line, "oldPath");
            let newPath = this.regEx(line, "newPath");
            this.moveMap[oldPath] = newPath;
        }
    })

 
    
    if(this.decisionArr == null) alert("decision array not loaded");
 

    let insertsToRemove = [];

    console.log(this.decisionArr);
    //loop over selection, relevant attributes: id, type of change, parent path and/or path, 
    for (const [id, a] of Object.entries(this.decisionArr)) {  //s: changeId, decision (0/1/-1) but should not be -1 at this point, type of change (i,u,d)
      
      
      switch(a.type){
        case "u":
          if(a.decision == 0){
            this.revertUpdate(id);
          };
          break;
        case "d":
          if(a.decision == 0){
            this.restoreDelete(id);
          };
          break;
        case "i":
          if(a.decision == 0){
            insertsToRemove.push(id);
          };
          break;
        default: 
          console.log(id, a.decision, a.type);
          alert("wrong value in change type");
      }
    }

    insertsToRemove.reverse().forEach(id => {  this.removeInsert(id); })

    console.info(this.newDoc);
    this.downloadFile();

  },

  restoreDelete: function(id){                                                 
    let change = this.getXmlLineById(id);
    //possibile element types: node, attribute, text, ...?
    //attribute: needed info: newPath
    let path = this.checkAncestorsForMove(change.getAttribute("oldPath"));

    path = useGetLocalXPath(path);
        //check for /math and/or /kineticLaw

    //Attribute
    if(change.localName == "attribute"){
      let name = change.getAttribute("name");
      let value = change.getAttribute("oldValue");
      this.newDoc.evaluate(path, this.newDoc, null, XPathResult.ANY_TYPE, null).iterateNext().setAttribute(name, value);
      return;
    }

    //Text
    if(change.localName == "text"){
      let parent = change.getAttribute("oldParent");
      parent = this.checkAncestorsForMove(parent);
      parent = useGetLocalXPath(parent);
      this.newDoc.evaluate(parent, this.newDoc, null, XPathResult.ANY_TYPE, null).iterateNext().insertAdjacentText('beforeend', change.getAttribute("oldText"));
      return;
    }

    //Node
    if(change.localName == "node"){
      let oldPath = change.getAttribute("oldPath");
      
      /* --- skips math changes
         --- handling mathematical changes atomicly will most likely make no sense
         --- also, it breaks the insertAdjacentElement method
      --- */

      //if(oldPath.includes("/math")) return; 
     
      let appendPath = change.getAttribute("oldParent");
      appendPath = this.checkAncestorsForMove(appendPath);

      oldPath = useGetLocalXPath(oldPath);
      appendPath = useGetLocalXPath(appendPath);

      let node = this.oldDoc.evaluate(oldPath, this.oldDoc, null, XPathResult.ANY_TYPE, null).iterateNext();
      
      console.debug(appendPath, this.newDoc, node);
      if(this.newDoc.evaluate(appendPath, this.newDoc, null,XPathResult.ANY_TYPE, null).iterateNext() == null){ //insert parent list
        
        

        let parentNode = this.oldDoc.evaluate(appendPath, this.oldDoc, null, XPathResult.ANY_TYPE, null).iterateNext();
        let parentPath = appendPath.substring(0, appendPath.lastIndexOf('/'));
        
        console.debug(parentPath, parentNode);
        this.newDoc.evaluate(parentPath, this.newDoc, null,XPathResult.ANY_TYPE, null).iterateNext().insertAdjacentElement('beforeend', parentNode.cloneNode(false));
      }
      
      this.newDoc.evaluate(appendPath, this.newDoc, null,XPathResult.ANY_TYPE, null).iterateNext().insertAdjacentElement('beforeend', node.cloneNode(true));
      if(oldPath.includes("math")) alert("!");
      return;
    }
    console.error("===> Delete of ", change.localName, " not handled!");


  },

  removeInsert: function(id){
    let change = this.getXmlLineById(id);
    //let path = change.getAttribute("newPath");

    /* --- skips math changes
        --- handling mathematical changes atomicly will most likely make no sense
        --- also, it breaks the insertAdjacentElement method, maybe because it would have to handle pure tag nodes
    --- */


    if(change.attributes.newPath.value.includes("/math")) {
      
      //if(change.localName == "node")
      	
      //return; 
    } 

    console.debug(change);
     
    //path = useGetLocalXPath(path);

    if(change.localName == "node" || change.localName == "text"){ //if a a delete was readded on the same node or text the iterateNext() + remove() will remove this correct element
      let path = useGetLocalXPath(change.attributes.newPath.value);
      console.debug(path);
      //alert("?");
      this.newDoc.evaluate(path, this.newDoc, null, XPathResult.ANY_TYPE, null).iterateNext().remove();
      console.debug("check");
      return;
    }

    if(change.localName == "attribute"){
      console.info(path);
      this.newDoc.evaluate(path,this.newDoc, null, XPathResult.ANY_TYPE, null).iterateNext().removeAttribute(change.getAttribute("name"));
      return;
    }

    if(change.localName != "node" || change.localName != "text") console.error("Unhadled remove insert: ", change.localName, change);

  },

  revertUpdate: function(id){
    let change = this.getXmlLineById(id);
    if(change.localName != "attribute") {           //dev check for type of elements that are affected
      console.error("revert update is not on an attribute", change);
    }

    let path = useGetLocalXPath(change.getAttribute("newPath"));
    let name = change.getAttribute("name");
    let value = change.getAttribute("oldValue");

    //set Attribute to old value
    this.newDoc.evaluate(path, this.newDoc, null, XPathResult.ANY_TYPE, null).iterateNext().setAttribute(name, value);
  },

  checkAncestorsForMove: function(oldPath){
    let trace = oldPath;
    if(this.moveMap[trace]) return this.moveMap[trace];
    while(trace.includes("/")){
      let lastSlash = trace.lastIndexOf('/');
      let end = trace.substring(lastSlash);

      trace = trace.substring(0, lastSlash);
      if(this.moveMap[trace]){
        return this.moveMap[trace] + end;
      } 
    }
    return oldPath;
  },

  regEx: function(line, attribute) {
    var regex = new RegExp(attribute + '="(.*?)"', 'g');
    return regex.exec(line)[1];
  },

  getXmlLineById: function(id){
    let element = this.xmlDiffDoc.getElementById(id);
    //console.info("element:", element);
    return element;
  },



  downloadFile: function () {
                //download
                let file = this.newDoc;
  
                const blob = new Blob([file.documentElement.outerHTML], { type: 'text/xml' });
                const url = URL.createObjectURL(blob); 

                const download = document.createElement('a');
                download.setAttribute("href", url);
                download.setAttribute("download", "merge.xml");
                download.setAttribute("display", "none");

                document.body.appendChild(download);
                download.click();
                download.remove();

            }
}
};
</script>