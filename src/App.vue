<template>
  <div id="topLayer" class="d-flex flex-column h-100">
    <div class="custom-color-1" id="head">
      <div class="container">
        <div class="row ">
          <div id="logoContainer" class="col-8  d-flex">
            <img id="fullLogo" src="/src/assets/full-logo.svg" />
          </div>

          <div class="col-4 d-flex align-items-center justify-content-end">
            <button type="button" class="btn btn-light btn-sm" v-on:click="btnSwitchView">
              Switch Mode
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="custom-color-2" id="intro">
      <div class="container">
        <div class="row justify-content-center">
          <p class="col-8">
            Model development is often a non-linear and decentralised process.
            It requries time consuming effort to gather the necessary
            information from literature, experimental data and databases. The
            information are either collected in tables or directly put into a
            model using common tools (e. g. CellDesigner). Subseqeuntly, the
            collaborative nature of modelling requires one partner to collect
            and combine the gathered information. Our merging tool aims to ease
            this process by enabling the combination of two model versions.
          </p>
        </div>
      </div>
    </div>
    <div class="custom-color-2" id="center">
      <div class="container">
        <user-merge v-if="!simple" />
        <simple-merge v-else />
      </div>
    </div>
    <div class="footer mt-auto custom-color-3" id="foot">
      <div class="container">
        <!--Image-->
        <div class="row">
          <div class="col-2 d-flex align-items-center">
            <a href="https://integrative-pathway-models.de/" title="INCOME-Website">
              <img src="./assets/csm_logo_INCOME11.png" class="img-fluid" alt />
            </a>
          </div>
          <div class="col-10 d-flex align-items-end flex-column" id="contact">
            <h5>Contact</h5>
            <p>Tom Gebhardt</p>
            <p>tom.gebhardt@uni-rostock.de</p>
            <p>https://www.sbi.uni-rostock.de/</p>
          </div>
        </div>
        <div class="row">
          <div class="col-12 d-flex justify-content-start">
            The project was funded by the BMBF (FKZ: 01ZX1705C)
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
//import Selection from './components/Selection.vue'
//import HelloWorld from './components/HelloWorld.vue'
import SimpleMerge from "./components/SimpleMerge.vue";
import UserMerge from "./components/UserMerge.vue";
//import { useRoute } from "vue-router";

export default {
  name: "app",
  components: {
    SimpleMerge,
    UserMerge,
  },
  data() {
    return {
      simple: false,
      job: "fakeJob",
      //job: this.$route.query.jobID,
    };
  },
  methods: {
    btnSwitchView: function () {
      this.simple = !this.simple;
    },
  },
};

// export default {
//   name: 'App',
//   components: {
//     //HelloWorld,
//     SimpleMerge,
//     //Selection,
//     UserMerge
//   }
// }
//import 'bootstrap/dist/css/bootstrap.min.css'

</script>

<style>
#head>button {
  margin-bottom: 0;
}
</style>

<style scoped>
.custom-color-1 {
  background-color: #365b9e;
  color: #feffff;
}

.custom-color-2 {
  background-color: #e5e5e5;
  color: #17252a;
}

.custom-color-3 {
  background-color: white;
  color: #17252a;
}

#intro {
  padding-top: 1rem;
}

#center {
  padding-bottom: 1em;
}

#head {
  padding-top: 1em;
  padding-bottom: 1rem;
}

#foot {
  padding-top: 1em;
  padding-bottom: 1em;
}

#contact>p {
  margin-bottom: auto;
}

#logoContainer {
  max-height: 75px;
  overflow: visible;
}

#fullLogo {
  min-height: 130px;
  position: relative;
  top: -10px;
  z-index: 999;
}
</style>