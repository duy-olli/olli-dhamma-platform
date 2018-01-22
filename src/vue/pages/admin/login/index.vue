<template>
  <section class="login-container">
    <div class="page-content">
      <div class="panel">
        <div class="panel-body">
          <div class="brand">
            <img class="brand-img" src="/img/logo.png" alt="Phalcon VueJS" width="100">
            <h2 class="brand-text font-size-18">{{ $t('pages.admin.login.title') }}</h2>
          </div>

          <el-form autoComplete="on" label-position="left" label-width="0px" class="login-form" :model="loginForm" ref="loginForm">
            <el-form-item prop="email" :rules="[
                { required: true, message: this.$t('pages.admin.login.msg.emailIsRequired'), trigger: 'blur' },
                { type: 'email', message: this.$t('pages.admin.login.msg.emailInvalid'), trigger: 'blur,change' }
            ]">
              <el-input name="email" type="text" size="small" autoComplete="on" :placeholder="$t('pages.admin.login.label.email')" v-model="loginForm.email" autofocus>
                <template slot="prepend"><i class="el-icon-fa-envelope"></i></template>
              </el-input>
            </el-form-item>
            <el-form-item prop="password" :rules="[
                { required: true, message: this.$t('pages.admin.login.msg.passwordIsRequired'), trigger: 'blur' }
            ]">
              <el-input name="password" type="password" size="small" :placeholder="$t('pages.admin.login.label.password')" v-model="loginForm.password" @keyup.enter.native="handleLogin">
                <template slot="prepend"><i class="el-icon-fa-key"></i></template>
              </el-input>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" style="width:100%;" :loading="loading" @click.native.prevent="handleLogin"> {{ $t('pages.admin.login.label.login') }}
              </el-button>
            </el-form-item>
          </el-form>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  fetch({ store, redirect }) {
    if (store.state.authUser) {
      return redirect('/admin/dashboard')
    }
  },

  head() {
    return {
      title: this.$t('pages.admin.login.title'),
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: this.$t('pages.admin.login.title')
        }
      ]
    }
  },

  data() {
    return {
      loginForm: {
        email: '',
        password: ''
      },
      loading: false
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
                path: '/admin/dashboard'
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

<style lang="scss" scoped>
@import "~assets/scss/mixin.scss";

.login-container {
    font-family: Arial;
    width: 100%;
    height: 100vh;
    font-weight: 100;
    background: #3949ab;
    background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJod…EiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
    background-image: -webkit-linear-gradient(top, #3949ab 0%, #283593 100%);
    background-image: -o-linear-gradient(top, #3949ab 0%, #283593 100%);
    background-image: -webkit-gradient(linear, left top, left bottom, from(#3949ab), to(#283593));
    background-image: linear-gradient(to bottom, #3949ab 0%, #283593 100%);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff3949ab', endColorstr='#ff283593', GradientType=0);
    background-repeat: repeat-x;
    background-position: center top;
    -webkit-background-size: cover;
    background-size: cover;

    .page-content {
        @include vertical-align();
        padding: 30px;
        text-align: center !important;

        .panel {
            width: 400px;
            margin-bottom: 45px;
            background: #fff;
            border-radius: 4px;
            display: inline-block;
            vertical-align: middle;

            .panel-body {
                padding: 50px 40px 40px;
                margin-left: 0 !important;
                min-height: 100%;
                max-height: 100%;

                .brand-text {
                    font-size: 18px!important;
                    font-weight: 400;
                    text-shadow: rgba(0,0,0,.15) 0 0 1px;
                }
                .login-form {
                    margin: 45px 0 30px;

                    .el-form-item {
                      margin-bottom: 22px;
                    }
                }
            }
        }
    }
}
</style>
