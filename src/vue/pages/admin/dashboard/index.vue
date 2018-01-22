<template>
  <div>
    <el-col :span="24">
      <div class="filter-icon"><i class="el-icon-fa-bar-chart"></i></div>
      <breadcrumb :bc="bc" :totalItems="totalItems" />
      <div class="top-right-toolbar">
        <pagination :totalItems="totalItems" :currentPage="query.page" :recordPerPage="recordPerPage" />
      </div>
    </el-col>
    <el-col :span="24">
      <div class="panel-body">
        <el-row :gutter="20">
          <el-col :span="24">
            <el-col :span="8">
              <mysql-card :data="myDashboards.meta"></mysql-card>
            </el-col>
            <el-col :span="8">
              <sphinx-card :data="myDashboards.meta"></sphinx-card>
            </el-col>
            <el-col :span="8">
              <beanstalk-card :data="myDashboards.meta"></beanstalk-card>
            </el-col>
          </el-col>
        </el-row>
        <br/>
        <el-row>
          <el-col :span="20">
            <el-input class="input-with-select"
              v-model="form.keyword"
              size="small"
              width="50%"
              @keyup.enter.native="handleFilter()">
              <el-select slot="prepend" v-model="form.field" placeholder="Select">
                <el-option :label="$t('pages.admin.dashboards.label.dateCreated')" value="date"></el-option>
                <el-option :label="$t('pages.admin.dashboards.label.logger')" value="logger"></el-option>
                <el-option :label="$t('pages.admin.dashboards.label.level')" value="level"></el-option>
                <el-option :label="$t('pages.admin.dashboards.label.message')" value="message"></el-option>
              </el-select>
              <el-button slot="append" icon="el-icon-search" @click="handleFilter()"></el-button>
            </el-input>
          </el-col>
          <el-col :span="4" style="text-align: right;">
            <el-button size="mini" icon="el-icon-fa-refresh" @click="handleRefresh()">
              {{ $t('default.refresh') }}
            </el-button>
          </el-col>
        </el-row>
        <log-view :records="myDashboards.records" v-show="loading === false" />
        <pagination :totalItems="totalItems" :currentPage="query.page" :recordPerPage="recordPerPage" />

        <span v-show="loading === true" class="loading">
          <i class="el-icon-fa-spinner el-icon-fa-spin"></i>
        </span>
      </div>
    </el-col>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import Breadcrumb from '~/components/admin/Breadcrumb'
import Pagination from '~/components/admin/Pagination'
import LogView from '~/components/admin/dashboard/LogView'
import MysqlCard from '~/components/admin/dashboard/MysqlCard'
import SphinxCard from '~/components/admin/dashboard/SphinxCard'
import BeanstalkCard from '~/components/admin/dashboard/BeanstalkCard'

export default {
  layout: 'admin',
  middleware: 'authenticated',

  components: {
    Breadcrumb,
    Pagination,
    LogView,
    MysqlCard,
    SphinxCard,
    BeanstalkCard
  },

  head() {
    return {
      title: this.$t('pages.admin.dashboards.title'),
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: this.$t('pages.admin.dashboards.title')
        }
      ]
    }
  },

  data() {
    return {
      loading: false,
      bc: [
        {
          name: this.$t('pages.admin.dashboards.title'),
          link: '/admin/dashboard'
        },
        {
          name: this.$t('default.list'),
          link: ''
        }
      ],
      source: {},
      form: {
        keyword: this.$route.query.keyword || '',
        field: this.$route.query.field || 'message'
      }
    }
  },

  computed: mapState({
    myDashboards: state => state.dashboards.data,
    totalItems: state => state.dashboards.totalItems,
    recordPerPage: state => state.dashboards.recordPerPage,
    query: state => state.dashboards.query
  }),

  created() {
    this.fetchDataAction()
  },

  watch: {
    $route: 'fetchDataAction'
  },

  methods: {
    async fetchDataAction() {
      this.loading = true
      await this.$store
        .dispatch('dashboards/get_all', {
          authToken: this.$store.state.authToken,
          query: this.$route.query
        })
        .then(() => {
          this.loading = false
        })
        .catch(e => {
          this.loading = false
        })
    },
    handleFilter() {
      const querystring = require('querystring')
      this.query.page = 1
      const pageUrl = `?${querystring.stringify(
        this.form
      )}&${querystring.stringify(this.query)}`

      return this.$router.push(pageUrl)
    },
    handleRefresh() {
      return this.fetchDataAction()
    }
  }
}
</script>

<style scoped lang="scss">
.panel-body {
  margin-left: 0;
  .el-input-group {
    width: 50%;
    .el-select {
      width: 110px;
    }
  }
}

</style>
