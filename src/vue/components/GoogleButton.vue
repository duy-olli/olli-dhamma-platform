<template>
  <el-button type="primary" size="small" ref="signinBtn" @click="handleLogin">
    <i class="fa fa-google"></i> &nbsp; Đăng nhập bằng Google
  </el-button>
</template>

<script>
export default {
  data() {
    return {
      auth2: {},
      form: {
        oauthUid: '',
        oauthAccessToken: '',
        oauthInfo: {}
      }
    }
  },

  methods: {
    handleLogin() {
      console.log('handle login')
      this.auth2
        .signIn({
          scope: 'profile email'
        })
        .then(googleUser => {
          const profile = googleUser.getBasicProfile()
          const authResponse = googleUser.getAuthResponse()

          this.form.oauthUid = profile.getId()
          this.form.oauthAccessToken = authResponse.access_token
          this.form.oauthInfo = {
            id: profile.getId(),
            email: profile.getEmail(),
            name: profile.getName(),
            avatar: profile.getImageUrl()
          }

          this.$store
            .dispatch('login_by_google', {
              formData: this.form
            })
            .then(() => {
              console.log('done promise ')
              const redirectQuery = this.$route.query.redirect
              if (typeof redirectQuery !== 'undefined') {
                return (window.location.href = new Buffer(
                  redirectQuery,
                  'base64'
                ).toString('ascii'))
              } else {
                return (window.location.href = '/')
              }
            })
        })
    }
  },

  mounted() {
    var self = this

    window.gapi.load('auth2', () => {
      const auth2 = window.gapi.auth2.init({
        client_id: process.env.GOOGLE_CLIENT_ID
      })

      self.auth2 = auth2
    })
  }
}
</script>

<style scoped lang="scss">
.el-button {
    background-color: #d34836;
    border-color: #d34836;
    z-index: 9999;
    opacity: 0.9;
}
</style>
