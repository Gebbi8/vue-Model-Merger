<template>
<div class="btn-group col-1" role="group" :id="`${changeID}-btngrp`" aria-label="Toggle group">
    <input type="radio" class="btn-check" :name="`${changeID}-btnradio`" :id="`${changeID}-bV1`" autocomplete="off" @click="this.emitDecision(changeID, 0)" :checked="this.dec == 0">
    <label class="btn btn-outline-primary" :for="`${changeID}-bV1`">V1</label>

<!--     <input type="radio" class="btn-check" :name="`${changeID}-btnradio`" :id="`${changeID}-bNew`" autocomplete="off" @click="this.emitDecision(changeID, 2)" disabled :checked="d == 2">
    <label class="btn btn-outline-primary" :for="`${changeID}-bNew`">New</label> -->

    <input type="radio" class="btn-check" :name="`${changeID}-btnradio`" :id="`${changeID}-bV2`" autocomplete="off" @click="this.emitDecision(changeID, 1)" :checked="this.dec == 1">
    <label class="btn btn-outline-primary" :for="`${changeID}-bV2`">V2</label>
</div>
</template>

<script>

export default {
    emits: ['decision'],
    props: {
        changeID: [String, Array],
        decisionArr: Array,
        d: Number
    },
    data(){
        return {
            dec: -1,
        }
    },
    mounted() {
        if(typeof this.changeID == 'string') this.dec = this.d;
    },
    updated() {
        if(typeof this.changeID != 'string'){
            console.debug(this.changeID);
            let check = this.decisionArr[this.changeID[0]]["decision"];
            

            for (let n = 1; n < this.changeID.length; n++) {
                console.debug(this.changeID[n], check);
                if(this.decisionArr[this.changeID[n]]["decision"] != check) check = -1;
            }
            
            this.dec = check;
        }
        else this.dec = this.d;

    },
    methods: {
        emitDecision: function (changeID, dec) {
            if (typeof changeID === 'string') this.$emit('decision', changeID, dec);
            else {
                for (let n = 0; n < changeID.length; n++) {
                    this.$emit('decision', changeID[n], dec);
                }
            }
        }

    }
}
</script>
