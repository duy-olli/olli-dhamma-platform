<template>
  <div class="pagination" v-if="totalItems > 0">
    <el-button type="text" icon="el-icon-arrow-left" :disabled="previousPage === 0" @click="handlePageChange(previousPage)">
      {{ $t('default.previousPage') }}
    </el-button>
    <span class="text">{{ $t('default.page') }} {{ currentPage }} / {{ totalPage }}</span>
    <el-button type="text" icon="el-icon-arrow-right" :disabled="nextPage > totalPage" @click="handlePageChange(nextPage)">
      {{ $t('default.nextPage') }}
    </el-button>
  </div>
</template>

<script>
export default {
  props: ['totalItems', 'currentPage', 'recordPerPage'],

  computed: {
    previousPage: function() {
      return this.$props.currentPage - 1
    },
    nextPage: function() {
      return this.$props.currentPage + 1
    },
    totalPage: function() {
      return Math.ceil(this.$props.totalItems / this.$props.recordPerPage)
    }
  },

  methods: {
    handlePageChange(page) {
      const querystring = require('querystring')

      let queryObject = _.assign({}, this.$route.query)
      queryObject.page = page
      const pageUrl = `?${querystring.stringify(queryObject)}`

      return this.$router.push(pageUrl)
    }
  }
}
</script>
