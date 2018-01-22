<template>
  <transition-group name="el-zoom-in-top">
    <div v-if="!noContent" :key="1">
      <el-table :data="records" style="width: 100%">
        <el-table-column :label="$t('pages.admin.dashboards.label.dateCreated')" width="220">
          <template slot-scope="scope">
            <small>{{ scope.row.date }}</small>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.dashboards.label.logger')" width="100">
          <template slot-scope="scope">
            <el-tag type="primary" size="small">
              <small><code>{{ scope.row.logger }}</code></small>
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.dashboards.label.level')" width="100">
          <template slot-scope="scope">
            <el-tag :type="scope.row.level.style" size="small">{{ scope.row.level.label }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.dashboards.label.message')" width="300">
          <template slot-scope="scope">
            <small>{{ scope.row.message }}</small>
          </template>
        </el-table-column>
        <el-table-column :label="$t('pages.admin.dashboards.label.context')">
          <template slot-scope="scope">
            <tree-view :data="scope.row.context" :options="{maxDepth: 3}"></tree-view>
          </template>
        </el-table-column>
      </el-table>
      <scroll-to-top></scroll-to-top>
    </div>
    <div v-else class="no-content" :key="2">
      <i class="el-icon-fa-bar-chart"></i>
      <p>{{ $t('default.noContent') }}</p>
    </div>
  </transition-group>
</template>

<script>
import { isEmpty } from 'lodash'

export default {
  props: ['records'],

  computed: {
    noContent() {
      return isEmpty(this.$props.records)
    }
  },

  data() {
    return {}
  }
}
</script>

<style scoped lang="scss">
.el-table {
  margin-top: 10px;
  td {
    padding: 5px 0;
  }
}
</style>
