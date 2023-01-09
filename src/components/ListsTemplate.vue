<template>
          <div v-if="el.change === 'i'">
            <ul class="list-group list-group-horizontal">
              <li v-for="(attr, name) in el.attr" :key="name" class="list-group-item insert-color">
                <span v-if="name === 'math'" v-html="attr"> </span>
                <span v-else> <b>{{ name }}: </b> {{attr}} </span>
              </li>
              <div v-if="el.changeID" class="container">
                      <decision-btn :changeID="el.changeID" :d="decisionArr[el.changeID]['decision']" @decision="this.updateDecision"/>
              </div>
            </ul>
          </div>
          <div v-else-if="el.change === 'd'">
            <ul class="list-group list-group-horizontal">
              <li v-for="(attr, name) in el.attr" :key="name" class="list-group-item delete-color">
                <span v-if="name === 'math'" v-html="attr"> </span>
                <span v-else> <b>{{ name }}: </b> {{attr}} </span>

              </li>
              <div v-if="el.changeID" class="container">
                      <decision-btn :changeID="el.changeID" :d="decisionArr[el.changeID]['decision']" @decision="this.updateDecision"/>
                </div>
            </ul>
          </div>
          <div v-else>         
            <ul class="list-group list-group-horizontal">
              <li v-for="(attr, name) in el" :key="name" class="list-group-item">
                <div v-if="typeof attr === 'object'">
                  <div v-if="attr.type === 'u'">
                    <span class="update-color">{{name}}</span>: 
                    <div v-if="name === 'math'">
                        <span class="delete-color" v-html="attr.oldValue"></span> &#8594; <span class="insert-color" v-html="attr.newValue"></span>
                    </div>
                    <span v-else class="delete-color"> {{ attr.oldValue}} </span> &#8594; <span class="insert-color"> {{ attr.newValue}} </span>
                    <div v-if="attr.changeID" class="container">
                      <decision-btn :changeID="attr.changeID" :d="decisionArr[attr.changeID]['decision']" @decision="this.updateDecision"/>
                </div>
                  </div>
                  <div v-else-if="attr.type === 'i'" class="insert-color">
                    <div v-if="name === 'math'"><b> unit: </b> {{attr.newValue}}</div>
                    <span v-else> <b>{{ name }}: </b> {{attr.newValue}} </span>
                    <div v-if="attr.changeID" class="container"> 
                      <decision-btn :changeID="attr.changeID" :d="decisionArr[attr.changeID]['decision']" @decision="this.updateDecision"/>
                    </div>
                  </div>
                  <div v-else class="delete-color">
                    <div v-if="name === 'math'"><b> math: </b> {{attr.oldValue}}</div>
                    <span v-else> <b>{{ name }}----: </b> {{attr.oldValue}}</span>
                    <div v-if="attr.changeID" class="container">
                      <decision-btn :changeID="attr.changeID" :d="decisionArr[attr.changeID]['decision']" @decision="this.updateDecision"/>
                </div>
                  </div>
                </div>
                <div v-else>
                    <span v-if="name === 'math'"><b> unit: </b> <span v-html="attr"></span></span>
                    <span v-else> <b>{{ name }}: </b> {{attr}}</span>
                </div>
              </li>
            </ul>
          </div>
</template>

<script>
//import { defineComponent } from '@vue/composition-api'

import DecisionBtn from './DecisionBtn.vue';

export default {
    props: ['el', 'decisionArr'],
    emits: ['decision'],
    components: {
        DecisionBtn
    },
    data(){
        return {
            
        }
    },
    methods: {
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
        updateDecision: function(changeID, dec){
            this.$emit('decision', changeID, dec);
        }
    }

}
</script>
