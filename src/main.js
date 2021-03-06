import Vue from 'vue'
import App from './App.vue'
import 'bootstrap'
import 'bootstrap/dist/css/bootstrap.min.css'

import VueRouter from 'vue-router'
Vue.use(VueRouter)


import UserMerge from './components/UserMerge.vue'
import SimpleMerge from './components/SimpleMerge.vue'
import Carousel from './components/Carousel.vue'
import Selection from './components/Selection.vue'

import mathjax from 'mathjax';


MathJax.Hub.Config({
  root: "extensions/",
  showProcessingMessages: true,
  extensions: ["TeX-AMS-MML_HTMLorMML.js"]

});

MathJax.Hub.Register.MessageHook("Math Processing Error",function (message) {
  //  do something with the error.  message[2] is the Error object that records the problem.
  console.log("Error", message);
});

MathJax.Hub.Startup.signal.Interest(
  //function (message) {alert("Startup: "+message)}
);
MathJax.Hub.signal.Interest(
  //function (message) {alert("Hub: "+message)}
);


//query params
const routes = [
  { path: '?jobID', component: SimpleMerge }
]

const router = new VueRouter({
  routes // short for `routes: routes`
})




import hljs from 'highlight.js';


Object.defineProperty(Vue.prototype, '$mathjax', { value: mathjax });
Object.defineProperty(Vue.prototype, '$hljs', { value: hljs });






Vue.component('app-usermerge', UserMerge);
Vue.component('app-carousel', Carousel);
Vue.component('app-selection', Selection);
Vue.component('app-simpleMerge', SimpleMerge);


Vue.config.productionTip = false

new Vue({
  router,
  render: h => h(App),
}).$mount('#app')