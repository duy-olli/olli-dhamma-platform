<template>
  <div>
    <el-col :span="24">
      <div class="filter-icon" @click="onBack">
        <i class="el-icon-fa-angle-left"></i>
      </div>
      <breadcrumb :bc="bc" />
    </el-col>
    <el-col :span="24">
      <create-form />
    </el-col>
  </div>
</template>

<script>
import Breadcrumb from '~/components/admin/Breadcrumb'
import CreateForm from '~/components/admin/user/CreateForm'

export default {
  layout: 'admin',
  middleware: 'authenticated',

  components: {
    Breadcrumb,
    CreateForm
  },

  head() {
    return {
      title: this.$t('pages.admin.users.create.title'),
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: this.$t('pages.admin.users.create.title')
        }
      ]
    }
  },

  data() {
    return {
      loading: false,
      bc: [
        {
          name: this.$t('pages.admin.users.title'),
          link: '/admin/user'
        },
        {
          name: this.$t('pages.admin.users.create.title'),
          link: ''
        }
      ]
    }
  },

  methods: {
    onBack() {
      return this.$router.go(-1)
    }
  }
}
</script>

<style scoped>
.panel-body {
  margin-left: 0;
}

.filter-icon {
  cursor: pointer;
}
</style>
