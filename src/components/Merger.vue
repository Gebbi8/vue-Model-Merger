<template>
    <p>{{status}}</p>
    <button
        v-if="!done"
        ref="compute merge"
        v-on:click="computeMerge"
        type="button"
        class="btn btn-primary btn-lg"
      >
        Compute Merge
      </button>
</template>

<script>
export default {
  name: "Merger",
  props: {
    xmlDiff: {
        type: String,
        required: true
    },
    decisionArr:{
      required: true
    },
    structuredData:{
      type: Array,
      required: true
    },
    v1: {
      type: String,
      required: true
    },
    v2:{
      type: String,
      required: true
    }

  },
  data() {
    return {
      done: false,  
      oldDoc: null,
      newDoc: null,
      xmlDiffDoc: null,
      status: "waiting...",
      changesHandled: 0,
      totalChanges: 0,
      moveMap: []
    };
  },
  methods: {
    computeMerge: function () {

      let xmlLines = this.$props.xmlDiff.split(/\r?\n/);

      let parser = new DOMParser();

      this.oldDoc = parser.parseFromString(this.$props.v1, "application/xml");
      this.newDoc = parser.parseFromString(this.$props.v2, "application/xml");
      this.xmlDiffDoc = parser.parseFromString(this.xmlDiff, "application/xml");
 
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

    let selection;
    console.info(this.$props);
    if(this.$props.decisionArr == null) selection = ["decision array not loaded"];
    else selection = this.$props.decisionArr;

    let insertsToRemove = [];

    //loop over selection, relevant attributes: id, type of change, parent path and/or path, 
    selection.forEach(s => {  //s: changeId, decision (0/1/-1) but should not be -1 at this point, type of change (i,u,d)
      switch(s[2]){
        case "u":
          if(s[1] == 0){
            this.revertUpdate(s[0]);
          };
          break;
        case "d":
          if(s[1] == 0){
            this.restoreDelete(s[0]);
          };
          break;
        case "i":
          if(s[1] == 0){
            insertsToRemove.push(s[0]);
          };
          break;
        default: 
          console.log(s);
          alert("wrong type in selection");
      }
    })

    insertsToRemove.reverse().forEach(id => {  this.removeInsert(id); })

    console.info(this.newDoc);
    this.downloadFile();

  },

  restoreDelete: function(id){                                                 
    let change = this.getXmlLineById(id);
    //possibile element types: node, attribute, text, ...?
    //attribute: needed info: newPath
    let path = this.checkAncestorsForMove(change.getAttribute("oldPath"));

    path = this.getLocalXPath(path);
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
      parent = this.getLocalXPath(parent);
      this.newDoc.evaluate(parent, this.newDoc, null, XPathResult.ANY_TYPE, null).iterateNext().insertAdjacentText('beforeend', change.getAttribute("oldText"));
      return;
    }

    //Node
    if(change.localName == "node"){
      let oldPath = change.getAttribute("oldPath");
      
      /* --- skips math changes
         --- handling mathematical changes atomicly will most likely make no sense
         --- also, it breaks the insertAdjacentElement method, maybe because it would have to handle pure tag nodes
      --- */
      if(oldPath.includes("/math")) return; 
     
      let appendPath = change.getAttribute("oldParent");
      appendPath = this.checkAncestorsForMove(appendPath);

      oldPath = this.getLocalXPath(oldPath);
      appendPath = this.getLocalXPath(appendPath);

      let node = this.oldDoc.evaluate(oldPath, this.oldDoc, null, XPathResult.ANY_TYPE, null).iterateNext();
      this.newDoc.evaluate(appendPath, this.newDoc, null,XPathResult.ANY_TYPE, null).iterateNext().insertAdjacentElement('beforeend', node);
      return;
    }
    console.info("===> Delete of ", change.localName, " not hanlded!");


  },

  removeInsert: function(id){
    let change = this.getXmlLineById(id);
    let path = change.getAttribute("newPath");

    /* --- skips math changes
        --- handling mathematical changes atomicly will most likely make no sense
        --- also, it breaks the insertAdjacentElement method, maybe because it would have to handle pure tag nodes
    --- */

    if(path.includes("/math")) return; 

    path = this.getLocalXPath(path);

    if(change.localName == "node" || change.localName == "text"){ //if a a delete was readded on the same node or text the iterateNext() + remove() will remove this correct element
      this.newDoc.evaluate(path,this.newDoc, null, XPathResult.ANY_TYPE, null).iterateNext().remove();
      return;
    }

    if(change.localName == "text"){
      this.newDoc.evaluate(path,this.newDoc, null, XPathResult.ANY_TYPE, null).iterateNext().remove();
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
      console.error("revert update ist not on an attribute", change);
    }

    let path = this.getLocalXPath(change.getAttribute("newPath"));
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

  getLocalXPath: function(path) {
    let pathArray;
    let returnPath = "";

    path = path.substr(1);
    pathArray = path.split("/");
    for (let j = 0; j < pathArray.length; j++) {
      let splitArr = pathArray[j].split("[");

        if (splitArr[0] == "text()") {
            //add special cases
            returnPath += "/" + splitArr[0] + "[" + splitArr[1];
        } else if (splitArr[1] == null) {//last element not specified -> path to group
            returnPath += "/*[local-name()='" + splitArr[0] + "']";
        } else returnPath += "/*[local-name()='" + splitArr[0] + "'][" + splitArr[1];
    }
    //returnPath += '/*:' + pathArray[pathArray.length-1];
    return returnPath;
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