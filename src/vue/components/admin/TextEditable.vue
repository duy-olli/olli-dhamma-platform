<template>
  <div>
    <i v-show="iconShow" class="el-icon-edit"></i>
    <el-row v-show="!isEdit">
      <div @click="enableEditMode" @mouseover="showIcon" @mouseleave="hideIcon" class="edit-area">{{ calc }}</div>
    </el-row>
    <el-row v-show="isEdit">
      <el-input
        ref="myinput"
        v-model="form.value"
        size="small"
        @keyup.enter.native="handleEdit"
        @blur="disableEditMode">
        <i class="el-icon-close el-input__icon" slot="suffix" @click="disableEditMode" v-if="!loading"></i>
        <i class="el-icon-fa-spinner el-icon-fa-spin el-input__icon" slot="suffix" v-else></i>
      </el-input>
    </el-row>
  </div>
</template>

<script>
export default {
  props: ['id', 'data', 'store', 'field'],

  data() {
    return {
      isEdit: false,
      loading: false,
      form: {
        value: ''
      },
      iconShow: false
    }
  },

  computed: {
    calc() {
      if (this.form.value !== '' && this.$props.data !== this.form.value) {
        return this.form.value
      } else {
        return this.$props.data
      }
    }
  },

  methods: {
    showIcon() {
      this.iconShow = true
    },
    hideIcon() {
      this.iconShow = false
    },
    enableEditMode() {
      this.isEdit = true
      this.form.value = this.$props.data

      // Focus on selected input
      const self = this
      setTimeout(function() {
        self.$refs.myinput.$el.getElementsByTagName('input')[0].focus()
      }, 1)
    },
    disableEditMode() {
      this.isEdit = false
      this.form.value = this.$props.data
    },
    async handleEdit() {
      this.loading = true

      await this.$store
        .dispatch(`${this.$props.store}/update_field`, {
          authToken: this.$store.state.authToken,
          formData: {
            id: this.$props.id,
            field: this.$props.field,
            value: this.form.value
          }
        })
        .then(res => {
          this.loading = false
          this.isEdit = false

          this.$notify({
            dangerouslyUseHTMLString: true,
            type: 'success',
            message: this.$t(
              `pages.admin.songs.msg.${this.$props.field}UpdateSuccess`
            ).replace('###name###', res.data.response.name)
          })
        })
    }
  }
}
</script>

<style scoped lang="css">
  .el-icon-edit {
    display: inline-block;
    float: right;
    margin-right: 10px;
    padding-top: 5px;
    color: #95a5a6;
  }
  .edit-area {
    min-height: 25px;
  }
</style>
