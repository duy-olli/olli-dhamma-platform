<template>
  <el-breadcrumb separator="/">
    <el-breadcrumb-item v-for="item, index in bc" :to="item.link" :key="index">
      {{ item.name }}
      <span v-if="index === bc.length - 1 && totalItems > 0"> &lpar;{{ totalItems }}&rpar;</span>
    </el-breadcrumb-item>
  </el-breadcrumb>
</template>

<script>
export default {
  props: ['bc', 'totalItems']
}
</script>

<style scoped lang="css">



</style>
