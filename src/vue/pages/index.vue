<template>
  <el-row :gutter="30">
    <p>
      MUSIC
    </p>
  </el-row>
</template>

<script>
export default {
  layout: 'site',

  head() {
    return {
      title: this.$t('pages.site.home.title'),
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: this.$t('pages.site.home.title')
        }
      ]
    }
  }
}
</script>
