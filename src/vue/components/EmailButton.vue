<template>
  <div>
    <el-dialog :visible.sync="show" size="tiny">
      <div>
        <el-tabs v-model="activeTab">
          <el-tab-pane label="Đăng nhập" name="login">
            <login-form />
          </el-tab-pane>
          <el-tab-pane label="Đăng ký" name="register">
            <register-form />
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-dialog>

    <el-button type="gray" size="small" @click="show = true">
      <i class="fa fa-envelope"></i> &nbsp; Đăng nhập bằng Email
    </el-button>
  </div>
</template>

<script>
import LoginForm from '~/components/LoginForm'
import RegisterForm from '~/components/RegisterForm'

export default {
  components: {
    LoginForm,
    RegisterForm
  },

  data() {
    return {
      activeTab: 'login',
      show: false
    }
  }
}
</script>
