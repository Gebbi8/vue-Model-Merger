<template>
    <div class="btn-group col-1" role="group" :id="`${changeID}-btngrp`" aria-label="Toggle group">
        <input type="radio" class="btn-check" :name="`${changeID}-btnradio`" :id="`${changeID}-bV1`" autocomplete="off" @click="this.emitDecision(changeID, 0)" :checked="d == 0">
        <label class="btn btn-outline-primary" :for="`${changeID}-bV1`">V1</label>

        <input type="radio" class="btn-check" :name="`${changeID}-btnradio`" :id="`${changeID}-bNew`" autocomplete="off" @click="this.emitDecision(changeID, 2)" disabled :checked="d == 2">
        <label class="btn btn-outline-primary" :for="`${changeID}-bNew`">New</label>

        <input type="radio" class="btn-check" :name="`${changeID}-btnradio`" :id="`${changeID}-bV2`" autocomplete="off" @click="this.emitDecision(changeID, 1)" :checked="d == 1">
        <label class="btn btn-outline-primary" :for="`${changeID}-bV2`">V2</label>
    </div>
</template>

<script>
export default {
    emits: ['decision'],
    props: {
        changeID: [String,  Array],
        d: Number
    },
    methods: {
        emitDecision: function(changeID, dec){
            if(typeof changeID === String) this.$emit('decision', changeID, dec);
            else {
                for(let n = 0; n < changeID.length; n++){
                    this.$emit('decision', changeID[n], dec);
                } 
            }
        }
        
    }
}
</script>