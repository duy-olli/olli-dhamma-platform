<template>
  <el-button type="primary" size="small" @click="handleLogin()">
    <i class="fa fa-facebook"></i> &nbsp; Đăng nhập bằng Facebook
  </el-button>
</template>

<script>
export default {
  data() {
    return {
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
      var self = this

      window.FB.login(
        function(response) {
          if (response.authResponse) {
            ;(self.form.oauthUid = response.authResponse.userID),
              (self.form.oauthAccessToken = response.authResponse.accessToken)

            window.FB.api(
              '/me',
              {
                fields:
                  'id,email,name,first_name,last_name,gender,picture.width(300)'
              },
              function(response) {
                self.form.oauthInfo = response

                self.$store
                  .dispatch('login_by_facebook', {
                    formData: self.form
                  })
                  .then(() => {
                    console.log('done promise ')
                    const redirectQuery = self.$route.query.redirect
                    if (typeof redirectQuery !== 'undefined') {
                      return (window.location.href = new Buffer(
                        redirectQuery,
                        'base64'
                      ).toString('ascii'))
                    } else {
                      return (window.location.href = '/')
                    }
                  })
              }
            )
          }
        },
        {
          scope: 'email,public_profile,publish_actions,user_birthday',
          return_scopes: true,
          enable_profile_selector: true
        }
      )
    }
  },

  mounted() {
    window.fbAsyncInit = function() {
      FB.init({
        appId: process.env.FACEBOOK_APP_ID,
        cookie: true, // enable cookies to allow the server to access the session
        xfbml: true, // parse social plugins on this page
        version: 'v2.8' // use graph api version 2.8
      })
    }
    ;(function(d, s, id) {
      var js,
        fjs = d.getElementsByTagName(s)[0]
      if (d.getElementById(id)) return
      js = d.createElement(s)
      js.id = id
      js.src = '//connect.facebook.net/en_US/sdk.js'
      fjs.parentNode.insertBefore(js, fjs)
    })(document, 'script', 'facebook-jssdk')
  }
}
</script>

<style scoped lang="scss">
.el-button {
    background-color: #3B5998;
    border-color: #3B5998;
    z-index: 9999;
    opacity: 0.9;
}
</style>
