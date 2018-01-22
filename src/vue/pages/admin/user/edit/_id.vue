<template>
  <div>
    <el-col :span="24">
      <div class="filter-icon" @click="onBack">
        <i class="el-icon-fa-angle-left"></i>
      </div>
      <breadcrumb :bc="bc" />
    </el-col>
    <el-col :span="24">
      <edit-form :id="this.$route.params.id" />
    </el-col>
  </div>
</template>

<script>
import Breadcrumb from '~/components/admin/Breadcrumb'
import EditForm from '~/components/admin/user/EditForm'

export default {
  layout: 'admin',
  middleware: 'authenticated',

  components: {
    Breadcrumb,
    EditForm
  },

  head() {
    return {
      title: `${this.$t('pages.admin.users.edit.title')} #${
        this.$route.params.id
      }`,
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: `${this.$t('pages.admin.users.edit.title')} #${
            this.$route.params.id
          }`
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
          name: `${this.$t('pages.admin.users.edit.title')} #${
            this.$route.params.id
          }`,
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
