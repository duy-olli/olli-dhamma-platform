<template>
  <div>
    <el-popover ref="myPopover" placement="left" v-model="visible">
      <p>{{ $t('default.msg.deleteConfirm') }}</p>
      <div style="text-align: right; margin: 0">
        <el-button size="mini" type="text" @click="onCancel">{{ $t('default.msg.cancel') }}</el-button>
        <el-button type="danger" size="mini" @click="onConfirm">{{ $t('default.msg.confirm') }}</el-button>
      </div>
    </el-popover>
    <el-tooltip class="item" effect="dark" :content="$t('default.delete')" placement="top" :enterable="false">
      <el-button v-popover:myPopover icon="el-icon-delete" size="mini" type="danger"></el-button>
    </el-tooltip>
  </div>
</template>

<script>
export default {
  props: ['id', 'targetStore'],

  data() {
    return {
      visible: false
    }
  },

  methods: {
    onCancel() {
      this.visible = false
    },
    async onConfirm() {
      this.visible = false

      await this.$store
        .dispatch(`${this.$props.targetStore}/delete`, {
          authToken: this.$store.state.authToken,
          id: this.$props.id
        })
        .then(res => {
          const msg = `#${this.$props.id} đã được xoá thành công`
          this.$message({
            showClose: true,
            message: msg,
            type: 'success',
            duration: 2 * 1000
          })
        })
    }
  }
}
</script>
