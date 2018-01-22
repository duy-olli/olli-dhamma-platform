<template>
  <div>
    <el-col :span="24">
      <div class="filter-icon" @click="onBack">
        <i class="el-icon-fa-angle-left"></i>
      </div>
      <breadcrumb :bc="bc" />
    </el-col>
    <el-col :span="24">
      <change-password-form />
    </el-col>
  </div>
</template>

<script>
import Breadcrumb from '~/components/admin/Breadcrumb'
import ChangePasswordForm from '~/components/admin/user/ChangePasswordForm'

export default {
  layout: 'admin',
  middleware: 'authenticated',

  components: {
    Breadcrumb,
    ChangePasswordForm
  },

  head() {
    return {
      title: this.$t('pages.admin.users.changePassword.title'),
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: this.$t('pages.admin.users.changePassword.description')
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
          name: this.$t('pages.admin.users.changePassword.title'),
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
