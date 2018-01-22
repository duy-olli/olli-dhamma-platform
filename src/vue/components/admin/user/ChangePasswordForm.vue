<template>
  <div class="panel-body">
    <el-col :span="4">
      <h2>{{ $t('pages.admin.users.changePassword.description') }}</h2>
      <p>{{ $t('pages.admin.users.changePassword.extraDescription') }}</p>
    </el-col>
    <el-col :span="20">
      <el-col :span="8">
        <el-form autoComplete="on" label-position="left" class="changepassword-form" :model="form" :rules="rules" ref="form">
          <el-form-item prop="oldpassword" :label="$t('pages.admin.users.label.oldPassword')">
            <el-input type="password" v-model="form.oldpassword"></el-input>
          </el-form-item>
          <el-form-item prop="newpassword" :label="$t('pages.admin.users.label.newPassword')">
            <el-input type="password" v-model="form.newpassword"></el-input>
          </el-form-item>
          <el-form-item prop="repeatnewpassword" :label="$t('pages.admin.users.label.repeatNewPassword')">
            <el-input type="password" v-model="form.repeatnewpassword"></el-input>
          </el-form-item>

          <el-form-item style="margin-top: 30px">
            <el-button type="primary" :loading="loading" @click.native.prevent="handleSubmit"> {{ $t('pages.admin.users.label.changePassword') }}
            </el-button>
            <el-button @click="resetField">{{ $t('default.reset') }}</el-button>
          </el-form-item>
        </el-form>
      </el-col>
    </el-col>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      form: {
        oldpassword: '',
        newpassword: '',
        repeatnewpassword: ''
      },
      rules: {
        oldpassword: [
          {
            required: true,
            message: this.$t('pages.admin.users.msg.oldPasswordIsRequired'),
            trigger: 'blur'
          }
        ],
        newpassword: [
          {
            required: true,
            message: this.$t('pages.admin.users.msg.newPasswordIsRequired'),
            trigger: 'blur'
          }
        ],
        repeatnewpassword: [
          {
            required: true,
            message: this.$t(
              'pages.admin.users.msg.repeatNewPasswordIsRequired'
            ),
            trigger: 'blur'
          }
        ]
      },
      source: {}
    }
  },

  methods: {
    handleSubmit() {
      this.$refs.form.validate(async valid => {
        if (valid) {
          this.loading = true
          await this.$store
            .dispatch('changepassword', {
              authToken: this.$store.state.authToken,
              formData: this.form
            })
            .then(res => {
              this.loading = false

              // flash message
              this.$message({
                showClose: true,
                message: this.$t('pages.admin.users.msg.changePasswordSuccess'),
                type: 'success',
                duration: 5 * 1000
              })

              // reset form
              this.$refs.form.resetFields()
            })
            .catch(e => {
              this.loading = false
            })
        } else {
          return false
        }
      })
    },
    resetField() {
      this.$refs.form.resetFields()
    }
  }
}
</script>
