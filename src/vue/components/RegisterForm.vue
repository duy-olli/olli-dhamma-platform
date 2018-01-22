<template>
  <el-form autoComplete="on" label-position="left" label-width="0px" class="register-form" :model="registerForm" :rules="registerRules" ref="registerForm">
    <el-form-item prop="fullname">
      <el-input name="fullname" type="text" autoComplete="on" placeholder="Tên đầy đủ" v-model="registerForm.fullname"></el-input>
    </el-form-item>
    <el-form-item prop="email">
      <el-input name="email" type="text" autoComplete="on" placeholder="Email" v-model="registerForm.email"></el-input>
    </el-form-item>
    <el-form-item prop="password">
      <el-input name="password" type="password" placeholder="Mật khẩu" v-model="registerForm.password"></el-input>
    </el-form-item>
    <el-form-item prop="repeatpassword">
      <el-input name="repeatpassword" type="password" placeholder="Nhập lại mật khẩu" v-model="registerForm.repeatpassword" @keyup.enter.native="handleRegister"></el-input>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" style="width:100%;" :loading="loading" @click.native.prevent="handleRegister"> Đăng ký
      </el-button>
    </el-form-item>
  </el-form>
</template>

<script>
export default {
  data() {
    const validateRepeatpassword = (rule, value, callback) => {
      if (value !== this.registerForm.password) {
        callback(new Error('Mật khẩu không trùng nhau'))
      } else {
        callback()
      }
    }

    return {
      loading: false,
      registerForm: {
        fullname: '',
        email: '',
        password: '',
        repeatpassword: ''
      },
      registerRules: {
        fullname: [
          {
            required: true,
            message: 'Vui lòng nhập tên đầy đủ',
            trigger: 'blur'
          }
        ],
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
        ],
        repeatpassword: [
          {
            required: true,
            message: 'Vui lòng nhập lại mật khẩu',
            trigger: 'blur'
          },
          {
            validator: validateRepeatpassword,
            trigger: 'blur'
          }
        ]
      },
      siteKey: ''
    }
  },

  methods: {
    handleRegister() {
      this.$refs.registerForm.validate(valid => {
        if (valid) {
          this.loading = true
          this.$store
            .dispatch('register', this.registerForm)
            .then(() => {
              this.loading = false
              return this.$router.push({
                path: `/activate/${this.registerForm.email}`
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
