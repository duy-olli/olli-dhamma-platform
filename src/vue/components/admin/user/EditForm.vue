<template>
  <div class="panel-body">
    <el-col :span="4">
      <h2>{{ $t('pages.admin.users.edit.description') }}</h2>
      <p>{{ $t('pages.admin.users.edit.extraDescription') }}</p>
    </el-col>
    <el-col :span="20">
      <el-col :span="8">
        <el-form autoComplete="on" label-position="left" class="edit-form" :model="form" :rules="rules" ref="form">
          <el-form-item prop="fullname" :label="$t('pages.admin.users.label.name')">
            <el-input type="text" size="small" v-model="form.fullname"></el-input>
          </el-form-item>
          <el-form-item prop="groupid" :label="$t('pages.admin.users.label.group')" size="small">
            <el-select v-model="form.groupid" placeholder="Chọn nhóm" style="width: 100%" :loading="loading">
              <el-option v-for="item in source.groupList" :key="item" :label="item" :value="item">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item prop="status" :label="$t('pages.admin.users.label.status')">
            <el-select v-model="form.status" placeholder="Chọn trạng thái" style="width: 100%" :loading="loading" size="small">
              <el-option v-for="item in source.statusList" :key="item.label" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item prop="verifytype" :label="$t('pages.admin.users.label.verifyType')">
            <el-select v-model="form.verifytype" placeholder="Chọn loại kích hoạt" style="width: 100%" :loading="loading" size="small">
              <el-option v-for="item in source.verifyList" :key="item.label" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </el-form-item>
          <el-form-item style="margin-top: 30px">
            <el-button type="primary" :loading="loading" @click.native.prevent="handleSubmit"> {{ $t('default.update') }}
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
  props: ['id'],

  data() {
    return {
      loading: false,
      form: {
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
            .dispatch('users/update', {
              authToken: this.$store.state.authToken,
              id: this.$props.id,
              formData: this.form
            })
            .then(res => {
              this.loading = false

              this.$message({
                showClose: true,
                message: this.$t('pages.admin.users.msg.updateSuccess'),
                type: 'success',
                duration: 5 * 1000
              })
            })
            .catch(e => {
              this.loading = false
            })
        } else {
          return false
        }
      })
    },
    async resetField() {
      await this.$store
        .dispatch('users/get', {
          authToken: this.$store.state.authToken,
          id: this.$props.id
        })
        .then(res => {
          return this.mapProperty(res)
        })
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
    },
    async fetchUserInfo() {
      await this.$store
        .dispatch('users/get', {
          authToken: this.$store.state.authToken,
          id: this.$props.id
        })
        .then(res => {
          return this.mapProperty(res)
        })
        .catch(e => {
          this.error = true
        })
    },
    mapProperty(res) {
      this.form.fullname = res.data.response.fullname
      this.form.groupid = res.data.response.groupid
      this.form.status = res.data.response.status.value
      this.form.verifytype = res.data.response.verifytype.value
    }
  },

  created() {
    this.fetchSourceAction(), this.fetchUserInfo()
  },

  watch: {
    $route: 'fetchSourceAction',
    $route: 'fetchUserInfo'
  }
}
</script>
