<template>
<div class="btn-group col-1" role="group" :id="btngrp" aria-label="Toggle group">
    <input type="radio" class="btn-check" :name="btnradio" :id="bV1" autocomplete="off" @click="this.emitDecision(0)" :checked="this.decision == 0">
    <label class="btn btn-outline-primary" :for="`${changeID}-bV1`">V1</label>

    <!--     <input type="radio" class="btn-check" :name="`${changeID}-btnradio`" :id="`${changeID}-bNew`" autocomplete="off" @click="this.emitDecision(changeID, 2)" disabled :checked="d == 2">
        <label class="btn btn-outline-primary" :for="`${changeID}-bNew`">New</label> -->

    <input type="radio" class="btn-check" :name="btnradio" :id="bV2" autocomplete="off" @click="this.emitDecision(1)" :checked="this.decision == 1">
    <label class="btn btn-outline-primary" :for="`bV2`">V2</label>
</div>
</template>

    
<script>
export default {
    emits: ['decision'],
    props: {
        decisionArr: Object,
        list: Array,
    },
    data() {
        return{
            decision: -1
        }
    },
    mounted() {
        console.debug(this.list);
        alert("asd");
        let d = this.list[0];
        for(let n = 0; n < list.length; n++){
            console.debug(list[n]);
            alert();
        }
    },
    methods: {
        emitDecision: function (dec) {

            for (let n = 0; n < this.list.length; n++) {
                this.$emit('decision', list[n][0], dec);
            }
        }
    }

}
</script>
