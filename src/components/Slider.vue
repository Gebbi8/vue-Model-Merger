<template >
    <div class="btn btn-primary col-1 arrow-btn" @click="slideNum--">
        <div class="arrow mirror"></div> 
    </div>


        <template v-for="(node, index) in data" :key="index">
            <div class="card col-10" v-if="slideNum == index">
                <div class="card-body row g-0 p-0">
                    <div class="col-8 bivesGraph" :id="`bivesGraph-${index}`"></div>
                    <div class="col-4 changeList" :id="`changeList-${index}`"></div>
                    <decision-btn :changeID="this.changeIds" @decision="this.emitDecision">
                        
                    </decision-btn>
                    {{ changeIds }}
                </div>
            
            <!--           <span> Species focus view, just like reaction view, but the subgraph is computed for d=2
                    {{ this.currentSlide }}
                </span> -->
            </div>
        </template>
    <div class="btn btn-primary col-1 arrow-btn" @click="slideNum++">
        <div class="arrow"></div>
    </div>
</template>

<script>
import * as divilApi from "../../DiVil/javascriptAndCss/init";
import DecisionBtn from './DecisionBtn.vue';


export default {
  components: { DecisionBtn },
    name: "graph-slider",
    props: {
        data: Array,
        structuredData: Object,
        decisionArr: Array,
        slideNumber: Number
    },
    emits: ['slideNum', 'decision'],
    data(){
        return {
            slideNum: this.slideNumber,
            changeIds: [],
            nextSlide: true
        }
    },
    methods: {
        getSlide: function(){

            
        
            let currentSlide = this.data[this.slideNum];

        let c = this.structuredData[currentSlide.centralNode];
        console.debug(c);
        
        this.changeIds = this.structuredData[currentSlide.centralNode].ids;


        let container = "bivesGraph-" + this.slideNum;
        let changeList = "changeList-" + this.slideNum;
      


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
    
        },

        emitDecision: function(changeID, dec){
            this.$emit('decision', changeID, dec);
        }
    },
    mounted (){
      this.getSlide();  
      this.nextSlide = false;
    },
    updated(){
        if(this.nextSlide) this.getSlide();
        this.nextSlide = false;
     
    },
    watch: {
        slideNum: {
            handler: function () {
                let length= this.data.length;
                if (this.slideNum == -1)
                    this.slideNum = length - 1;
                if (this.slideNum >= length)
                    this.slideNum = 0;
                    this.nextSlide = true;
            }
        }
    },
    beforeUnmount () {
        this.$emit("slideNum", this.slideNum);
        divilApi.stopD3ForceOfDivil("bivesGraph-" + this.slideNum);
       
      }    
}
</script>

<style scoped>
.mirror{
/*     font-size: 30px;
 */
    -webkit-transform: matrix(-1, 0, 0, 1, 0, 0);
    -moz-transform: matrix(-1, 0, 0, 1, 0, 0);
    -o-transform: matrix(-1, 0, 0, 1, 0, 0);
    transform: matrix(-1, 0, 0, 1, 0, 0);
}

.arrow{
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
    background-repeat:no-repeat!important;
    background-position: center;
    height: 100%;
}

.arrow-btn{
    margin-bottom: 0;
}
</style>