<template>
  <div id="simpleMerge" class>
    <h3>Automatic Merging</h3>
    <p>
      With the fully automatic approach to merging you only need to provide the
      two SBML files you want to merge. You can use this website, the command
      line or call out PHP service. In all cases the files are send to our BiVeS
      service and stored temporarly. We generate a random ID for each call which
      encapsulates your uploaded files, as well as the merge result. The files
      can only be accessed and downloaded with this ID.
    </p>
    <h4>How to use it?</h4>
    <div id="howToWeb">
      <h5>Web interface</h5>
      <p>
        You can select two local model files with the provided interface. The
        files are send to out BiVeS server to the BiVeS service with "compute
        merge". You can afterwards download the merged model and share a link to
        it.
      </p>

      <div class="container">
        <local-files></local-files>
      </div>

      <button
        v-if="!job"
        ref="compute merge"
        v-on:click="computeMerge"
        type="button"
        class="btn btn-primary btn-lg"
      >
        Compute Merge
      </button>
      <!--       <button
        v-else
        ref="goBackBtn"
        v-on:click="goBackToOrigin"
        type="button"
        class="btn btn-primary btn-lg"
      >
        Return to Starting Page
      </button> -->
      <button
        v-if="job"
        ref="download"
        v-on:click="download"
        type="button"
        class="btn btn-primary btn-lg"
      >
        Download
      </button>
      <button
        v-if="job"
        ref="copy"
        v-on:click="copyURL"
        type="button"
        class="btn btn-primary btn-lg"
      >
        Copy URL to merged model
      </button>
    </div>
    <hr />

    <div id="debugging" class="bg-warning" v-if="debug">
      <h4>Development out</h4>
      <p>set debug to false to disable the dev output</p>
      <p>jobID: {{ this.job }}</p>
      <p>goBack: {{ $route.query.goBack }}</p>
      <p>devCounter: 8</p>
    </div>

    <div id="howToCurl">
      <h5>Command line access</h5>
      <p>
        You can use the BiVeS service from the command line. Below you find an
        example. Visit the
        <a href="https://bives.bio.informatik.uni-rostock.de/"> website </a> to
        see more options.
      </p>
      <pre><code>
        curl -d '{
          "files":
          [
              "_path to file 1_",
              "_path to file 2_" 
          ],
          "commands": [ "merge" ] }'
          https://bives.bio.informatik.uni-rostock.de/
      </code></pre>
    </div>
    <hr />

    <div id="howToURL">
      <h5>Website integration</h5>
      <p>
        For integration you can send post requests to our PHP script. This is a
        two step process:
      </p>
      <p>
        1. Send a post request to
        https://merge-proto.bio.informatik.uni-rostock.de/bives/simpleMerge.php
        with the two files atached with the keys file1 and file2. You will
        recieve an ID as response.
      </p>
      <p>
        2. Send a post request to the same URL. Attach the ID with the key jobID
        and "mergedModel" with the key getFile.
      </p>
    </div>
  </div>
</template>

<script>
import LocalFiles from "./LocalFiles.vue";
import axios from 'axios';

export default {
  components: {
        LocalFiles
    },
  data() {
    return {
      job: this.$route.query.jobID,
      debug: false,
      //goBackexsists: this.$route.query.goBack,
      //goBack: decodeURIComponent(this.$route.query.goBack),
      merged: false,
      file1: "",
      file2: "",
    };
  },
  computed: {},
  methods: {
    download: function () {
      //const axios = require("axios");
      //alert("job is set");
      const paramsBuild = new URLSearchParams();
      paramsBuild.append("jobID", this.job);
      paramsBuild.append("getFile", "mergedModel");

      axios
        .get("/bives/simpleMerge.php", {
          params: paramsBuild,
        })
        .then((response) => {
          console.log("Response of get File: \n" + response.data);
          this.forceFileDownload(response);
        });
      //this.produceSimpleMerge(false);
    },
    downloadMerge: function () {},
    computeMerge: function () {
      //const axios = require("axios");
      /*
          Initialize the form data
        */
      let formData = new FormData();

      /*
          Iteate over any file sent over appending the files
          to the form data.
        */
      let file = this.file1;
      formData.append("file1", file);
      file = this.file2;
      formData.append("file2", file);

      /*
        Make the request to the POST /multiple-files URL
      */

      console.log("sending files to bives for merge. returning Job ID");
      axios
        .post("/bives/simpleMerge.php", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          console.log(response);
          console.log("ID = " + response.data);
          this.job = response.data;

        })
        .catch(function (e) {
          console.log("FAILURE!!" + e);
        });
    },
    copyURL: function () {
      // Create new element
      var el = document.createElement("textarea");
      // Set value (string to be copied)
      var baseURL =
        "https://merge-proto.bio.informatik.uni-rostock.de/bives/simpleMerge.php?";

      ///bives/simpleMerge.php?getFile=mergedModel&jobID=fdc5ba9ea21ea49bcc6db0a8af335955

      el.value = baseURL + "getFile=" + "mergedModel" + "&jobID=" + this.job;

      // Set non-editable to avoid focus and move outside of view
      el.setAttribute("readonly", "");
      el.style = { position: "absolute", left: "-9999px" };
      document.body.appendChild(el);
      // Select text inside element
      el.select();
      // Copy text to clipboard
      document.execCommand("copy");
      // Remove temporary element
      document.body.removeChild(el);
    },
    goBackToOrigin: function () {
      console.log("calling goBackToOrigin");

      this.produceSimpleMerge(true);

      var regex = /.+?(?=merge_versions|[?#])/;
      var backRoute = regex.exec(this.goBack);
      backRoute =
        backRoute +
        "#/?mergedModel=" +
        "/tmp/mergestorage/" +
        this.job +
        "/mergedModel";
      alert(backRoute);
      window.open(backRoute, "_self");
    },
    produceSimpleMerge: function (isExternal) {
      let f1;
      let f2;

      if (isExternal) {
        console.log("external request " + this.job);
        f1 = "/tmp/mergestorage/" + this.job + "/f1";
        f2 = "/tmp/mergestorage/" + this.job + "/f2";
      } else {
        console.log("internal request ");
        f1 = this.file1;
        f2 = this.file2;
      }

      console.log(f1, f2);

      this.submitFiles();
    },
    forceFileDownload(response) {
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", "mergedModel.xml"); //or any other extension
      document.body.appendChild(link);
      link.click();
    }
  },
};
</script>

<style>
#fileUpload {
  padding-bottom: 1em;
}

.btn-primary {
  background-color: #365b9e;
  border-color: #365b9e;
}

.btn {
  margin-bottom: 1em;
}
</style>