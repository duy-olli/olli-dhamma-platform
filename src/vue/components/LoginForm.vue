<template>
  <el-form autoComplete="on" label-position="left" label-width="0px" class="login-form" :model="loginForm" :rules="loginRules" ref="loginForm">
    <el-form-item prop="email">
      <el-input name="email" type="text" autoComplete="on" placeholder="Email" v-model="loginForm.email">
        <template slot="append"><i class="fa fa-envelope"></i></template>
      </el-input>
    </el-form-item>
    <el-form-item prop="password">
      <el-input name="password" type="password" placeholder="Mật khẩu" v-model="loginForm.password" @keyup.enter.native="handleLogin">
        <template slot="append"><i class="fa fa-key"></i></template>
      </el-input>
    </el-form-item>
    <el-form-item>
      <nuxt-link to="/activate">Kích hoạt</nuxt-link>
      <nuxt-link to="/forgot" style="float: right">
        Quên mật khẩu?
      </nuxt-link>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" style="width:100%;" :loading="loading" @click.native.prevent="handleLogin"> Đăng nhập
      </el-button>
    </el-form-item>
  </el-form>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      loginForm: {
        email: '',
        password: ''
      },
      loginRules: {
        email: [
          {
            required: true,
            message: 'Vui lòng nhập địa chỉ email',
            trigger: 'blur'
          },
          {
            type: 'email',
            message: 'Vui lòng nhập email có giá trị',
            trigger: 'blur,change'
          }
        ],
        password: [
          {
            required: true,
            message: 'Vui lòng nhập mật khẩu',
            trigger: 'blur'
          }
        ]
      }
    }
  },

  methods: {
    handleLogin() {
      this.$refs.loginForm.validate(valid => {
        if (valid) {
          this.loading = true
          this.$store
            .dispatch('login_by_email', this.loginForm)
            .then(() => {
              this.loading = false
              return this.$router.push({
                path: '/'
              })
            })
            .catch(e => {
              this.loading = false
            })
        } else {
          return false
        }
      })
    }
  }
}
</script>
