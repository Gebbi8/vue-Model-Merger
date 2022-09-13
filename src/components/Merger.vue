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
    moves: {
        type: Number,
        required: true
    },
    xmlDiff: {
        type: String,
        required: true
    },

  },
  data() {
    return {
      done: false,  
      file1: "",
      file2: "",
      status: "waiting...",
      changesHandled: 0,
      totalChanges: 0,
      moveMap: []
    };
  },
  computed: {},
  methods: {
    computeMerge: function () { 
      let xmlLines = this.$props.xmlDiff.split(/\r?\n/);
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

    console.log(this.moveMap);
    //sort dependencies

    //loop over selection
        //
    },
    regEx: function(line, attribute) {
      var regex = new RegExp(attribute + '="(.*?)"', 'g');
      return regex.exec(line)[1];
    }
  }
};
</script>