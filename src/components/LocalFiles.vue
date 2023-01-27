<template>
  <div>
    <div id="fileUpload" class="row custom-color-3">
      <div id="uploadFirst" class="col-sm-6 custom-color-1">
        <div class="input-group mb-3">
          <input type="file" class="form-control" id="inputGroupFile02" ref="file1" v-on:change="handleFileUpload(0)" />
        </div>
      </div>
      <div id="uploadSecond" class="col-sm-6 custom-color-2">
        <div class="input-group mb-3">
          <input type="file" class="form-control" id="inputGroupFile02" ref="file2" v-on:change="handleFileUpload(1)" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  emits: ["file1", "file2"],
  props: {
  },
  data() {
    return {
      file1: null,
      file2: null
    }
  },
  methods: {
    handleFileUpload(v) {
      if (v == 0) {
        this.file1 = this.$refs.file1.files[0];
        console.log(this.$refs.file1.files[0]);
        //emit
        this.$emit('file1', this.file1);
      } else if (v == 1) {
        this.file2 = this.$refs.file2.files[0];
        console.log(this.$refs.file2.files[0]);
        this.$emit('file2', this.file2);
      } else console.error(v + "  is not a valid version number");
    },
  }
}
</script>