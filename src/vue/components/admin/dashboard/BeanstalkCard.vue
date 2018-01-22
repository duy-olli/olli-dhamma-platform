<template>
  <el-card class="box-card" v-if="!noContent">
    <div slot="header" class="clearfix">
      <span>
        <i class="el-icon-fa-server" :style="{ color: color }"></i>
        &nbsp;
        <strong>Beanstalkd</strong>
      </span>
    </div>
    <div v-for="(item, key) in $props.data.services.beanstalk" :key="key" class="text item">
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
    },
    color() {
      if (this.$props.data.services.beanstalk.status) {
        return 'yellowgreen'
      } else {
        return 'darkgray'
      }
    }
  }
}
</script>

<style scoped lang="scss">
  i::before {
    font-size: 27px;
  }
  .box-card {
    height: 245px;
    font-size: 13px;
    overflow-y: scroll;
  }
</style>
