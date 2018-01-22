<template>
  <el-form ref="form" :model="form" label-position="top">
    <el-form-item prop="keyword">
      <el-input size="small" :placeholder="$t('pages.admin.users.label.search')"
        v-model="form.keyword"
        @keyup.enter.native="handleFilter"
        @click.native="setSearchWidth('focus')"
        @blur="setSearchWidth('blur')"
        :style="{ width: searchInputWidth + '%' }">
        <el-button slot="append" @click="handleFilter"><i class="el-icon-fa-search"></i></el-button>
      </el-input>
    </el-form-item>
    <el-form-item prop="groupid" :label="$t('pages.admin.users.label.group')">
      <el-select clearable size="small" v-model="form.groupid" :placeholder="$t('default.all')">
        <el-option v-for="item, index in data.groupList" :key="item" :label="item" :value="item">
        </el-option>
      </el-select>
    </el-form-item>
    <el-form-item prop="status" :label="$t('pages.admin.users.label.status')">
      <el-select clearable size="small" v-model="form.status" :placeholder="$t('default.all')">
        <el-option v-for="item in data.statusList" :key="item.value" :label="item.label" :value="item.value">
        </el-option>
      </el-select>
    </el-form-item>
    <el-form-item prop="verifytype" :label="$t('pages.admin.users.label.verifyType')">
      <el-select clearable size="small" v-model="form.verifytype" :placeholder="$t('default.all')">
        <el-option v-for="item in data.verifyList" :key="item.value" :label="item.label" :value="item.value">
        </el-option>
      </el-select>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" size="small" @click="handleFilter">{{ $t('default.filter') }}</el-button>
      <el-button size="small" @click="resetFilter">{{ $t('default.reset') }}</el-button>
    </el-form-item>
  </el-form>
</template>

<script>
import { mapState } from 'vuex'

export default {
  props: ['data'],

  data() {
    return {
      searchInputWidth: 100,
      form: {
        keyword: this.$route.query.keyword || '',
        status: this.$route.query.status || '',
        groupid: this.$route.query.groupid || '',
        verifytype: this.$route.query.verifytype || ''
      }
    }
  },

  computed: mapState({
    query: state => state.users.query
  }),

  methods: {
    handleFilter() {
      const querystring = require('querystring')
      this.query.page = 1
      const pageUrl = `?${querystring.stringify(
        this.form
      )}&${querystring.stringify(this.query)}`

      return this.$router.push(pageUrl)
    },
    resetFilter() {
      return this.$router.push('/admin/user')
    },
    setSearchWidth(type) {
      switch (type) {
        case 'focus':
          this.searchInputWidth = 200
          break
        case 'blur':
          this.searchInputWidth = 100
          break
      }
    }
  }
}
</script>
