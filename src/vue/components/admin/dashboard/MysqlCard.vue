<template>
  <el-card class="box-card" v-if="!noContent">
    <div slot="header" class="clearfix">
      <span>
        <i class="el-icon-fa-database" :style="{ color: 'yellowgreen' }"></i>
        &nbsp;
        <strong>MySQL</strong>
      </span>
    </div>
    <div v-for="(item, key) in $props.data.services.mysql" :key="key" class="text item">
      <strong>{{ key }}</strong>: {{ item }}
    </div>
  </el-card>
</template>

<script>
import { isEmpty } from 'lodash'

export default {
  props: ['data'],

  computed: {
    noContent() {
      return isEmpty(this.$props.data)
    }
  }
}
</script>

<style scoped>
  i::before {
    font-size: 27px;
  }
  .box-card {
    height: 245px;
    font-size: 13px;
    overflow-y: scroll;
  }
</style>
