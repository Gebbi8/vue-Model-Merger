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
  props : {
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
    v2:{
      type: String,
      required: true
    }

  },
  data() {
    return {
      done: false,  
      file1: "",
      file2: "",
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

    console.info(this.moveMap, selection);


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
            this.removeInsert(s[0]);
          };
          break;
        default: 
          console.log(s);
          alert("wrong type in selection");
      }
    })

  },
  restoreDelete: function(id){
    let line = this.getXmlLineById(id);
     
    console.info("restore delete");
  },
  removeInsert: function(id){
    console.info("remove Insert");

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
  checkAncestorsForMove: function(){

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
  }
}
};
</script>