<template>
  <el-card class="box-card" v-if="!noContent">
    <div slot="header" class="clearfix">
      <span>
        <i class="el-icon-fa-search" :style="{ color: color }"></i>
        &nbsp;
        <strong>Sphinxsearch</strong>
      </span>
      <el-button style="float: right; padding: 3px 0" type="text" @click="handleIndexing" :loading="loading"> reindex </el-button>
    </div>
    <div v-for="(item, key) in $props.data.services.sphinx" :key="key" class="text item">
      <strong>{{ key }}</strong>: {{ item }}
    </div>
  </el-card>
</template>

<script>
import { isEmpty } from 'lodash'

export default {
  props: ['data'],
  data() {
    return {
      loading: false
    }
  },
  computed: {
    noContent() {
      return isEmpty(this.$props.data)
    },
    color() {
      if (this.$props.data.services.sphinx.status) {
        return 'yellowgreen'
      } else {
        return 'darkgray'
      }
    }
  },
  methods: {
    async handleIndexing() {
      this.loading = true
      await this.$store
        .dispatch('songs/re_index', {
          authToken: this.$store.state.authToken,
        })
        .then(res => {
          this.loading = false

          this.$message({
            showClose: true,
            message: this.$t('pages.admin.songs.msg.updateSuccess'),
            type: 'success',
            duration: 5 * 1000
          })
        })
        .catch(e => {
          this.loading = false
        })

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
