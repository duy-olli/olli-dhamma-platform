<template>
  <div>
    <i v-show="iconShow" class="el-icon-edit"></i>
    <el-row v-show="isEdit == false" >
      <div @click="enableEditMode" @mouseover="showIcon" @mouseleave="hideIcon">
        <el-tag :type="data.style" size="small">{{ data.label }}</el-tag>
      </div>
    </el-row>
    <el-row v-show="isEdit == true" style="margin-top: 8px;">
      <el-select v-model="form.value" size="mini">
        <el-option
          v-for="item in options"
          :key="item.value"
          :label="item.label"
          :value="item.value">
        </el-option>
      </el-select>
    </el-row>
    <el-row type="flex" justify="end" style="margin-top: 8px;margin-bottom: 8px;" v-show="isEdit == true">
      <el-button-group>
        <el-button size="mini" type="primary" @click="handleEdit" icon="el-icon-check"></el-button>
        <el-button size="mini" @click="isEdit = false" icon="el-icon-close"></el-button>
      </el-button-group>
    </el-row>
  </div>
</template>

<script>
export default {
  props: ['id', 'data', 'store', 'field', 'options'],

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

  methods: {
    showIcon() {
      this.iconShow = true
    },
    hideIcon() {
      this.iconShow = false
    },
    enableEditMode() {
      this.isEdit = true
      this.form.value = this.$props.data.value
    },
    disableEditMode() {
      this.isEdit = false
      this.form.value = this.$props.data.value
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
    margin-right: -12px;
    padding-top: 5px;
    color: #ccc;
  }
  .el-button--mini {
    padding: 2px 5px;
  }
</style>
