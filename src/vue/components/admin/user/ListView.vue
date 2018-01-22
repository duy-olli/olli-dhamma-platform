<template>
  <transition-group name="el-zoom-in-top">
    <div v-if="!noContent" :key="1">
      <el-table ref="bulkSelected" :data="records" style="width: 100%" @selection-change="handleSelectionChange">
        <el-table-column type="selection"></el-table-column>
        <el-table-column :label="$t('pages.admin.users.label.name')" width="250" prop="fullname"></el-table-column>
        <el-table-column :label="$t('pages.admin.users.label.email')" width="250" prop="email"></el-table-column>
        <el-table-column :label="$t('pages.admin.users.label.phone')" width="150" prop="mobilenumber"></el-table-column>
        <el-table-column :label="$t('pages.admin.users.label.group')">
          <template slot-scope="scope">
            <el-tag type="success" size="small">{{ scope.row.groupid }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.users.label.status')">
          <template slot-scope="scope">
            <el-tag :type="scope.row.status.style" size="small">{{ scope.row.status.label }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.users.label.activate')">
          <template slot-scope="scope">
            <el-tag :type="scope.row.verify.style" size="small">{{ scope.row.verify.label }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.users.label.verifyType')">
          <template slot-scope="scope">
              <el-tag type="gray" size="small">{{ scope.row.verifytype.label }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.users.label.dateCreated')" width="180">
          <template slot-scope="scope">
            <small>{{ scope.row.humandatecreated }}</small>
          </template>
        </el-table-column>
        <el-table-column class-name="td-operation">
          <template slot-scope="scope">
              <el-button-group class="operation">
                <nuxt-link :to="`/admin/user/edit/${scope.row.id}`">
                    <el-button icon="el-icon-edit" size="mini"></el-button>
                </nuxt-link>
                <del-button :id="scope.row.id" targetStore="users" />
              </el-button-group>
          </template>
        </el-table-column>
      </el-table>
      <div style="margin-top: 20px">
        <el-select v-model="bulkAction" :placeholder="$t('default.selectAction')" size="small">
          <el-option v-for="item in bulklist" :key="item.value" :label="item.label" :value="item.value" size="small">
          </el-option>
        </el-select>
        <el-button style="margin-left: 10px" type="primary" size="small" @click="bulkSubmitHandle">{{ $t('default.submit') }}</el-button>
      </div>
      <scroll-to-top :duration="1000" :timing="'ease'"></scroll-to-top>
    </div>
    <div v-else class="no-content" :key="2">
      <i class="el-icon-fa-users"></i>
      <p>{{ $t('default.noContent') }}</p>
    </div>
  </transition-group>
</template>

<script>
import { isEmpty, startCase, toLower, find, indexOf } from 'lodash'
import DelButton from '~/components/admin/DelButton'

export default {
  props: ['records'],

  components: {
    DelButton
  },

  computed: {
    noContent() {
      return isEmpty(this.$props.records)
    }
  },

  data() {
    return {
      bulklist: [
        {
          value: 'delete',
          label: this.$t('pages.admin.users.label.delete')
        },
        {
          value: 'enable',
          label: this.$t('pages.admin.users.label.enable')
        },
        {
          value: 'disable',
          label: this.$t('pages.admin.users.label.disable')
        }
      ],
      bulkSelected: [],
      bulkAction: ''
    }
  },
  methods: {
    handleSelectionChange(val) {
      this.bulkSelected = val
    },
    async bulkSubmitHandle() {
      if (this.bulkSelected.length === 0) {
        this.$message({
          showClose: true,
          message: this.$t('default.msg.noItemSelected'),
          type: 'warning',
          duration: 5 * 1000
        })
      } else if (this.bulkAction === '') {
        this.$message({
          showClose: true,
          message: this.$t('default.msg.noActionChosen'),
          type: 'warning',
          duration: 5 * 1000
        })
      } else {
        await this.$store
          .dispatch('users/bulk', {
            authToken: this.$store.state.authToken,
            formData: {
              itemSelected: this.bulkSelected,
              actionSelected: this.bulkAction
            }
          })
          .then(async () => {
            let queryObject = assign({}, this.$route.query)

            await this.$store
              .dispatch('users/get_all', {
                authToken: this.$store.state.authToken,
                query: queryObject
              })
              .then(() => {
                this.$message({
                  showClose: true,
                  message: `${startCase(
                    toLower(this.bulkAction)
                  )} ${this.$t('default.msg.deleteSuccess')}`,
                  type: 'success',
                  duration: 5 * 1000
                })
              })
          })
      }
    }
  }
}
</script>
