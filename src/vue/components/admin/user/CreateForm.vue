<template>
  <div class="panel-body">
    <el-col :span="4">
      <h2>{{ $t('pages.admin.users.create.description') }}</h2>
      <p>{{ $t('pages.admin.users.create.extraDescription') }}</p>
    </el-col>
    <el-col :span="20">
      <el-col :span="8">
        <el-form autoComplete="on" label-position="left" class="create-form" :model="form" :rules="rules" ref="form">
          <el-form-item prop="fullname" :label="$t('pages.admin.users.label.name')">
            <el-input type="text" size="small" v-model="form.fullname"></el-input>
          </el-form-item>
          <el-form-item prop="email" :label="$t('pages.admin.users.label.email')">
            <el-input type="text" size="small" v-model="form.email"></el-input>
          </el-form-item>
          <el-form-item prop="password" :label="$t('pages.admin.users.label.password')">
            <el-input type="password" size="small" v-model="form.password"></el-input>
          </el-form-item>
          <el-form-item prop="groupid" :label="$t('pages.admin.users.label.group')">
            <el-select size="small" v-model="form.groupid" :placeholder="$t('pages.admin.users.label.selectGroup')" style="width: 100%" :loading="loading">
              <el-option v-for="item in source.groupList" :key="item" :label="item" :value="item">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item prop="status" :label="$t('pages.admin.users.label.status')">
            <el-select size="small" v-model="form.status" :placeholder="$t('pages.admin.users.label.selectStatus')" style="width: 100%" :loading="loading">
              <el-option v-for="item in source.statusList" :key="item.label" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item prop="verifytype" :label="$t('pages.admin.users.label.verifyType')">
            <el-select size="small" v-model="form.verifytype" :placeholder="$t('pages.admin.users.label.selectVerifyType')" style="width: 100%" :loading="loading">
              <el-option v-for="item in source.verifyList" :key="item.label" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item style="margin-top: 30px">
            <el-button type="primary" :loading="loading" @click.native.prevent="handleSubmit"> {{ $t('default.create') }}
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
        email: '',
        password: '',
        fullname: '',
        groupid: '',
        status: '',
        verifytype: ''
      },
      rules: {
        fullname: [
          {
            required: true,
            message: this.$t('pages.admin.users.msg.nameIsRequired'),
            trigger: 'blur'
          }
        ],
        email: [
          {
            required: true,
            message: this.$t('pages.admin.users.msg.emailIsRequired'),
            trigger: 'blur'
          },
          {
            type: 'email',
            message: this.$t('pages.admin.users.msg.emailInvalid'),
            trigger: 'blur,change'
          }
        ],
        password: [
          {
            required: true,
            message: this.$t('pages.admin.users.msg.passwordIsRequired'),
            trigger: 'blur'
          }
        ],
        groupid: [
          {
            required: true,
            message: this.$t('pages.admin.users.msg.groupIsRequired'),
            trigger: 'change'
          }
        ],
        status: [
          {
            required: true,
            message: this.$t('pages.admin.users.msg.statusIsRequired'),
            trigger: 'change'
          }
        ],
        verifytype: [
          {
            required: true,
            message: this.$t('pages.admin.users.msg.verifyTypeIsRequired'),
            trigger: 'change'
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
            .dispatch('users/create', {
              authToken: this.$store.state.authToken,
              formData: this.form
            })
            .then(res => {
              this.loading = false

              const msg = this.$message({
                showClose: true,
                message: this.$t('pages.admin.users.msg.createSuccess'),
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
    },
    async fetchSourceAction() {
      await this.$store
        .dispatch('users/get_form_source', {
          authToken: this.$store.state.authToken
        })
        .then(() => {
          this.source = this.$store.state.users.formSource.records
        })
        .catch(e => {
          this.error = true
        })
    }
  },

  created() {
    this.fetchSourceAction()
  },

  watch: {
    $route: 'fetchSourceAction'
  }
}
</script>
